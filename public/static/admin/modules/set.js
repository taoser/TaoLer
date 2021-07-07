/**

 @Name：layuiAdmin（iframe版） 设置
 @Author：贤心
 @Site：http://www.layui.com/admin/
 @License: LPPL
    
 */
 
layui.define(['form', 'upload'], function(exports){
  var $ = layui.$
  ,layer = layui.layer
  ,laytpl = layui.laytpl
  ,setter = layui.setter
  ,view = layui.view
  ,admin = layui.admin
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
  
  //网站设置
  form.on('submit(set_website)', function(obj){
    //layer.msg(JSON.stringify(obj.field));
    loading = layer.load(2, {
                shade: [0.2, '#000']
            });
    //提交修改
    
    admin.req({
	   type: "post"
      ,url: setWebSite
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
  
  //邮件服务
  form.on('submit(set_system_email)', function(obj){
    //layer.msg(JSON.stringify(obj.field));
	loading = layer.load(2, {
                shade: [0.2, '#000']
            });
    
    //提交修改
    
    admin.req({
		type: 'post'
      ,url: setEmail
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
  
  
  //设置我的资料
  form.on('submit(setmyinfo)', function(obj){
    //layer.msg(JSON.stringify(obj.field));
    //console.log(obj.field);
    //提交修改
    
    admin.req({
		type: 'post'
      ,url: adminInfoSet
      ,data: obj.field
      ,success: function(res){
		if(res.code == 0){         
          //登入成功的提示与跳转
          layer.msg('修改成功', {
            offset: '15px'
            ,icon: 1
            ,time: 1000
          }, function(){
            location.reload();
          });
		} else {	
			layer.open({
				tiele:'修改失败',
				content:res.msg,
				icon:5,
				anim:6
			});
		}
      }
    });
    
    return false;
  });

  var  othis = $("input[name='copyright']");
  if(sysCy == 0){
	  othis.addClass('layui-disabled');
	  othis.attr("disabled");
  }

  //上传头像
  var avatarSrc = $('#LAY_avatarSrc');
  upload.render({
    url: '/api/upload/'
    ,elem: '#LAY_avatarUpload'
    ,done: function(res){
      if(res.status == 0){
        avatarSrc.val(res.url);
      } else {
        layer.msg(res.msg, {icon: 5});
      }
    }
  });
  
  //查看头像
  admin.events.avartatPreview = function(othis){
    var src = avatarSrc.val();
    layer.photos({
      photos: {
        "title": "查看头像" //相册标题
        ,"data": [{
          "src": src //原图地址
        }]
      }
      ,shade: 0.01
      ,closeBtn: 1
      ,anim: 5
    });
  };
  
  
  //设置密码
  form.on('submit(setmypass)', function(obj){
    //layer.msg(JSON.stringify(obj.field));
    //console.log(obj.field);
    //提交修改
    admin.req({
		type: 'post'
      ,url: adminRepassSet
      ,data: obj.field
      ,success: function(res){
        if(res.code == 0){         
          layer.msg('修改成功', {
            offset: '15px'
            ,icon: 1
            ,time: 1000
          }, function(){
            location.reload();
          });
		} else {	
			layer.open({
				tiele:'修改失败',
				content:res.msg,
				icon:5,
				anim:6
			});
		}
      }
    });
    return false;
  });
  
  //对外暴露的接口
  exports('set', {});
});