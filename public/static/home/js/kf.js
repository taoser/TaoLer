(function(win, doc) {
	if (!win._kefu) return;
	var k = win._kefu;
    var u = '&uid=' + (k.uid||'') + '&name=' + (k.name||'') + '&avatar=' + encodeURIComponent(k.avatar||'');
    var a = doc.createElement("a");
    a.className = "_kefu_outer";
    if (k.mini && !navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i)) {
        function layer () {
            if (layer.state) {
                if (layer.state == 1) {
                    layer.iframe_outer.style.height = "36px";
                    layer.iframe_outer.style.width = "160px";
                    layer.toggle.className = '_kefu_toggle _kefu_rotate';
                    layer.state = 2;
                } else {
                    layer.iframe_outer.style.height = "500px";
                    layer.iframe_outer.style.width = "360px";
                    layer.toggle.className = '_kefu_toggle';
                    layer.state = 1;
                }
                return;
            }
            layer.iframe_outer = doc.createElement("div");
            layer.iframe_outer.className = '_kefu_if_outer';
            var iframe = doc.createElement("iframe");
            //iframe.src = k.domain+"/user?bid="+k.bid+"&groupid="+k.groupid+"&mini=1";
            iframe.className = '_kefu_iframe';
            iframe.name = '_kefu_';
            iframe.allow="microphone";
            layer.toggle = doc.createElement("div");
            layer.toggle.className = '_kefu_toggle';
            layer.toggle.onclick = layer;
            layer.iframe_outer.appendChild(layer.toggle);
            layer.iframe_outer.appendChild(iframe);
            doc.body.appendChild(layer.iframe_outer);
            p('_kefu_');
            layer.state = 1;
        }
        a.onclick = layer;
    } else {
        a.href = k.domain+"/user?bid="+k.bid+"&groupid="+k.groupid+u;
        a.target="_blank";
    }

    var i = doc.createElement("img");
    i.src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADwAAAA2CAYAAACbZ/oUAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAAZAAAAGQBeJH1SwAAAB90RVh0U29mdHdhcmUATWFjcm9tZWRpYSBGaXJld29ya3MgOLVo0ngAAAXoSURBVGiB5VpZjBRVFD3nyTCCK6iggGZUNGhAUTNCAsYFFFBUEogbiYloJNGAGj/cEsOHRvxTIx+Ie9SEqCQSNVEWBQUxGBdwREBEXIm4MAKCEc/xo2omTdPVVd1dNYPx/FRX1733nfNe13u373tEDpDUF8BgAEMAXAhgJICTSPYEANt/APgSwHKSK2xvJLmZ5J95tF8LWK+jpLMATABwEYAzSB6T1de2AGwBsBrAIpJvk/y2Xi6FQdIhkm6S9J5zhKR2Sa9IGt/dGjsRC12fp9AE8UskXdRtQm0Pt/1u0UIrCJ8bzw1dh3hUd3W12BLRX0hq7SqxD3SX0FJI2iHpiqLFPtzdQksh6W9JE4sSe0d3C6wESTttn9Oovn3WYdujbS8jGRoNXAQcJSytJNvrjdEpTFIv2/MOVLEAQPIU27MbiVEqbibJIQ1y6gpMl3R2vc4EANtH2l5Psl9+vIqD7QUhhMn1+HaM8FX/FbExJkoaXI9jAADb1+TLp1iQ7ElySj2+QdIAACNy5lQ4bF9Sj18PAGeS7J2hgU8BPAvgJwCtAKaTPKyK/YsA3iDZbHsyycTEwfbPAJ4EsBbAqQBuJHlCCqVhtnvbngBgTwjhjTQNAABJj2dY9F+X1Fzm1yrp1wT7aRVEzUqw/br8fZQ0QNJnGXgdL+mW+PNSSRdnEbwxJeguSScm+N5TwT6xpyWtrWB/bYLtGElK0Txa0hll8Z6TlFiMCAAqiinBhhDC5oRnyyp8t7RKrJWlN7b/AvB+gu1qAL9VI2a7L4AfbO/t+I7k9QA+kDSqkk8geVC1oACaqzxratA+IJpH9kPMKy3r60FyB4A9Zb4nA1gk6fJyh2D7n5Sgpzg5ad8vIICKk5PtngAuKCPWBGBcgv0FJPukcNsZX1X+gGQvAC9LGrPPA0ltKe+JJa2Ol69SQpMk7U5wmVXWRrOkpxJib5M0osx+qKTNGXgNldRX0p4qNr/YbunsCEkPkrw3pSdh+ycALwP4EcBIkpNS7N8HsAjAwQAmkhxWxXYPyfm22xAtS1eRPDyFUrvtgYjKwWtSuCwJIYwFAEg6P60nD0RIWhXzn5LRfioQTVprbFedDQ9QLImv52a0v892UyD5O4B3CiJVJObH1wuzGJM8zfYlHdP+88VwKgzLQghrbA8BMLwGv6sDAJB80/bnxXDLH7YfjK/TSFZcxxMwukPwXgCzCuCWO2y/FkJYFBfpb6jRfUBnJhNCeNX2wnzp5Yt4cp0R395F8ugaQ/Qor1oea/sjkgNzYZgzbF8ZQljoaPvnw47t2Br8vU+uSnIrgCm2d+XKNAfYnhlCWCip2fYztYpNCz5WUnuOeUJDkHR7BzdJLzYQZ7+cGyWBWyWty5N4Hdgu6eoSTo80EkzS3kTBcQN9Jc3Li32N5BZLOj3+xVHS3BxiZjtiIen8rhptSZts31zyep0g6a2cYrdlEhyLrvj3Li9I+kTSDNuHlrQ5VdL3ObbxWC1ZSrVKRs1wVN5Zh6gktDCE0FkuknQegLtJXppnmwBeqkVwZtjeDWAFgF6IOmo3gO0AtgL4BkAbgDWltbK4KjoOwM0kLyuA1rIQwqq8BbfbfgHAnBDCujRjR4nOCERCx5NMKyjWDdv3AwkFtDqCbSP5tO25HaMWF9CGIhpNIBrtowAMAtACYIjtkzNUNvLgNyeEsLwmJ0kvVJgEvpN0f3m9K26kRdKWvCaceiFplaReNfdSqWBJmyTdmXasSNLALEXCAsWulXRczWJj8gskfSXpVpcsHRn8+kta2g1635XUvy6xtml7uKTEzbMU/ybbszNsnTSM+MTPQ7YrbRJ0LSSNkvR2gWI/dg4nfXKHo39j8yX9kbPgR9PaLiTxSAPJxQAW226xPRbREeThAAZV23POgINT224geK5wVIwY5Gg3oR/JPrabEWVogxFlYFU3yW0/EUKY3gV0i4ftIyTdJmlDlZ/03O7mmTts91Z0yvfT/4XgDthuknSdpJUlgud1N68ugaOt21VZRvhfqUIuqAd1mb8AAAAASUVORK5CYII=";
    a.appendChild(i);
    doc.body.appendChild(a);

    var s = doc.createElement("style");
    s.type = "text/css";
    var css_text = "._kefu_outer{z-index:2147483000!important;position:fixed!important;bottom:20px;right:20px;width:60px!important;height:60px!important;border-radius:50%!important;background:#fa4228;display:flex;align-items:center;justify-content:center;cursor: pointer;}._kefu_outer img{width:30px;}._kefu_if_outer{background:#fff;width:360px;height:500px;position:fixed;bottom:2px;right:90px;z-index:2147483000;box-shadow:rgba(15, 66, 76, 0.25) 0 0 24px 0;}._kefu_iframe{width:100%;height:100%;border:none;}._kefu_toggle{width:36px;height:36px;position:absolute;right:0;top:0;cursor:pointer;background-repeat: no-repeat;background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkBAMAAAATLoWrAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAqUExURUxpcf///////////////////////////////////////////////////+bWS5kAAAANdFJOUwAL9Bm4LUecY9Lmf4C+A9zDAAAAS0lEQVQoz2NgGAX4AUsAhGZ1gAvxXjcAUcy1F+BCbHO1BRgYGDfdTEDo5Fi7goGh61YDsmkyutMyLx1EtcH87t1idFtDXEdDHh0AAEpTDuHGIX5rAAAAAElFTkSuQmCC)}._kefu_rotate{transform:rotate(180deg);background-origin:border-box;padding-left:130px;}";
    try{
        s.appendChild(doc.createTextNode(css_text));
    }catch(ex){
        s.style = css_text;
    }
    doc.head.appendChild(s);

    var page_history = lg();
    if (!page_history[k.bid]) {
        page_history[k.bid] = [];
    }
    page_history[k.bid].push({title:document.title, url:document.location.href, time:parseInt(new Date().getTime()/1000)});
    if (page_history[k.bid].length > 10) {
        page_history[k.bid] = page_history[k.bid].slice(-10);
    }
    try {
        localStorage.setItem("kefu_page_history", JSON.stringify(page_history));
    } catch (e) {}

    function lg(bid) {
        var page_history = bid ? [] : {};
        try {
            page_history = localStorage.getItem("kefu_page_history");
        } catch (e) {}
        if (!page_history) {
            page_history = {};
        } else {
            try {
                page_history = JSON.parse(page_history);
            } catch (e) {
                page_history = {};
            }
        }
        if (bid) {
            return page_history[bid] || [];
        }
        return page_history;
    }

    function p(target) {
        var f = document.createElement("form");
        f.action = k.domain+"/user?bid="+k.bid+"&groupid="+k.groupid+"&mini=1"+u;
        f.target = target || '_blank';
        f.method = "post";
        f.style = 'display:none';

        try {
            t(f, 'history', JSON.stringify(lg(k.bid)));
            if (_kefu.goods) {
                t(f, 'goods', JSON.stringify(_kefu.goods));
            }
        } catch(e){}
        doc.body.appendChild(f);
        f.submit();
        doc.body.removeChild(f);
    }

    function t(f, n, v) {
        var h = document.createElement("input");
        h.name = n;
        h.value = v;
        f.appendChild(h);
    }

})(window, document);