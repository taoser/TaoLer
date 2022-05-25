﻿/**
 TaoLer社区修改 www.aieok.com
 @Name: Fly社区主入口
 2021-5.21
 */

layui.define(['layer', 'laytpl', 'form', 'element', 'upload', 'util', 'imgcom'], function(exports){
  
  var $ = layui.jquery
  ,layer = layui.layer
  ,laytpl = layui.laytpl
  ,form = layui.form
  ,element = layui.element
  ,upload = layui.upload
  ,util = layui.util
  ,imgcom = layui.imgcom
  ,device = layui.device()
  ,DISABLED = 'layui-btn-disabled';
  var uid = layui.cache.user.uid;

  //阻止IE7以下访问
  if(device.ie && device.ie < 8){
    layer.alert('如果您非得使用 IE 浏览器访问Fly社区，那么请使用 IE8+');
  }
  
  layui.focusInsert = function(obj, str){
    var result, val = obj.value;
    obj.focus();
    if(document.selection){ //ie
      result = document.selection.createRange(); 
      document.selection.empty(); 
      result.text = str; 
    } else {
      result = [val.substring(0, obj.selectionStart), str, val.substr(obj.selectionEnd)];
      obj.focus();
      obj.value = result.join('');
    }
  };

  //数字前置补零
  layui.laytpl.digit = function(num, length, end){
    var str = '';
    num = String(num);
    length = length || 2;
    for(var i = num.length; i < length; i++){
      str += '0';
    }
    return num < Math.pow(10, length) ? str + (num|0) : num;
  };
  
  var fly = {
	dir: layui.cache.host + 'static/res/mods/' //模块路径
    //Ajax
    ,json: function(url, data, success, options){
      var that = this, type = typeof data === 'function';
      
      if(type){
        options = success
        success = data;
        data = {};
      }

      options = options || {};

      return $.ajax({
        type: options.type || 'post',
        dataType: options.dataType || 'json',
        data: data,
        url: url,
        success: function(res){
          if(res.status === 0) {
            success && success(res);
          } else {
            layer.msg(res.msg || res.code, {shift: 6});
            options.error && options.error();
          }
        }, error: function(e){
          layer.msg('请求异常，请重试', {shift: 6});
          options.error && options.error(e);
        }
      });
    }

    //计算字符长度
    ,charLen: function(val){
      var arr = val.split(''), len = 0;
      for(var i = 0; i <  val.length ; i++){
        arr[i].charCodeAt(0) < 299 ? len++ : len += 2;
      }
      return len;
    }
    
    ,form: {}

    //简易编辑器
    ,layEditor: function(options){
      var html = ['<div class="layui-unselect fly-edit">'
          ,'<span type="strong" title="加粗"><i class="layui-icon layedit-tool-b layedit-tool-active" title="加粗" lay-command="Bold" layedit-event="b" "=""></i></span>'
          ,'<span type="face" title="表情"><i class="iconfont icon-yxj-expression" style="top: 1px;"></i></span>'
          ,'<span type="picture" title="图片：img[src]"><i class="iconfont icon-tupian"></i></span>'
          ,'<span type="video" title="视频"><i class="layui-icon layui-icon-video"></i></span>'
          ,'<span type="audio" title="音频"><i class="layui-icon layui-icon-headset"></i></span>'
          ,'<span type="href" title="超链接格式：a(href)[text]"><i class="iconfont icon-lianjie"></i></span>'
          ,'<span type="quote" title="引用"><i class="iconfont icon-yinyong" style="top: 1px;"></i></span>'
          ,'<span type="code" title="插入代码" class="layui-hide-xs"><i class="iconfont icon-emwdaima" style="top: 1px;"></i></span>'
          ,'<span type="hr" title="水平线">hr</span>'
          ,'<span type="preview" title="预览"><i class="iconfont icon-yulan1"></i></span>'
          ,'</div>'].join('');

      var closeTips = function(){
          layer.close(mod.face.index);
      };

      var log = {}, mod = {
      //加粗
      strong: function(editor){
        var str = window.getSelection().toString();
        if(!str == ''){
          //var strB = '<b>'+ str + '</b>';
          layui.focusInsert(editor[0], '[strong]'+ str + '[/strong]');
          //console.log(str);
          // console.log(strB);
        }
      },
      face: function(editor, self){ //插入表情
          var str = '', ul, face = fly.faces;
          for(var key in face){
              str += '<li title="'+ key +'"><img src="'+ face[key] +'"></li>';
          }
          str = '<ul id="LAY-editface" class="layui-clear" style="margin: -10px 0 0 -1px;">'+ str +'</ul>';

          layer.close(mod.face.index);
          mod.face.index = layer.tips(str, self, {
              tips: 3
              ,time: 0
              ,skin: 'layui-edit-face'
              ,tipsMore: true
          });

          $(document).off('click', closeTips).on('click', closeTips);

          $('#LAY-editface li').on('click', function(){
              var title = $(this).attr('title') + ' ';
              layui.focusInsert(editor[0], 'face' + title);
              editor.trigger('keyup');
          });
      }
      ,picture: function(editor){ //插入图片
        //判断登陆
        if(uid == -1){
          layer.msg('请登录再发图', {icon: 6}, function(){
            location.href = login;
          })
          return false;
        }
      
        layer.open({
          type: 1
          ,id: 'fly-jie-upload'
          ,title: '插入图片'
          ,area: 'auto'
          ,shade: false
          //,area: '465px'
          ,fixed: false
          ,offset: [
              editor.offset().top - $(window).scrollTop() + 'px'
              ,editor.offset().left + 'px'
          ]
          ,skin: 'layui-layer-border'
          ,content: ['<ul class="layui-form layui-form-pane" style="margin: 20px;">'
              ,'<li class="layui-form-item">'
              ,'<label class="layui-form-label">URL</label>'
              ,'<div class="layui-input-inline">'
              ,'<input required name="image" placeholder="支持粘贴远程图片地址" value="" class="layui-input">'
              ,'</div>'
              ,'<button type="button" class="layui-btn layui-btn-primary" id="uploadImg"><i class="layui-icon">&#xe67c;</i>上传图片</button>'
              ,'</li>'
              ,'<li class="layui-form-item" style="text-align: center;">'
              ,'<button type="button" lay-submit lay-filter="uploadImages" class="layui-btn" id="img-button">确认</button>'
              ,'</li>'
              ,'</ul>'].join('')
              ,success: function(layero, index){
              var image =  layero.find('input[name="image"]');
              
              //执行上传实例
              upload.render({
                  elem: '#uploadImg'
                  ,accept: 'images'
                  ,acceptMime: 'image/*'
                  ,exts: 'jpg|png|gif|bmp|jpeg'
                  ,url: uploads
                  ,data: {type:'image'}
                  ,auto: false
                  //,bindAction: '#img-button' //指向一个按钮触发上传
                  //,field: 'image'
                  ,size: 10240
                  ,choose: function (obj) { //选择文件后的回调
                    imgcom.uploads(obj);
                  }
                  ,done: function(res){
                      if(res.status == 0){
                      //console.log(res.url);
                          image.val(res.url);
                      } else {
                          layer.msg(res.msg, {icon: 5});
                      }
                  }
                  ,error: function(){
                    layer.msg('系统错误，请联系管理员');
                  }
              });

              form.on('submit(uploadImages)', function(data){
                  var field = data.field;							  
                  if(!field.image) return image.focus();
                  layui.focusInsert(editor[0], 'img['+ field.image + '] ');
                  layer.close(index);
                  editor.trigger('keyup');
              });
          }
      });
    }
    ,href: function(editor){ //超链接
        layer.prompt({
            title: '请输入合法链接'
            ,shade: false
            ,fixed: false
            ,id: 'LAY_flyedit_href'
            ,offset: [
                editor.offset().top - $(window).scrollTop() + 1 + 'px'
                ,editor.offset().left + 1 + 'px'
            ]
        }, function(val, index, elem){
            if(!/^http(s*):\/\/[\S]/.test(val)){
                layer.tips('请务必 http 或 https 开头', elem, {tips:1})
                return;
            }
            layui.focusInsert(editor[0], ' a('+ val +')['+ val + '] ');
            layer.close(index);
            editor.trigger('keyup');
        });
    }
    ,quote: function(editor){ //引用
        layer.prompt({
            title: '请输入引用内容'
            ,formType: 2
            ,maxlength: 10000
            ,shade: false
            ,id: 'LAY_flyedit_quote'
            ,offset: [
                editor.offset().top - $(window).scrollTop() + 1 + 'px'
                ,editor.offset().left + 1 + 'px'
            ]
            ,area: ['300px', '100px']
        }, function(val, index, elem){
            layui.focusInsert(editor[0], '[quote]\n  '+ val + '\n[/quote]\n');
            layer.close(index);
            editor.trigger('keyup');
        });
    }
    ,code: function(editor){ //插入代码
        layer.prompt({
            title: '请贴入代码'
            ,formType: 2
            ,maxlength: 10000
            ,shade: false
            ,id: 'LAY_flyedit_code'
            ,area: ['800px', '360px']
        }, function(val, index, elem){
            layui.focusInsert(editor[0], '[pre]\n'+ val + '\n[/pre]\n');
            layer.close(index);
            editor.trigger('keyup');
        });
    }
    ,hr: function(editor){ //插入水平分割线
        layui.focusInsert(editor[0], '[hr]\n');
        editor.trigger('keyup');
    }
    ,video: function(editor){ //插入视频
      //判断登陆
      if(uid == -1){
        layer.msg('请登录再发视频', {icon: 6}, function(){
          location.href = login;
        })
        return false;
      }
      layer.open({
        type: 1
        ,id: 'fly-jie-video-upload'
        ,title: '插入视频'
        ,area: 'auto'
        ,shade: false
        //,area: '465px'
        ,fixed: false
        ,offset: [
            editor.offset().top - $(window).scrollTop() + 'px'
            ,editor.offset().left + 'px'
        ]
        ,skin: 'layui-layer-border'
        ,content: ['<ul class="layui-form layui-form-pane" style="margin: 20px;">'
            ,'<li class="layui-form-item">'
            ,'<label class="layui-form-label">封面</label>'
            ,'<div class="layui-input-inline">'
            ,'<input type="text" required name="cover" placeholder="支持粘贴远程图片地址" value="" class="layui-input">'
            ,'</div>'
            ,'<button type="button" lay-type="image" class="layui-btn" id="video-img"><i class="layui-icon">&#xe67c;</i>上传封图</button>'
            ,'</li>'
            ,'<li class="layui-form-item">'
            ,'<label class="layui-form-label">URL</label>'
            ,'<div class="layui-input-inline">'
            ,'<input type="text" required name="video" placeholder="支持粘贴远程视频地址" value="" class="layui-input">'
            ,'</div>'
            ,'<button type="button" lay-type="video" class="layui-btn" id="layedit-video"><i class="layui-icon layui-icon-video"></i>上传视频</button>'
            ,'</li>'
            ,'<li class="layui-form-item" style="text-align: center;">'
            ,'<button type="button" lay-submit lay-filter="uploadImages" class="layui-btn">确认</button>'
            ,'</li>'
            ,'</ul>'].join('')
            ,success: function(layero, index){
              var video =  layero.find('input[name="video"]'), cover =  layero.find('input[name="cover"]');

              //上传视频
              upload.render({
                url: uploads
                ,data: {type:'video'}
                ,accept: 'video'
                ,acceptMime: 'video/mp4'
                ,exts: 'mp4'
                ,elem: '#layedit-video'
                ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
                layer.load(2); //上传loading
                }
                ,done: function(res){
                    if(res.status == 0){
                        video.val(res.url); 
                    } else {
                        layer.msg(res.msg, {icon: 5});
                    }
                    layer.closeAll('loading');
                  }
              });
          //上传图片
          upload.render({
            elem: '#video-img'
            ,accept: 'images'
            ,acceptMime: 'image/*'
            ,exts: 'jpg|png|gif|bmp|jpeg'
            ,url: uploads
            ,data: {type:'image'}
            ,auto: false
            //,bindAction: '#img-button' //指向一个按钮触发上传
            //,field: 'image'
            ,size: 10240
            ,choose: function (obj) { //选择文件后的回调
              imgcom.uploads(obj);
            }
            ,done: function(res){
                if(res.status == 0){
                    cover.val(res.url);
                } else {
                    layer.msg(res.msg, {icon: 5});
                }
            }
            ,error: function(){
              layer.msg('系统错误，请联系管理员');
            }
          });
          form.on('submit(uploadImages)', function(data){
              var field = data.field;
              if(!field.video) return video.focus();
              layui.focusInsert(editor[0], 'video('+field.cover+')['+ field.video + '] ');
              layer.close(index);
          });
              }
      });
    }
    ,audio: function(editor){ //插入音频
      //判断登陆
      if(uid == -1){
        layer.msg('请登录再发布', {icon: 6}, function(){
          location.href = login;
        })
        return false;
      }
        layer.open({
            type: 1
            ,id: 'fly-jie-audio-upload'
            ,title: '插入音频'
            ,area: 'auto'
            ,shade: false
            //,area: '465px'
            ,fixed: false
            ,offset: [
                editor.offset().top - $(window).scrollTop() + 'px'
                ,editor.offset().left + 'px'
            ]
            ,skin: 'layui-layer-border'
            ,content: ['<ul class="layui-form layui-form-pane" style="margin: 20px;">'
                ,'<li class="layui-form-item">'
                ,'<label class="layui-form-label">URL</label>'
                ,'<div class="layui-input-inline">'
                ,'<input required name="audio" placeholder="支持直接粘贴远程音频地址" value="" class="layui-input">'
                ,'</div>'
                ,'<button required type="button" name="file" lay-type="audio" class="layui-btn upload-audio"><i class="layui-icon layui-icon-headset"></i>上传音频</button>'
                ,'</li>'
                ,'<li class="layui-form-item" style="text-align: center;">'
                ,'<button type="button" lay-submit lay-filter="uploadImages" class="layui-btn">确认</button>'
                ,'</li>'
                ,'</ul>'].join('')
                ,success: function(layero, index){
                var loding,audio =  layero.find('input[name="audio"]');

                upload.render({
                    url: uploads
                    ,data: {type:'audio'}
                    ,elem: '#fly-jie-audio-upload .upload-audio'
                    ,accept: 'audio'
                    ,acceptMime: 'audio/*'
                    ,exts: 'mp3|m4a'
                    ,before: function(obj){   
                    //loding = layer.msg('文件上传中,请稍等哦', { icon: 16 ,shade:0.3,time:0 });
                    layer.load(2); //上传loading
                    }
                    ,done: function(res){
        
                        if(res.status == 0){
                            audio.val(res.url);
                        } else {
                            layer.msg(res.msg, {icon: 5});
                        }
                    layer.closeAll('loading');
                    }
                });
                form.on('submit(uploadImages)', function(data){
                    var field = data.field;
                    if(!field.audio) return audio.focus();
                    layui.focusInsert(editor[0], 'audio['+ field.audio + '] ');
                    layer.close(index);
                });
            }
        });
    }
    ,preview: function(editor, span){ //预览
        var othis = $(span), getContent = function(){
            var content = editor.val();
            return /^\{html\}/.test(content)
                ? content.replace(/^\{html\}/, '')
                : fly.content(content)
        }, isMobile = device.ios || device.android;

        if(mod.preview.isOpen) return layer.close(mod.preview.index);

        mod.preview.index = layer.open({
            type: 1
            ,title: '预览'
            ,shade: false
            ,offset: 'r'
            ,id: 'LAY_flyedit_preview'
            ,area: [
                isMobile ? '100%' : '775px'
                ,'100%'
            ]
            ,scrollbar: isMobile ? false : true
            ,anim: -1
            ,isOutAnim: false
            ,content: '<div class="detail-body layui-text" style="margin:20px;">'+ getContent() +'</div>'
            ,success: function(layero){
                editor.on('keyup', function(val){
                    layero.find('.detail-body').html(getContent());
                });
                mod.preview.isOpen = true;
                othis.addClass('layui-this');
            }
            ,end: function(){
                delete mod.preview.isOpen;
                othis.removeClass('layui-this');
            }
        });
    }
};

    layui.use('face', function(face){
        options = options || {};
        fly.faces = face;
        $(options.elem).each(function(index){
            var that = this, othis = $(that), parent = othis.parent();
            parent.prepend(html);
            parent.find('.fly-edit span').on('click', function(event){
                var type = $(this).attr('type');
                mod[type].call(that, othis, this);
                if(type === 'face'){
                    event.stopPropagation()
                }
            });
        });
    });

    }

    ,escape: function(html){
        return String(html||'').replace(/&(?!#?[a-zA-Z0-9]+;)/g, '&amp;')
            .replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, '&#39;').replace(/"/g, '&quot;');
    }

      //内容转义
    ,content: function(content){
        var util = fly
        ,item = fly.faces;

        //支持的html标签
        var html = function(end){
            return new RegExp('\\n*\\|\\-'+ (end||'') +'(div|span|p|button|table|thead|th|tbody|tr|td|ul|li|ol|li|dl|dt|dd|h2|h3|h4|h5)([\\s\\S]*?)\\-\\|\\n*', 'g');
        };


        //XSS
        content = util.escape(content||'')

            //转义图片
            .replace(/img\[([^\s]+?)\]/g, function(img){
                return '<div style="text-align: center;"><img src="' + img.replace(/(^img\[)|(\]$)/g, '') + '"></div>';
            })

            //转义@
            .replace(/@(\S+)(\s+?|$)/g, '@<a href="javascript:;" class="fly-aite">$1</a>$2')

            //转义表情
            .replace(/face\[([^\s\[\]]+?)\]/g, function(face){
                var alt = face.replace(/^face/g, '');
                return '<img alt="'+ alt +'" title="'+ alt +'" src="' + item[alt] + '">';
            })

            //转义脚本
            .replace(/a(\(javascript:)(.+)(;*\))/g, 'a(javascript:layer.msg(\'非法脚本\');)')

            //转义链接
            .replace(/a\([\s\S]+?\)\[[\s\S]*?\]/g, function(str){
                var href = (str.match(/a\(([\s\S]+?)\)\[/)||[])[1];
                var text = (str.match(/\)\[([\s\S]*?)\]/)||[])[1];
                if(!href) return str;
                var rel =  /^(http(s)*:\/\/)\b(?!(\w+\.)*(sentsin.com|layui.com))\b/.test(href.replace(/\s/g, ''));
                return '<a href="'+ href +'" style="color: red;" target="_blank"'+ (rel ? ' rel="nofollow"' : '') +'>'+ (text||href) +'</a>';
            })

            //转义横线
            .replace(/\[hr\]\n*/g, '<hr>')

            //转义表格
            .replace(/\[table\]([\s\S]*)\[\/table\]\n*/g, function(str){
                return str.replace(/\[(thead|th|tbody|tr|td)\]\n*/g, '<$1>')
                    .replace(/\n*\[\/(thead|th|tbody|tr|td)\]\n*/g, '</$1>')

                    .replace(/\[table\]\n*/g, '<table class="layui-table">')
                    .replace(/\n*\[\/table\]\n*/g, '</table>');
            })

            //转义 div/span
            .replace(/\n*\[(div|span)([\s\S]*?)\]([\s\S]*?)\[\/(div|span)\]\n*/g, function(str){
                return str.replace(/\[(div|span)([\s\S]*?)\]\n*/g, '<$1 $2>')
                    .replace(/\n*\[\/(div|span)\]\n*/g, '</$1>');
            })

            //转义列表
            .replace(/\[ul\]([\s\S]*)\[\/ul\]\n*/g, function(str){
                return str.replace(/\[li\]\n*/g, '<li>')
                    .replace(/\n*\[\/li\]\n*/g, '</li>')

                    .replace(/\[ul\]\n*/g, '<ul>')
                    .replace(/\n*\[\/ul\]\n*/g, '</ul>');
            })

            //转义代码
            .replace(/\[pre\]([\s\S]*)\[\/pre\]\n*/g, function(str){
                return str.replace(/\[pre\]\n*/g, '<pre>')
                    .replace(/\n*\[\/pre\]\n*/g, '</pre>');
            })

            //转义引用
            .replace(/\[quote\]([\s\S]*)\[\/quote\]\n*/g, function(str){
                return str.replace(/\[quote\]\n*/g, '<div class="layui-elem-quote">')
                    .replace(/\n*\[\/quote\]\n*/g, '</div>');
            })

            //转义加粗
            .replace(/\[strong\]([\s\S]*)\[\/strong\]\n*/g, function(str){
              return str.replace(/\[strong\]\n*/g,'<b>')
              .replace(/\n*\[\/strong\]\n*/g, '</b>');
            })

            //转义换行
            .replace(/\n/g, '<br>')

            //转义视频
            .replace(/video\(.*?\)\[([^\s]+?)\]/g, function(str){
                var cover = (str.match(/video\(([\s\S]+?)\)\[/)||[])[1];
                var video = (str.match(/\)\[([^\s]+?)\]/)||[])[1];
                cover = cover ? cover : '/static/res/images/video_cover.jpg';
                return  '<video poster="'+ cover + '" controls crossorigin><source src="'+ video + '" type="video/mp4"></video>';
            })
            //转义音频
            .replace(/audio\[([^\s]+?)\]/g, function(audio){
                return  '<audio controls><source src="'+ audio.replace(/(^audio\[)|(\]$)/g, '')+ '" type="audio/mp3"></audio>';
            })

        return content;
    }
    
    //新消息通知
    ,newmsg: function(){
      var elemUser = $('.fly-nav-user');
      if(layui.cache.user.uid !== -1 && elemUser[0]){
        fly.json(messageNums, {
          _: new Date().getTime()
        }, function(res){
          if(res.status === 0 && res.count > 0){
            var msg = $('<a class="fly-nav-msg" href="javascript:;">'+ res.count +'</a>');
            elemUser.append(msg);
            msg.on('click', function(){
              fly.json(messageRead, {}, function(res){
                if(res.status === 0){
                  location.href = userMessage;
                }
              });
            });
            layer.tips('你有 '+ res.count +' 条未读消息', msg, {
              tips: 3
              ,tipsMore: true
              ,fixed: true
            });
            msg.on('mouseenter', function(){
              layer.closeAll('tips');
            })
          }
        });
      }
      return arguments.callee;
    }

	//手机绑定弹窗
    ,setPhoneNotice: function(){
      layer.open({
        type: 1
        ,id: 'LAY_Notice_add'
        ,title: '手机号绑定通知'
        ,content: '<div class="layui-text" style="padding: 20px;">您需要绑定手机号后，才可进行发帖/回帖等操作。</div>'
        ,btnAlign: 'c'
        ,btn: ['立即绑定', '朕偏不！']
        ,yes: function(){
          location.href = '/user/set'
        }
        ,btn2: function(){
          layer.msg('少年，我看好你！');
        }
      });
    }

    //邮箱激活提示
    ,setEmailNotice: function(){
      layer.open({
        type: 1
        ,id: 'LAY_Notice_add'
        ,title: '邮箱激活通知'
        ,content: '<div class="layui-text" style="padding: 20px;">您需要激活邮箱后，才可进行发帖/回帖等操作。</div>'
        ,btnAlign: 'c'
        ,btn: ['前往激活', '朕偏不！']
        ,yes: function(){
          location.href = '/user/set'
        }
        ,btn2: function(){
          layer.msg('少年，我看好你！');
        }
      });
    }
  }; 

  //加载扩展模块
  layui.config({
    base: fly.dir
  }).extend({
    im: 'im'
    ,face: 'face'
  });

  //头像
  if(device.android || device.ios){
    $('#LAY_header_avatar').on('click', function(){
      return false;
    })
  }

  //刷新图形验证码
  $('body').on('click', '#captcha111', function(){
      var othis = $(this);
      othis.attr('src', othis.attr('src')+'?'+ new Date().getTime());
      //console.log(othis.attr('src'));
  });

  //头条轮播
  if($('#FLY_topline')[0]){
    layui.use('carousel', function(){
      var carousel = layui.carousel;
      
      var ins = carousel.render({
        elem: '#FLY_topline'
        ,width: '100%'
        ,height: '172px'
        ,anim: 'fade'
      });

      var resizeTopline = function(){
        var width = $(this).prop('innerWidth');
        if(width >= 1200){
          ins.reload({
            height: '172px'
          });
        } else if(width >= 992){
          ins.reload({
            height: '141px'
          });
        } else if(width >= 768){
          ins.reload({
            height: '166px'
          });
        }
      };

      resizeTopline()

      $(window).on('resize', resizeTopline);

    });
  }


  //签到
  var jName = "金币";
  var tplSignin = ['{{# if(d.signed){ }}'
    ,'<button class="layui-btn layui-btn-disabled">今日已签到</button>'
    ,'<span>获得了<cite>{{ d.experience }}</cite>' + jName + '</span>'
    ,'{{# } else { }}'
    ,'<button class="layui-btn layui-btn-danger" id="LAY_signin">今日签到</button>'
    ,'<span>可获得<cite>{{ d.experience }}</cite>' + jName + '</span>'
    ,'{{# } }}'].join('')
    ,tplSigninDay = '已连续签到<cite>{{ d.days }}</cite>天'

    ,signRender = function(data){
      laytpl(tplSignin).render(data, function(html){
      elemSigninMain.html(html);
    });
    laytpl(tplSigninDay).render(data, function(html){
      elemSigninDays.html(html);
    });
  }

  ,elemSigninHelp = $('#LAY_signinHelp')
  ,elemSigninTop = $('#LAY_signinTop')
  ,elemSigninMain = $('.fly-signin-main')
  ,elemSigninDays = $('.fly-signin-days');
  
  if(elemSigninMain[0]){
    fly.json(signStatusUrl, function(res){
      if(!res.data) return;
      signRender.token = res.data.token;
      signRender(res.data);
    });
    
  }

  $('body').on('click', '#LAY_signin', function(){
  	//登录判断
  	if(uid == -1){
  		layer.msg('请登录再签到', {icon: 6}, function(){
  			location.href = login;
  		})
    	return false;
    }
    		
    var othis = $(this);
    if(othis.hasClass(DISABLED)) return;
	
    fly.json(signInUrl, {
      token: signRender.token || 1
    }, function(res){
      signRender(res.data);
    }, {
      error: function(){
        othis.removeClass(DISABLED);
      }
    });

    othis.addClass(DISABLED);
  });

  //签到说明
  elemSigninHelp.on('click', function(){
	  
	$.getJSON(signRuleUrl, function(data) {
		
		//拼接表格字符串
		var $str = '';
		$.each(data.msg, function(k, v) {
			$str += '<tr><td>≥' + v.days + '</td><td>' + v.score + '</td></tr>';
		 });
		 
		layer.open({
		  type: 1
		  ,title: '签到说明'
		  ,area: '300px'
		  ,shade: 0.8
		  ,shadeClose: true
		  ,content: ['<div class="layui-text" style="padding: 20px;">'
			,'<blockquote class="layui-elem-quote">“签到”可获得社区' + jName + '，规则如下</blockquote>'
			,'<table class="layui-table">'
			  ,'<thead>'
				,'<tr><th>连续签到天数</th><th>每天可获' + jName + '</th></tr>'
			  ,'</thead>'
			  ,'<tbody>'
			   ,$str
			  ,'</tbody>'
			,'</table>'
			,'<ul>'
			  ,'<li>中间若有间隔，则连续天数重新计算</li>'
			  ,'<li style="color: #FF5722;">不可利用程序自动签到，否则' + jName + '清零</li>'
			,'</ul>'
		  ,'</div>'].join('')
		});
	});
	
  });

  //签到活跃榜
  var tplSigninTop = ['{{# layui.each(d.data, function(index, item){ }}'
    ,'<li>'
      ,'<a href="/u/{{item.uid}}" target="_blank">'
        ,'<img src="{{item.user.avatar}}">'
        ,'<cite class="fly-link">{{item.user.username}}</cite>'
      ,'</a>'
      ,'{{# var date = new Date(item.time); if(d.index === 0) { }}'
        ,'<span class="fly-grey"> {{ layui.laytpl.digit(date.getFullYear()) + "-" + layui.laytpl.digit(date.getMonth()+1) + "-" + layui.laytpl.digit(date.getDate())}} 签到 <i class="layui-icon layui-icon-ok"></i></span>'
      ,'{{# } else if(d.index == 1) { }}' 
        ,'<span class="fly-grey">签到于 {{ layui.laytpl.digit(date.getHours()) + ":" + layui.laytpl.digit(date.getMinutes()) + ":" + layui.laytpl.digit(date.getSeconds()) }} <i class="layui-icon layui-icon-flag"></i></span>'
      ,'{{# } else { }}'
        ,'<span class="fly-grey">已连续签到 <i>{{ item.days }}</i> 天 <i class="layui-icon layui-icon-face-smile"></i></span>'
      ,'{{# } }}'
    ,'</li>'
  ,'{{# }); }}'
  ,'{{# if(d.data.length === 0) { }}'
    ,'{{# if(d.index < 2) { }}'
      ,'<li class="fly-none fly-grey">今天还没有人签到</li>'
    ,'{{# } else { }}'
      ,'<li class="fly-none fly-grey">还没有签到记录</li>'
    ,'{{# } }}'
  ,'{{# } }}'].join('');

  elemSigninTop.on('click', function(){
    var loadIndex = layer.load(1, {shade: 0.8});
    fly.json(signJsonUrl, function(res){ //实际使用，请将 url 改为真实接口
      var tpl = $(['<div class="layui-tab layui-tab-brief" style="margin: 5px 0 0;">'
        ,'<ul class="layui-tab-title">'
          ,'<li class="layui-this">最新签到</li>'
          ,'<li>今日最快</li>'
          ,'<li>总签到榜</li>'
        ,'</ul>'
        ,'<div class="layui-tab-content fly-signin-list" id="LAY_signin_list">'
          ,'<ul class="layui-tab-item layui-show"></ul>'
          ,'<ul class="layui-tab-item">2</ul>'
          ,'<ul class="layui-tab-item">3</ul>'
        ,'</div>'
      ,'</div>'].join(''))
      ,signinItems = tpl.find('.layui-tab-item');

      layer.close(loadIndex);

      layui.each(signinItems, function(index, item){
        var html = laytpl(tplSigninTop).render({
          data: res.data[index]
          ,index: index
        });
        $(item).html(html);
      });

      layer.open({
        type: 1
        ,title: '签到活跃榜 - TOP 20'
        ,area: '300px'
        ,shade: 0.8
        ,shadeClose: true
        ,id: 'layer-pop-signintop'
        ,content: tpl.prop('outerHTML')
      });

    }, {type: 'get'});
  });


  //回帖榜
  var tplReply = ['{{# layui.each(d.data, function(index, item){ }}'
    ,'<dd>'
      ,'<a href="{{item.uid}}">'
        ,'<img src="{{item.user.avatar}}">'
        ,'<cite>{{item.user.username}}</cite>'
        ,'<i>{{item["count(*)"]}}' +replyNum+'</i>'
      ,'</a>'
    ,'</dd>'
  ,'{{# }); }}'].join('')
  ,elemReply = $('#LAY_replyRank');

  if(elemReply[0]){
    
    fly.json(replyUrl, {
      limit: 20
    }, function(res){
      var html = laytpl(tplReply).render(res);
      elemReply.find('dl').html(html);
    });
    
  };

  //相册
  if($(window).width() > 750){
    layer.photos({
      photos: '.photos'
      ,zIndex: 9999999999
      ,anim: -1
    });
  } else {
    $('body').on('click', '.photos img', function(){
      window.open(this.src);
    });
  }


  //搜索
  $('.fly-search').on('click', function(){
    layer.open({
      type: 1
      ,title: false
      ,closeBtn: false
      //,shade: [0.1, '#fff']
      ,shadeClose: true
      ,maxWidth: 10000
      ,skin: 'fly-layer-search'
      ,content: ['<form action='+searchUrl+'>'
        ,'<input autocomplete="off" placeholder="搜索内容，回车跳转" type="text" name="keywords">'
      ,'</form>'].join('')
      ,success: function(layero){
        var input = layero.find('input');
        input.focus();

        layero.find('form').submit(function(){
          var val = input.val();
          if(val.replace(/\s/g, '') === ''){
            return false;
          }
          input.val();
      });
      }
    })
  });
  
  //移动端搜索
  

  //新消息通知
  fly.newmsg();

  //发送激活邮件
  fly.activate = function(email){
    fly.json(actvateEmaiUrl, {"email":email}, function(res){
      if(res.status === 0){
        layer.alert('已成功将激活链接发送到了您的邮箱，接受可能会稍有延迟，请注意查收。', {
          icon: 1
        });
      };
    });
  };
  $('#LAY-activate').on('click', function(){
    fly.activate($(this).attr('email'));
  });

  //点击@
  $('body').on('click', '.fly-aite', function(){
    var othis = $(this), text = othis.text();
    if(othis.attr('href') !== 'javascript:;'){
      return;
    }
    text = text.replace(/^@|（[\s\S]+?）/g, '');
    othis.attr({
      href: jumpUrl + '?name=' + text
      ,target: '_blank'
    });
  });

  //表单提交
  form.on('submit(*)', function(data){
    var action = $(data.form).attr('action'), button = $(data.elem);
    fly.json(action, data.field, function(res){
      var end = function(){
        if(res.action){
          location.href = res.action;
        } else {
          fly.form[action||button.attr('key')](data.field, data.form);
        }
      };
      if(res.status == 0){
        button.attr('alert') ? layer.alert(res.msg, {
          icon: 1,
          time: 10*1000,
          end: end
        }) : end();
      };
    });
    //return false;
  });

  //加载特定模块
  if(layui.cache.page && layui.cache.page !== 'index'){
    var extend = {};
    extend[layui.cache.page] = layui.cache.page;
    layui.extend(extend);
    layui.use(layui.cache.page);
  }
  
  //加载IM
  if(!device.android && !device.ios){
    //layui.use('im');
  }

  //加载编辑器
  fly.layEditor({
    elem: '.fly-editor'
  });
  
  //手机设备的简单适配 底部左侧栏导航
  var treeMobile = $('.site-tree-mobile')
  ,shadeMobile = $('.site-mobile-shade')

  treeMobile.on('click', function(){
    $('body').addClass('site-mobile');
  });

  shadeMobile.on('click', function(){
    $('body').removeClass('site-mobile');
  });
  
  //手机设备的简单适配 头部左侧栏导航
  var treeMobileTop = $('.site-tree-mobile-top')
  ,shadeMobileTop = $('.site-mobile-shade-top')

  treeMobileTop.on('click', function(){
    $('body').addClass('site-mobile');
  });

  shadeMobileTop.on('click', function(){
    $('body').removeClass('site-mobile');
  });
  
  //导航窗口scroll
  ;!function(){
    var main = $('.site-menu'), scroll = function(){
      var stop = $(window).scrollTop();

      if($(window).width() <= 992) return;
      var bottom = $('.fly-footer').offset().top - $(window).height();

      if(stop > 60){ //211
        if(!main.hasClass('site-fix')){
          main.addClass('site-fix').css({
            width: main.parent().width()
          });
        }
      }else {     
        if(main.hasClass('site-fix')){
          main.removeClass('site-fix').css({
            width: 'auto'
          });
        }
      }
      stop = null;
    };
    scroll();
    $(window).on('scroll', scroll);
  }();

  //获取统计数据
  $('.fly-handles').each(function(){
    var othis = $(this);
    $.get('/api/handle?alias='+ othis.data('alias'), function(res){
      othis.html('（下载量：'+ res.number +'）');
    })
  });
  
  //添加文章
  $('#add_post').click(function() {
	  if (layui.cache.user.uid !== -1) {
		  loading = layer.load(2, {
            shade: [0.2, '#000']
      });
		  location.href = articleAdd;
		} else {
			layer.msg('请先登陆',{
				icon:5,
				time:2000
			},function () {
				location.href = login;
			});
		}
		return false;
  });
  
  //退出登录
    $('.logi_logout').click(function() {
        loading = layer.load(2, {
            shade: [0.2, '#000']
        });
        var url = $(this).data('url');
        $.getJSON(url, function(data) {
            if (data.code == 200) {
                layer.close(loading);
                layer.msg(data.msg, { icon: 1, time: 1000 }, function() {
                    location.href = data.url;
                });
            } else {
                layer.close(loading);
                layer.msg(data.msg, { icon: 2, anim: 6, time: 1000 });
            }
        });
    });
	
  //pc端监听多语言切换
	$('#language').on('change',function(){
	  var data = $(this).val();
		$.post(langUrl,{language:data},function(res){
			if(res.code == 0){
				location.reload();
			}
		});
		return false;
	});
	//移动端左侧栏监听多语言切换
	$('#language1').on('change',function(){
	  var data = $(this).val();
		$.post(langUrl,{language:data},function(res){
			if(res.code == 0){
				location.reload();
			}
		});
		return false;
	});
  
  //固定Bar
  util.fixbar({
    bar1: '&#xe642;'
    ,bgcolor: '#009688'
    ,click: function(type){
		//添加文章
      if(type === 'bar1'){
        //slayer.msg('打开 index.js，开启发表新帖的路径');
        location.href = articleAdd;
      }
    }
  });

  exports('fly', fly);
});

