/**

 @Name：layuiAdmin 用户管理 管理员管理 角色管理
 @Author：star1029
 @Site：http://www.layui.com/admin/
 @License：LPPL
    
 */


layui.define(['table', 'form'], function(exports){
  var $ = layui.$
  ,table = layui.table
  ,form = layui.form;

  //用户管理
  table.render({
    elem: '#LAY-user-manage'
    ,url: userList //模拟接口
    ,cols: [[
      {type: 'checkbox'}
      ,{field: 'id', width: 60, title: 'ID', sort: true}
      ,{field: 'username', title: '用户名', minWidth: 100}
	  ,{field: 'nick', title: '昵称',minWidth: 100}
      ,{field: 'avatar', title: '头像', width: 80, templet: '#imgTpl'}
      //,{field: 'phone', title: '手机',width: 80}
      ,{field: 'email', title: '邮箱', width: 120}
      ,{field: 'sex', width: 60, title: '性别',templet: '#sex'}
      ,{field: 'ip', title: '登录IP', width: 100}
	  ,{field: 'city', title: '城市', width: 80}
	  ,{field: 'logintime', title: '最后登录',width: 150, sort: true}
      ,{field: 'jointime', title: '注册时间',width: 110, sort: true}
	  ,{field: 'check', title: '状态', templet: '#buttonCheck', width: 95, align: 'center'}
	  ,{field: 'auth', title: '超级管理员', templet: '#buttonAuth', width: 80, align: 'center'}
      ,{title: '操作', width: 150, align:'center', toolbar: '#table-useradmin-webuser'}
    ]]
    ,page: true
    ,limit: 30
    ,height: 'full-220'
    ,text: '对不起，加载出现异常！'
  });
  
  //监听工具条
  table.on('tool(LAY-user-manage)', function(obj){
    var data = obj.data;
    if(obj.event === 'del'){
      layer.prompt({
        formType: 1
        ,title: '敏感操作，请验证口令'
      }, function(value, index){
        layer.close(index);
        
        layer.confirm('真的删除行么', function(index){
          //obj.del();
		  $.ajax({
				type:'post',
				url:userDelete,
				data:{id:data.id},
				dataType:'json',
				success:function(data){
					if(data.code == 0){
						layer.msg(data.msg,{
							icon:6,
							time:2000
						},function(){
							location.reload();
						});
					} else {
						layer.open({
							title:'删除失败',
							content:data.msg,
							icon:5,
							adim:6
						})
					}
				}
			});
		  
          layer.close(index);
        });
      });
    } else if(obj.event === 'edit'){
      var tr = $(obj.tr);

      layer.open({
        type: 2
        ,title: '编辑用户'
        ,content: userEdit +'?id='+ data.id
        ,maxmin: true
        ,area: ['500px', '450px']
        ,btn: ['确定', '取消']
        ,yes: function(index, layero){
			
          var iframeWindow = window['layui-layer-iframe'+ index]
          ,submitID = 'LAY-user-front-submit'
          ,submit = layero.find('iframe').contents().find('#'+ submitID);

          //监听提交
          iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
            var field = data.field; //获取提交的字段
			
            //提交 Ajax 成功后，静态更新表格中的数据
            $.ajax({
				type:"post",
				url:userEdit,
				data:{"id":field.id,"name":field.username,"phone":field.phone,"email":field.email,"user_img":field.avatar,"sex":field.sex},
				daType:"json",
				success:function (res){
					if (res.code == 0) {
						layer.msg(res.msg,{
							icon:6,
							time:2000
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
			
            table.reload('LAY-user-front-submit'); //数据刷新
            layer.close(index); //关闭弹层
          });  
          
          submit.trigger('click');
        }
        ,success: function(layero, index){
          
        }
      });
    }
  });

  //管理员管理
  table.render({
    elem: '#LAY-user-back-manage'
    ,url: adminIndex //模拟接口
    ,cols: [[
      {type: 'checkbox'}
      ,{field: 'id', width: 60, title: 'ID', sort: true}
      ,{field: 'loginname', title: '登录名', width: 100}
      ,{field: 'telphone', title: '手机', width: 120}
      ,{field: 'email', title: '邮箱', minWidth: 150}
      ,{field: 'check', title:'审核状态', templet: '#buttonTpl', width: 100, align: 'center'}
      ,{field: 'ip', title: 'IP', width: 100}
	  ,{field: 'logintime', title: '最后登陆', width: 150}
      ,{title: '操作', width: 150, align: 'center', toolbar: '#table-useradmin-admin'}
    ]]
    ,text: '对不起，加载出现异常！'
  });
  
  //监听工具条
  table.on('tool(LAY-user-back-manage)', function(obj){
    var data = obj.data;
    if(obj.event === 'del'){
      layer.prompt({
        formType: 1
        ,title: '敏感操作，请验证口令'
      }, function(value, index){
        layer.close(index);
        layer.confirm('确定删除此管理员？', function(index){
          //obj.del();
		  $.ajax({
				type:'post',
				url:adminDelete,
				data:{id:data.id},
				dataType:'json',
				success:function(data){
					if(data.code == 0){
						layer.msg(data.msg,{
							icon:6,
							time:2000
						},function(){
							location.reload();
						});
					} else {
						layer.open({
							title:'删除失败',
							content:data.msg,
							icon:5,
							adim:6
						})
					}
				}
			});
		  
          layer.close(index);
        });
      });
    }else if(obj.event === 'edit'){
      var tr = $(obj.tr);

      layer.open({
        type: 2
        ,title: '编辑管理员'
        ,content: adminEdit +'?id='+ data.id
        ,area: ['420px', '420px']
        ,btn: ['确定', '取消']
        ,yes: function(index, layero){
          var iframeWindow = window['layui-layer-iframe'+ index]
          ,submitID = 'LAY-user-back-submit'
          ,submit = layero.find('iframe').contents().find('#'+ submitID);

          //监听提交
          iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
            var field = data.field; //获取提交的字段

            //提交 Ajax 成功后，静态更新表格中的数据
            $.ajax({
				type:"post",
				url:adminEdit,
				data:{"id":field.id,"password":field.password,"mobile":field.mobile,"email":field.email},
				daType:"json",
				success:function (res){
					if (res.code == 0) {
						layer.msg(res.msg,{
							icon:6,
							time:2000
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
			
            table.reload('LAY-user-back-manage'); //数据刷新
            layer.close(index); //关闭弹层
          });  
          
          submit.trigger('click');
        }
        ,success: function(layero, index){           
          
        }
      })
    }
	
  });

  //角色管理
  table.render({
    elem: '#LAY-user-back-role'
    ,url: authGroupList //role接口
    ,cols: [[
	{field: 'id', width: 60, title: 'ID', sort: true}
      ,{field: 'rolename', title: '角色名', width: 120}
      ,{field: 'limits', title: '拥有权限', minWidth: 150}
      ,{field: 'descr', title: '具体描述', width: 120}
	  ,{field: 'check', title: '角色审核', toolbar: '#buttonCheck', width: 95}
      ,{title: '操作', width: 150, align: 'center', toolbar: '#table-useradmin-admin'}
    ]]
    ,text: '对不起，加载出现异常！'
  });
  
  //监听工具条
  table.on('tool(LAY-user-back-role)', function(obj){
    var data = obj.data;

    if(obj.event === 'del'){
      layer.confirm('确定删除此角色？', function(index){
        //obj.del();
		$.ajax({
				type:'post',
				url:authGroupRoledel,
				data:{id:data.id},
				dataType:'json',
				success:function(data){
					if(data.code == 0){
						layer.msg(data.msg,{
							icon:6,
							time:2000
						},function(){
							location.reload();
						});
					} else {
						layer.open({
							title:'删除失败',
							content:data.msg,
							icon:5,
							adim:6
						})
					}
				}
			});
        layer.close(index);
      });
    }else if(obj.event === 'edit'){
      var tr = $(obj.tr);

      layer.open({
        type: 2
        ,title: '编辑角色'
        ,content: authGroupRoleEdit +'?id='+ data.id
        ,area: ['500px', '480px']
        ,btn: ['确定', '取消']
        ,yes: function(index, layero){
          var iframeWindow = window['layui-layer-iframe'+ index]
          ,submit = layero.find('iframe').contents().find("#LAY-user-role-submit");

          //监听提交
          iframeWindow.layui.form.on('submit(LAY-user-role-submit)', function(data){
            var field = data.field; //获取提交的字段
			
			var mId = "";
			var e =iframeWindow.$(":checkbox");
			e.each(function () {
			   if($(this).next().hasClass("layui-form-checked")){
				  mId+=$(this).val()+",";
			   };
			})
			mId = mId.substring(0,mId.length-1);	
			rules = mId;
			
		//	var arr = new Array(); //获取提交的字段
		//	  $(iframeWindow.document).find('input[name=rules]:checked').each(function(){
		//		arr.push($(this).val());
		//	  });  
		//	console.log(arr);
		//		var rules = arr.join(',');//将数组元素连接起来以构建一个字符串

            
            //提交 Ajax 成功后，静态更新表格中的数据
        $.ajax({
				type:"post",
				url:authGroupRoleEdit,
				data:{"id":field.id,"rules":rules,"title":field.title,"descr":field.descr},
				daType:"json",
				success:function (res){
					if (res.code == 0) {
						layer.msg(res.msg,{
							icon:6,
							time:2000
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
			
            table.reload('LAY-user-back-role'); //数据刷新
            layer.close(index); //关闭弹层
          });  
          
          submit.trigger('click');
        }
        ,success: function(layero, index){
        
        }
      })
    }
  });
  
  
    //用户组管理
  table.render({
    elem: '#LAY-user-back-group'
    ,url: authAccessIndex //用户组access接口
    ,cols: [[
      {field: 'id', width: 60, title: 'ID', sort: true}
      ,{field: 'username', title: '管理员'}
      ,{field: 'title', title: '权限'}
	  ,{field: 'check', title: '状态', toolbar: '#buttonCheck', width: 95}
      ,{title: '操作', width: 150, align: 'center', toolbar: '#table-useradmin-admin'}
    ]]
    ,text: '对不起，加载出现异常！'
  });
  
  //监听工具条
  table.on('tool(LAY-user-back-group)', function(obj){
    var data = obj.data;

    if(obj.event === 'del'){
      layer.confirm('确定删除此角色？', function(index){
        //obj.del();
		$.ajax({
				type:'post',
				url:authAccessDelete,
				data:{id:data.id},
				dataType:'json',
				success:function(data){
					if(data.code == 0){
						layer.msg(data.msg,{
							icon:6,
							time:2000
						});
					} else {
						layer.open({
							title:'删除失败',
							content:data.msg,
							icon:5,
							adim:6
						})
					}
				}
			});
		table.reload('LAY-user-back-group'); //数据重载	
        layer.close(index);
      });
    }else if(obj.event === 'edit'){
      var tr = $(obj.tr);

      layer.open({
        type: 2
        ,title: '编辑用户权限'
        ,content: authAccessEdit +'?id='+ data.id
        ,area: ['450px', '420px']
        ,btn: ['确定', '取消']
        ,yes: function(index, layero){
          var iframeWindow = window['layui-layer-iframe'+ index]
          ,submit = layero.find('iframe').contents().find("#LAY-admin-group-submit");

          //监听提交
          iframeWindow.layui.form.on('submit(LAY-admin-group-submit)', function(data){
            var field = data.field; //获取提交的字段
            //提交 Ajax 成功后，静态更新表格中的数据
            $.ajax({
            type:"post",
            url:authAccessEdit,
            data:field,
            daType:"json",
            success:function (res){
              if (res.code == 0) {
                layer.msg(res.msg,{
                  icon:6,
                  time:2000
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
			
            table.reload('LAY-user-back-group'); //数据刷新
            layer.close(index); //关闭弹层
      });  
          
          submit.trigger('click');
        }
        ,success: function(layero, index){
        
        }
      })
    }
  });
  
  //权限管理
  table.render({
    elem: '#LAY-user-auth-rule'
    ,url: authRuleIndex //权限接口
    ,cols: [[
      {type: 'checkbox', fixed: 'left'}
      ,{field: 'id', width: 50, title: 'ID', align: 'center'}
	  ,{field: 'sort', title: '排序',width: 60 , align: 'center',edit: 'text'}
      ,{field: 'title', title: '权限名', templet: '#rules-title'}
      ,{field: 'name', title: '权限地址', minWidth: 150}
      ,{field: 'icon', title: '图标'}
      ,{field: 'status', title:'权限状态', templet: '#buttonAuth', minWidth: 80, align: 'center'}
      ,{field: 'level', title: '层级',width: 60, align: 'center'}
	  ,{field: 'ishidden', title: '菜单',templet: '#menu', width: 60}
      ,{title: '操作', width: 150, align: 'center', fixed: 'right', toolbar: '#table-authrule-edit'}
    ]]
    ,text: '对不起，加载出现异常！'
  });
  
  //监听工具条
  table.on('tool(LAY-user-auth-rule)', function(obj){
    var data = obj.data;
    if(obj.event === 'del'){
      layer.prompt({
        formType: 1
        ,title: '敏感操作，请验证口令'
      }, function(value, index){
        layer.close(index);
        layer.confirm('确定删除此权限？', function(index){
          //obj.del();
		  //console.log(data.id);
		 $.ajax({
				type:'post',
				url:authRuleDelete,
				data:{id:data.id},
				dataType:'json',
				success:function(data){
					if(data.code == 0){
						layer.msg(data.msg,{
							icon:6,
							time:2000
						},function(){
							location.reload();
						});
					} else {
						layer.open({
							title:'删除失败',
							content:data.msg,
							icon:5,
							adim:6
						})
					}
				}
			});
          layer.close(index);
        });
      });
    }else if(obj.event === 'edit'){
      var tr = $(obj.tr);

      layer.open({
        type: 2
        ,title: '编辑权限'
        ,content: authRuleEdit +'?id='+ data.id
        ,area: ['420px', '420px']
        ,btn: ['确定', '取消']
        ,yes: function(index, layero){
          var iframeWindow = window['layui-layer-iframe'+ index]
          ,submitID = 'LAY-user-rule-submit'
          ,submit = layero.find('iframe').contents().find('#'+ submitID);
		  
          //监听提交
          iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
            var field = data.field; //获取提交的字段
			
			if (field.ishidden == 'on'){
				field.ishidden = 0;
			} else {
				field.ishidden = 1;
				}
			
      //提交 Ajax 成功后，静态更新表格中的数据
			$.ajax({
				type:"post",
				url:authRuleEdit,
				data:{"id":field.id,"pid":field.pid,"title":field.title,"name":field.name,"icon":field.icon,"sort":field.sort,"ishidden":field.ishidden},
				daType:"json",
				success:function (res){
					if (res.code == 0) {
						layer.msg(res.msg,{
							icon:6,
							time:2000
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
			
            //table.reload('LAY-user-auth-rule'); //数据刷新
            layer.close(index); //关闭弹层
          }); 
          
          submit.trigger('click');
        }
        ,success: function(layero, index){
		
        }
      });
    }
	
  });


  exports('useradmin', {})
});