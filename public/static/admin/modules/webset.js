//网站后台综合设置

layui.define(['table', 'form'], function(exports){
  var $ = layui.$
  ,table = layui.table
  ,form = layui.form;

	//签到规则
		table.render({
			elem: '#sign-rule',
			url: signSignRule,
			cols:[[
				{type: 'numbers', fixed: 'left'},
				{field: 'days',title: '天数'},
				{field: 'score',title: '积分'},
				{field: 'ctime',title: '时间'},
				{title: '操作', width: 150, align:'center', fixed: 'right', toolbar: '#sign-rule-button'}
				
			]]
			,page: true
			,limit: 10
			,height: 'full-220'
			,text: '对不起，加载出现异常！'
		});
		
		 //监听工具条
  table.on('tool(sign-rule)', function(obj){
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
				url:signDelete,
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
		  table.reload('sign-rule');
          layer.close(index);
        });
      });
    } else if(obj.event === 'edit'){
      var tr = $(obj.tr);

      layer.open({
        type: 2
        ,title: '编辑签到'
        ,content: signSignEdit +'?id='+ data.id
        ,maxmin: true
        ,area: ['350px', '300px']
        ,btn: ['确定', '取消']
        ,yes: function(index, layero){
			
          var iframeWindow = window['layui-layer-iframe'+ index]
          ,submitID = 'LAY-user-sign-submit'
          ,submit = layero.find('iframe').contents().find('#'+ submitID);

          //监听提交
          iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
            var field = data.field; //获取提交的字段
			
            //提交 Ajax 成功后，静态更新表格中的数据
            $.ajax({
				type:"post",
				url:signSignEdit,
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
			
            table.reload('sign-rule'); //数据刷新
            layer.close(index); //关闭弹层
          });  
          
          submit.trigger('click');
        }
        ,success: function(layero, index){
          
        }
      });
    }
  });
		
		
  //Vip规则
		table.render({
			elem: '#vip-rule',
			url: vipRule,
			cols:[[
				{type: 'numbers', fixed: 'left'},
				{field: 'vip',title: '等级'},
				{field: 'score',title: '积分'},
				{field: 'nick',title: '认证'},
				{field: 'rules',title: '权限'},
				{field: 'ctime',title: '时间'},
				{title: '操作', width: 150, align:'center', fixed: 'right', toolbar: '#vip-rule-button'}
				
			]]
			,page: true
			,limit: 10
			,height: 'full-220'
			,text: '对不起，加载出现异常！'
		});
  
  //监听工具条
  table.on('tool(vip-rule)', function(obj){
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
				url:vipDelete,
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
		  table.reload('vip-rule'); //数据刷新
          layer.close(index);
        });
      });
    } else if(obj.event === 'edit'){
      var tr = $(obj.tr);

      layer.open({
        type: 2
        ,title: '编辑VIP'
        ,content: vipEdit +'?id='+ data.id
        ,maxmin: true
        ,area: ['400px', '370px']
        ,btn: ['确定', '取消']
        ,yes: function(index, layero){
			
          var iframeWindow = window['layui-layer-iframe'+ index]
          ,submitID = 'LAY-user-vip-submit'
          ,submit = layero.find('iframe').contents().find('#'+ submitID);

          //监听提交
          iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
            var field = data.field; //获取提交的字段
			
            //提交 Ajax 成功后，静态更新表格中的数据
            $.ajax({
				type:"post",
				url:vipEdit,
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
			
            table.reload('vip-rule'); //数据刷新
            layer.close(index); //关闭弹层
          });  
          
          submit.trigger('click');
        }
        ,success: function(layero, index){
          
        }
      });
    }
  });

   

  exports('webset', {})
});