
 
layui.define(['form', 'upload'], function(exports){
  var $ = layui.jquery
  ,layer = layui.layer
  ,laytpl = layui.laytpl
  ,setter = layui.setter
  ,view = layui.view
  ,form = layui.form
  ,upload = layui.upload;

  var $body = $('body');
  
  //自定义验证
  form.verify({
    nickname: function(value, item){ //value：表单的值、item：表单的DOM对象
      if(!new RegExp("^[a-zA-Z0-9_\u4e00-\u9fa5\\s·]+$").test(value)){
        return '用户名不能有特殊字符';
      }
      if(/(^\_)|(\__)|(\_+$)/.test(value)){
        return '用户名首尾不能出现下划线\'_\'';
      }
      if(/^\d+\d+\d$/.test(value)){
        return '用户名不能全为数字';
      }
    }
    
    //我们既支持上述函数式的方式，也支持下述数组的形式
    //数组的两个值分别代表：[正则匹配、匹配不符时的提示文字]
    ,pass: [
      /^[\S]{6,12}$/
      ,'密码必须6到12位，且不能出现空格'
    ]
    
    //确认密码
    ,repass: function(value){
      if(value !== $('#LAY_password').val()){
        return '两次密码输入不一致';
      }
    }
  });
  
  //网站信息设置
  form.on('submit(set_website)', function(obj){
    //layer.msg(JSON.stringify(obj.field));
    var URL = $(this).data('url');
    loading = layer.load(2, {
          shade: [0.2, '#000']
        });
    //提交修改
    
    $.ajax({
    type: "post"
    ,url: URL
    ,data: obj.field
    ,success: function(data){
      if (data.code == 0) {
        layer.close(loading);
          layer.msg(data.msg,{
            icon:6,
            time:2000
          });
        } else {
          layer.close(loading);
          layer.open({
            tiele:'设置失败',
            content:data.msg,
            icon:5,
            anim:6
          });
        }
    }
    });
    
    return false;
  });

  //网站系统配置
  form.on('submit(set_system_config)', function(data){
    var field = data.field;
    var URL = $(this).data('url');
    $.post(URL, field,function(res){
      if(res.code === 0){
        layer.msg(res.msg,{icon:6,tiye:2000},function(){
          location.reload();
        });
      } else {
        layer.open({title:"设置失败",content:res.msg,icon:5,anim:6});
      }
    });
    return false;
  });

  //域名配置
  form.on('submit(set_system_domain)', function(data){
    var field = data.field;
    var URL = $(this).data('url');
    $.post(URL,field,function(res){
      if(res.code == 0){
        layer.msg(res.msg,{icon:6,tiye:2000},function(){
          location.reload();
        });
      } else {
        layer.open({title:"设置失败",content:res.msg,icon:5,anim:6});
      }
    });
    return false;
  });

  // URL美化
  form.on('submit(set_url_rewrite)', function(data){
    var field = data.field;
    var URL = $(this).data('url');
    $.post(URL,field,function(res){
      if(res.code == 0){
        layer.msg(res.msg,{icon:6,tiye:2000},function(){
          location.reload();
        });
      } else {
        layer.open({title:"设置失败",content:res.msg,icon:5,anim:6});
      }
    });
    return false;
  });
  
  // 域名检查
  form.on('switch(domain_check)', function(data){
    var data = data.elem;
    var status = data.checked ? 'on' : 'off';
    var URL = $(this).data('url');
    if(status == 'on'){
      $('#set_domain').removeClass('layui-hide');
    } else {
      $('#set_domain').addClass('layui-hide');
      $.post(URL,{"domain_check":status},function(res){
        if(res.code == 0){
          layer.msg(res.msg,{icon:6,tiye:2000},function(){
            location.reload();
          });
        } else {
          layer.open({title:"设置失败",content:res.msg,icon:5,anim:6});
        }
      });
    }
    return false;
  });

  // 应用映射
  form.on('submit(set_bind_map)', function(data){
    var field = data.field;
    var URL = $(this).data('url');
    $.post(URL,field,function(res){
      if(res.code == 0){
        layer.msg(res.msg,{icon:6,tiye:2000},function(){
          window.location.reload();
        });
      } else {
        layer.open({title:"设置失败",content:res.msg,icon:5,anim:6});
      }
    });
    return false;
  });

  var  othis = $("input[name='copyright']");
  var sysCy = othis.data('level');
  if(sysCy === 0){
	  othis.addClass('layui-disabled');
	  othis.attr("disabled");
  }

  //对外暴露的接口
  exports('set', {});
});