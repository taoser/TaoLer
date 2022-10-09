/**
 *auth: liuyu 4654081@qq.com
 *Date: 2022/9/22
 *Desc: layui消息提示插件
 * */

layui.define(function (exports) {
    "use strict";

    function _typeof(obj) {
        "@babel/helpers - typeof";
        if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
            _typeof = function _typeof(obj) {
                return typeof obj;
            };
        } else {
            _typeof = function _typeof(obj) {
                return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
            };
        }
        return _typeof(obj);
    }

    !function (global, factory) {
        (typeof exports === "undefined" ? "undefined" : _typeof(exports)) === "object" && typeof module !== "undefined" ? module.exports = factory() : typeof define === "function" && define.amd ? define(factory) : (global = global || self, global.notify = factory());
    }(void 0, function () {
        "use strict";


        function c(args, children) {
            var el = document.createElement("div");

            for (var key in args) {
                var element = args[key];

                if (key == "className") {
                    key = "class";
                    el.setAttribute(key, element);
                } else if (key[0] == "_") {
                    el.addEventListener(key.slice(1), element);
                }
            }
            if (typeof children == "string") {
                el.innerHTML = children;
            } else if (_typeof(children) == "object" && children.tagName) {
                el.appendChild(children);
            } else if (children) {
                for (var i = 0; i < children.length; i++) {
                    var child = children[i];
                    el.appendChild(child);
                }
            }

            return el;
        }

        function addAnimationEnd(el, fn) {
            ["a", "webkitA"].forEach(function (prefix) {
                var name = prefix + "nimationEnd";
                el.addEventListener(name, function () {
                    fn();
                });
            });
        }

        function css(el, css) {
            for (var key in css) {
                el.style[key] = css[key];
            }

            if (el.getAttribute("style") === "") {
                el.removeAttribute("style");
            }
        }

        function addClass(el, s) {
            var c = el.className || "";

            if (!hasClass(c, s)) {
                var arr = c.split(/\s+/);
                arr.push(s);
                el.className = arr.join(" ");
            }
        }

        function hasClass(c, s) {
            return c.indexOf(s) > -1 ? !0 : !1;
        }

        function removeClass(el, s) {
            var c = el.className || "";

            if (hasClass(c, s)) {
                var arr = c.split(/\s+/);
                var i = arr.indexOf(s);
                arr.splice(i, 1);
                el.className = arr.join(" ");
            }

            if (el.className === "") {
                el.removeAttribute("class");
            }
        }

        var initArgs = {
            elem:"body", //默认显示在body,可以指定class或id
            msg: "", //文字内容
            position: 'topCenter', // bottomRight, bottomLeft, topRight, topLeft, topCenter, bottomCenter, vcenter
            duration: 3000, //默认3秒关闭
            showClose: true, //显示关闭按钮
            shadow:false
        };
        var positionB=['bottomLeft','bottomCenter','bottomRight'];
        var notify = {
            info: function info() {
                initConfig(arguments, "info");
            },
            success: function success() {
                initConfig(arguments, "success");
            },
            warning: function warning() {
                initConfig(arguments, "warning");
            },
            error: function error() {
                initConfig(arguments, "error");
            },
            loading: function loading() {
                return initConfig(arguments, "loading");
            },
            alert: function loading() {
                return initConfig(arguments, "alert");
            },
            confirm: function loading() {
                return initConfig(arguments, "confirm");
            },
            destroyAll: function destroyAll() {
                _destroyAll();
            },
            config: function config(obj) {
                for (var key in obj) {
                    if (Object.hasOwnProperty.call(obj, key)) {
                        if (obj[key] !== undefined) {
                            initArgs[key] = obj[key];
                        }
                    }
                }
            }
        };

        function initConfig(obj, type) {
            var args = {};
            for (var key in initArgs) {
                args[key] = initArgs[key];
            }
            var posArr = ["bottomRight", "bottomLeft", "topRight", "topLeft", "topCenter", "bottomCenter", "vcenter"];
            for (var i = 0; i < obj.length; i++) {
                var it = obj[i];

                if (it !== undefined) {
                    if (typeof it == "string" || _typeof(it) === "object") {
                        if (posArr.indexOf(it) > -1) {
                            args.position = it;
                        } else if(it.substring(0,1)=="."||it.substring(0,1)=="#"){
                            args.elem=it;
                        }else if(it=="shadow"){
                            args.shadow=true;
                        }else {
                            args.msg = it;
                        }

                    } else if (typeof it == "boolean") {
                        args.showClose = it;
                    } else if (typeof it == "function") {
                        args.onClose = it;
                    } else if (typeof it == "number") {
                        args.duration = it;
                    } else if (typeof it == "number") {
                        args.duration = it;
                    }
                }
            }

            args.type = type;
            return createMsgEl(args);
        }

        var msgWrappers = new Array();

        function createMsgEl(args) {
            var _msgWrapper;
            var type = args.type,
                elem = args.elem,
                duration = args.duration,
                msg = args.msg,
                position = args.position,
                closable = args.showClose,
                shadow = args.shadow,
                onClose = args.onClose;
            var iconObj = getIconObj();

            if (document.getElementsByClassName(position)[0]) {
                _msgWrapper = document.getElementsByClassName(position)[0];
            } else {
                _msgWrapper = c({
                    className: "notify-msg-stage " + position
                });
                msgWrappers.push(_msgWrapper);
            }

            if (type === "loading") {
                msg = msg === "" ? "正在加载，请稍后" : msg;
                closable = false; //loading不显示关闭按钮
            }

            var el,an;

            if(positionB.indexOf(position)!=-1){
                if(type=="alert" || type =="confirm"){
                    an="bounceIn";
                }else{
                    an="notify-bottom notify-msg-fade-in-b";
                }
                el = c({
                    className: "notify-msg-wrapper"
                }, [c({
                    className: "notify-msg " + an + " notify-msg-" + type
                }, [c({
                    className: "notify-msg-icon"
                }, iconObj[type]), c({
                    className: "notify-msg-content"
                }, msg), c({
                    className: "notify-msg-wait " + (closable ? "notify-msg-pointer" : ""),
                    _click: function _click() {
                        if (closable) {
                            closeFlag = true; //点击关闭按钮标志
                            flag = false; //正常关闭标志
                            closeMsg(el, onClose, _msgWrapper,shadow);
                        }
                    }
                }, getMsgRight(closable,type))])]);
            }else{
                if(type=="alert" || type =="confirm"){
                    an="bounceIn";
                }else{
                    an="notify-msg-fade-in";
                }
                el = c({
                    className: "notify-msg-wrapper"
                }, [c({
                    className: "notify-msg " + an + " notify-msg-" + type
                }, [c({
                    className: "notify-msg-icon"
                }, iconObj[type]), c({
                    className: "notify-msg-content"
                }, msg), c({
                    className: "notify-msg-wait " + (closable ? "notify-msg-pointer" : ""),
                    _click: function _click() {
                        if (closable) {
                            closeFlag = true; //点击关闭按钮标志
                            flag = false; //正常关闭标志
                            closeMsg(el, onClose, _msgWrapper,shadow);
                        }
                    }
                }, getMsgRight(closable,type))])]);
            }

            var anm = el.querySelector(".notify-msg__circle");

            if (anm) {
                css(anm, {
                    animation: "notify-msg_" + type + " " + duration + "ms linear"
                });

                if ("onanimationend" in window) {
                    addAnimationEnd(anm, function () {
                        closeMsg(el, onClose, _msgWrapper,shadow);
                    });
                } else {
                    setTimeout(function () {
                        closeMsg(el, onClose, _msgWrapper,shadow);
                    }, duration);
                }
            }

            if (type != "loading" && type != "alert" && type != "confirm") {
                setTimeout(function () {
                    closeMsg(el, onClose, _msgWrapper,shadow);
                }, duration);
            }
            //遮罩
            if(shadow &&!document.querySelector(".notify-modal")){
                var shadenode=document.createElement("div");
                if(shadow){
                    shadenode.className="notify-modal";
                }else{
                    shadenode.className="notify-modal notify-none";
                }
                
                document.querySelector("body").appendChild(shadenode);
            }
            if (!_msgWrapper.children.length) {
                if(elem!=="body"){
                    var _pos=getComputedStyle(document.querySelector(elem)).position;
                    if(_pos=="static"||_pos==""){
                        document.querySelector(elem).style.position="relative";
                    }

                    _msgWrapper.style.position = "absolute";
                }else{
                    _msgWrapper.style.position = "fixed";
                }
                document.querySelector(elem).appendChild(_msgWrapper);
            }
            _msgWrapper.appendChild(el);
            if(type=="confirm"){
                var btnCancel=document.createElement("button");//'<button type="button" class="btnCancel">取 消</button>';
                var textNode=document.createTextNode("取 消");
                btnCancel.appendChild(textNode);
                btnCancel.className="btnCancel";
                btnCancel.onclick=function(){
                    closeMsg(el,'', _msgWrapper,shadow);

                }
                document.querySelector(".notify-msg-confirm").appendChild(btnCancel);

            }
            css(el, {
                height: el.offsetHeight + "px"
            });
            setTimeout(function () {
                if(positionB.indexOf(position)!=-1){
                    removeClass(el.children[0], "notify-msg-fade-in-b");
                }else{
                    removeClass(el.children[0], "notify-msg-fade-in");
                }

            }, 300);

            if (type == "loading") {
                return function () {
                    closeMsg(el, onClose, _msgWrapper,shadow);
                };
            }

        }

        function getMsgRight(showClose,type) {
            if (showClose) {
                if(type=="alert" || type=="confirm"){
                    return "<button type=\"button\" class=\"btnOk\">确 定</button>"
                }else{
                    return "\n    <svg class=\"notify-msg-close\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"5514\"><path d=\"M810 274l-238 238 238 238-60 60-238-238-238 238-60-60 238-238-238-238 60-60 238 238 238-238z\" p-id=\"5515\"></path></svg>\n    ";
                }
            } 
        }

        var flag = true; //正常关闭标志
        var closeFlag = false;//点击关闭按钮标志

        function closeMsg(el, cb, _msgWrapper,shadow) {
            if (!el) return;
            if(hasClass(el.children[0].className,"notify-bottom")){
                addClass(el.children[0], "notify-msg-fade-out-b");
            }else if(hasClass(el.children[0].className,"bounceIn")){
                addClass(el.children[0], "bounceOut");
            }else{
                addClass(el.children[0], "notify-msg-fade-out");
            }

            if(shadow && document.querySelector(".notify-modal")){
                document.querySelector("body").removeChild(document.querySelector(".notify-modal"));
            }
            
           
            if (closeFlag) { //点击关闭按钮
                closeFlag = false;
                cb && cb(); //回调方法
            } else {
                if (flag) {//正常关闭，全局变量
                    cb && cb();
                } else {
                    flag = true
                    // return;
                }
            }

            setTimeout(function () {

                if (!el) return;
                var has = false;
                if (_msgWrapper) {
                    for (var i = 0; i < _msgWrapper.children.length; i++) {
                        if (_msgWrapper.children[i] && _msgWrapper.children[i] === el) {
                            has = true;
                        }
                    }
                    has && removeChild(el);
                    el = null;

                    if (!_msgWrapper.children.length) {
                        has && removeChild(_msgWrapper);
                    }

                }

            }, 300);
        }

        function getIconObj() {
            return {
                info: "\n    <svg t=\"1609810636603\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"3250\"><path d=\"M469.333333 341.333333h85.333334v469.333334H469.333333z\" fill=\"#ffffff\" p-id=\"3251\"></path><path d=\"M469.333333 213.333333h85.333334v85.333334H469.333333z\" fill=\"#ffffff\" p-id=\"3252\"></path><path d=\"M384 341.333333h170.666667v85.333334H384z\" fill=\"#ffffff\" p-id=\"3253\"></path><path d=\"M384 725.333333h256v85.333334H384z\" fill=\"#ffffff\" p-id=\"3254\"></path></svg>\n    ",
                success: "\n    <svg t=\"1609781242911\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"1807\"><path d=\"M455.42 731.04c-8.85 0-17.75-3.05-24.99-9.27L235.14 553.91c-16.06-13.81-17.89-38.03-4.09-54.09 13.81-16.06 38.03-17.89 54.09-4.09l195.29 167.86c16.06 13.81 17.89 38.03 4.09 54.09-7.58 8.83-18.31 13.36-29.1 13.36z\" p-id=\"1808\" fill=\"#ffffff\"></path><path d=\"M469.89 731.04c-8.51 0-17.07-2.82-24.18-8.6-16.43-13.37-18.92-37.53-5.55-53.96L734.1 307.11c13.37-16.44 37.53-18.92 53.96-5.55 16.43 13.37 18.92 37.53 5.55 53.96L499.67 716.89c-7.58 9.31-18.64 14.15-29.78 14.15z\" p-id=\"1809\" fill=\"#ffffff\"></path></svg>\n    ",
                warning: "\n    <svg t=\"1609776406944\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"18912\"><path d=\"M468.114286 621.714286c7.314286 21.942857 21.942857 36.571429 43.885714 36.571428s36.571429-14.628571 43.885714-36.571428L585.142857 219.428571c0-43.885714-36.571429-73.142857-73.142857-73.142857-43.885714 0-73.142857 36.571429-73.142857 80.457143l29.257143 394.971429zM512 731.428571c-43.885714 0-73.142857 29.257143-73.142857 73.142858s29.257143 73.142857 73.142857 73.142857 73.142857-29.257143 73.142857-73.142857-29.257143-73.142857-73.142857-73.142858z\" p-id=\"18913\" fill=\"#ffffff\"></path></svg>\n    ",
                error: "\n    <svg t=\"1609810716933\" viewBox=\"0 0 1024 1024\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" p-id=\"5514\"><path d=\"M810 274l-238 238 238 238-60 60-238-238-238 238-60-60 238-238-238-238 60-60 238 238 238-238z\" p-id=\"5515\" fill=\"#ffffff\"></path></svg>\n    ",
                loading: "\n    <div class=\"notify-msg_loading\">\n    <svg class=\"notify-msg-circular\" viewBox=\"25 25 50 50\">\n      <circle class=\"notify-msg-path\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-width=\"4\" stroke-miterlimit=\"10\"/>\n    </svg>\n    </div>\n    "
            };
        }

        function removeChild(el) {
            el && el.parentNode.removeChild(el);
        }

        function _destroyAll() {
            for (var j = 0; j < msgWrappers.length; j++) {
                for (var i = 0; i < msgWrappers[j].children.length; i++) {
                    var element = msgWrappers[j].children[i];
                    closeMsg(element, '', msgWrappers[j]);
                }
            }
        }

        window.addEventListener('DOMContentLoaded', function () {
            insertCssInHead();
        });

        function insertCssInHead() {
            var doc = document;

            if (doc && doc.head) {
                var head = doc.head;

                var _css = doc.createElement('style');

                var cssStr = "\n\n[class|=notify],[class|=notify]::after,[class|=notify]::before{box-sizing:border-box;outline:0}.notify-msg-progress{width:13px;height:13px}.notify-msg__circle{stroke-width:2;stroke-linecap:square;fill:none;transform:rotate(-90deg);transform-origin:center}.notify-msg-stage:hover .notify-msg__circle{-webkit-animation-play-state:paused!important;animation-play-state:paused!important}.notify-msg__background{stroke-width:2;fill:none}.notify-msg-stage{position:fixed;width:auto;z-index:99891015}.topLeft{top:20px;left:20px}.topCenter{top:20px;left:50%;transform:translate(-50%,0)}.topRight{top:20px;right:20px}.bottomLeft{bottom:20px;left:20px}.bottomCenter{bottom:20px;left:50%;transform:translate(-50%,0)}.bottomRight{bottom:20px;right:20px}.vcenter{top:50%;left:50%;transform:translate(-50%,-50%)}.notify-msg-wrapper{position:relative;left:50%;transform:translate(-50%,0);transform:translate3d(-50%,0,0);transition:height .3s ease,padding .3s ease;padding:6px 0;will-change:transform,opacity}.notify-msg{padding:15px 21px;border-radius:3px;position:relative;left:50%;transform:translate(-50%,0);transform:translate3d(-50%,0,0);display:flex;align-items:center}.notify-msg-content,.notify-msg-icon,.notify-msg-wait{display:inline-block}.notify-msg-icon{position:relative;width:13px;height:13px;border-radius:100%;display:flex;justify-content:center;align-items:center}.notify-msg-icon svg{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:11px;height:11px}.notify-msg-wait{width:20px;height:20px;position:relative;fill:#4eb127}.notify-msg-wait svg{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)}.notify-msg-close{width:14px;height:14px}.notify-msg-content{margin:0 10px;min-width:240px;text-align:left;font-size:14px;font-weight:500;font-family:-apple-system,Microsoft Yahei,sans-serif;text-shadow:0 0 1px rgba(0,0,0,.01)}.notify-msg.notify-msg-info{color:#0c5460;background-color:#d1ecf1;box-shadow:0 0 2px 0 rgba(0,1,1,.01),0 0 0 1px #bee5eb}.notify-msg.notify-msg-info .notify-msg-icon{background-color:#1d96aa}.notify-msg.notify-msg-success{color:#155724;background-color:#d4edda;box-shadow:0 0 2px 0 rgba(0,1,0,.01),0 0 0 1px #c3e6cb}.notify-msg.notify-msg-success .notify-msg-icon{background-color:#4ebb23}.notify-msg.notify-msg-warning{color:#856404;background-color:#fff3cd;box-shadow:0 0 2px 0 rgba(1,1,0,.01),0 0 0 1px #ffeeba}.notify-msg.notify-msg-warning .notify-msg-icon{background-color:#f1b306}.notify-msg.notify-msg-error{color:#721c24;background-color:#f8d7da;box-shadow:0 0 2px 0 rgba(1,0,0,.01),0 0 0 1px #f5c6cb}.notify-msg.notify-msg-error .notify-msg-icon{background-color:#f34b51}.notify-msg.notify-msg-loading{color:#0fafad;background-color:#e7fdfc;box-shadow:0 0 2px 0 rgba(0,1,1,.01),0 0 0 1px #c2faf9}.notify-msg_loading{flex-shrink:0;width:20px;height:20px;position:relative}.notify-msg-circular{-webkit-animation:notify-msg-rotate 2s linear infinite both;animation:notify-msg-rotate 2s linear infinite both;transform-origin:center center;height:18px!important;width:18px!important}.notify-msg-path{stroke-dasharray:1,200;stroke-dashoffset:0;stroke:#0fafad;-webkit-animation:notify-msg-dash 1.5s ease-in-out infinite;animation:notify-msg-dash 1.5s ease-in-out infinite;stroke-linecap:round}@-webkit-keyframes notify-msg-rotate{100%{transform:translate(-50%,-50%) rotate(360deg)}}@keyframes notify-msg-rotate{100%{transform:translate(-50%,-50%) rotate(360deg)}}@-webkit-keyframes notify-msg-dash{0%{stroke-dasharray:1,200;stroke-dashoffset:0}50%{stroke-dasharray:89,200;stroke-dashoffset:-35px}100%{stroke-dasharray:89,200;stroke-dashoffset:-124px}}@keyframes notify-msg-dash{0%{stroke-dasharray:1,200;stroke-dashoffset:0}50%{stroke-dasharray:89,200;stroke-dashoffset:-35px}100%{stroke-dasharray:89,200;stroke-dashoffset:-124px}}.notify-msg.notify-msg-info .notify-msg-wait{fill:#0fafad}.notify-msg.notify-msg-success .notify-msg-wait{fill:#4ebb23}.notify-msg.notify-msg-warning .notify-msg-wait{fill:#f1b306}.notify-msg.notify-msg-error .notify-msg-wait{fill:#f34b51}.notify-msg.notify-msg-loading .notify-msg-wait{fill:#0fafad}.notify-msg.notify-msg-alert .notify-msg-wait{fill:#999}.notify-msg.notify-msg-alert .notify-msg-content,.notify-msg.notify-msg-confirm .notify-msg-content{font-size:18px}.notify-msg.notify-msg-alert .notify-msg-wait{display:block;width:100px;height:auto;margin:auto;margin-top:30px}.notify-msg.notify-msg-confirm .notify-msg-wait{display:inline-block;width:100px;height:auto;margin:auto}.notify-msg.notify-msg-confirm .notify-msg-content{display:block;margin-bottom:30px}.notify-msg.notify-msg-alert .notify-msg-wait .btnOk,.notify-msg.notify-msg-confirm .notify-msg-wait .btnOk{line-height:30px;border-radius:4px;background-color:#0069d9;border:1px solid #0062cc;color:#fff;width:100px;cursor:pointer}.notify-msg.notify-msg-confirm .btnCancel{line-height:30px;border-radius:4px;background-color:#fff;border:1px solid #ddd;color:#666;width:100px;cursor:pointer;margin-left:6px}.notify-msg-pointer{cursor:pointer}@-webkit-keyframes notify-msg_info{0%{stroke:#0fafad}to{stroke:#0fafad;stroke-dasharray:0 100}}@keyframes notify-msg_info{0%{stroke:#0fafad}to{stroke:#0fafad;stroke-dasharray:0 100}}@-webkit-keyframes notify-msg_success{0%{stroke:#4eb127}to{stroke:#4eb127;stroke-dasharray:0 100}}@keyframes notify-msg_success{0%{stroke:#4eb127}to{stroke:#4eb127;stroke-dasharray:0 100}}@-webkit-keyframes notify-msg_warning{0%{stroke:#fcbc0b}to{stroke:#fcbc0b;stroke-dasharray:0 100}}@keyframes notify-msg_warning{0%{stroke:#fcbc0b}to{stroke:#fcbc0b;stroke-dasharray:0 100}}@-webkit-keyframes notify-msg_error{0%{stroke:#eb262d}to{stroke:#eb262d;stroke-dasharray:0 100}}@keyframes notify-msg_error{0%{stroke:#eb262d}to{stroke:#eb262d;stroke-dasharray:0 100}}.notify-msg-fade-in{-webkit-animation:notify-msg-fade .2s ease-out both;animation:notify-msg-fade .2s ease-out both}.notify-msg-fade-out{animation:notify-msg-fade .3s linear reverse both}@-webkit-keyframes notify-msg-fade{0%{opacity:0;transform:translate(-50%,0);transform:translate3d(-50%,-80%,0)}to{opacity:1;transform:translate(-50%,0);transform:translate3d(-50%,0,0)}}@keyframes notify-msg-fade{0%{opacity:0;transform:translate(-50%,0);transform:translate3d(-50%,-80%,0)}to{opacity:1;transform:translate(-50%,0);transform:translate3d(-50%,0,0)}}.notify-msg-fade-in-b{-webkit-animation:notify-msg-fade-b .2s ease-out both;animation:notify-msg-fade-b .2s ease-out both}.notify-msg-fade-out-b{animation:notify-msg-fade-b .3s linear reverse both}@-webkit-keyframes notify-msg-fade-b{0%{opacity:0;transform:translate(-50%,0);transform:translate3d(-50%,80%,0)}to{opacity:1;transform:translate(-50%,0);transform:translate3d(-50%,0,0)}}@keyframes notify-msg-fade-b{0%{opacity:0;transform:translate(-50%,0);transform:translate3d(-50%,80%,0)}to{opacity:1;transform:translate(-50%,0);transform:translate3d(-50%,0%,0)}}.notify-msg.notify-msg-alert,.notify-msg.notify-msg-confirm{display:block;box-shadow:0 0 6px 2px rgba(0,0,0,.1);background-color:#fff;border:1px solid #ccc}.bounceIn,.bounceOut{-webkit-animation-duration:.45s;-moz-animation-duration:.45s;-o-animation-duration:.45s;animation-duration:.45s}@keyframes bounceIn{0%{opacity:0;filter:alpha(opacity=0)}100%{opacity:1;filter:alpha(opacity=100)}}.bounceIn{-webkit-animation-name:bounceIn;-moz-animation-name:bounceIn;-o-animation-name:bounceIn;animation-name:bounceIn}@keyframes bounceOut{0%{opacity:1;filter:alpha(opacity=100)}100%{opacity:0;filter:alpha(opacity=0)}}.bounceOut{-webkit-animation-name:bounceOut;-moz-animation-name:bounceOut;-o-animation-name:bounceOut;animation-name:bounceOut}.notify-none{display:none}.notify-modal{left:0;top:0;width:100%;height:100%;background:#000;opacity:0.6;filter:alpha(opacity=60);position:fixed;transition:height .3s ease,padding .3s ease}\n        ";
                _css.innerHTML = cssStr;

                if (head.children.length) {
                    head.insertBefore(_css, head.children[0]);
                } else {
                    head.appendChild(_css);
                }
            }
        }

        return notify;
    });


    //输出接口
    exports('notify', notify);
});