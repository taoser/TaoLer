/**
 TaoLer社区修改 www.aieok.com
 @Name: Fly社区主入口
 2024-4.7
 */

layui.define(['layer', 'form', 'util'], function(exports){
  
  var $ = layui.jquery
  ,layer = layui.layer
  ,form = layui.form
  ,util = layui.util
  ,device = layui.device()
  var uid = layui.cache.user.uid;
  var login = $('.fly-nav-user').attr('userlogin');

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

    //简易编辑器 -移除 -2024/4/7

    //新消息通知
    ,newmsg: function(){
      var elemUser = $('.fly-nav-user');
      var messageNums = elemUser.attr('msg-url');
      var messageRead = elemUser.attr('readMsg-url');
      if(uid != -1 && elemUser[0]){
        fly.json(messageNums, {
          _: new Date().getTime()
        }, function(res){
          if(res.status === 0 && res.count > 0){
            var msg = $('<a class="fly-nav-msg" href="javascript:;">'+ res.count +'</a>');
            elemUser.append(msg);
            msg.on('click', function(){
              fly.json(messageRead, {}, function(res){
                if(res.status === 0){
                  location.href = res.url;
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
  // layui.config({
  //   base: fly.dir
  // }).extend({
  //   im: 'im'
  //   ,face: 'face'
  // });

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

  //签到

  //活跃榜

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
  $('.fly-search').on('click', function(data){
    var searchUrl = $('.fly-search').attr('data-url');
    var forms = '<form action='+ searchUrl + '>';
    layer.open({
      type: 1
      ,title: false
      ,closeBtn: false
      ,shade: [0.1, '#fff']
      ,shadeClose: true
      ,maxWidth: 10000
      ,skin: 'fly-layer-search'
      ,content: [forms
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

  
  //手机设备的简单适配 用户中心底部左侧栏导航
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
      var bottom = $('.footer').offset().top - $(window).height();

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
    ,css: {right: 10, bottom: 50}
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

