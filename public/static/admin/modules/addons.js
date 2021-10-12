//网站app版本发布

layui.define(['table', 'form','upload'], function(exports){
  var $ = layui.jquery
  ,table = layui.table
  ,form = layui.form
  ,upload = layui.upload;

  //安装插件
	table.render({
		elem: '#addons-list',
        toolbar: '#toolbar',
		url: addonsList,
		cols:[
			col
		]
		,page: true
		,limit: 10
		,height: 'full-220'
		,text: '对不起，加载出现异常！'
	});

    //头工具栏事件
    table.on('toolbar(addons-list)', function(obj){
        var checkStatus = table.checkStatus(obj.config.id);
        switch(obj.event){
            case 'installed':
				$.post(addonsIndex + '?type=installed',function(){
					location.href = addonsIndex + '?type=installed';
				});
                $.post(addonsList + '?type=installed',{"type":"installed"});
                table.reload('addons-list', {
                    where: {"type":"installed"}
                }); //数据刷新
                break;
            case 'onlineAddons':
				$.post(addonsIndex + '?type=onlineAddons',function(){
					location.href = addonsIndex + '?type=onlineAddons';
				});
                $.post(addonsList + '?type=onlineAddons',{"type":"onlineAddons"});
                table.reload('addons-list', {
                    where: {"type":"onlineAddons"}
                }); //数据刷新
                break;
            case 'isAll':
                layer.msg(checkStatus.isAll ? '全选': '未全选');
                break;

            //自定义头工具栏右侧图标 - 提示
            case 'LAYTABLE_TIPS':
                layer.alert('这是工具栏右侧自定义的一个图标按钮');
                break;
        };
    });
	
//监听工具条
  table.on('tool(addons-list)', function(obj){
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
				url:addonsDelete,
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
		  table.reload('addons-list');
          layer.close(index);
        });
      });
    } else if(obj.event === 'edit'){
      var tr = $(obj.tr);
      layer.open({
        type: 2
        ,title: '编辑插件'
        ,content: addonsEdit + '?id='+ data.id
        ,maxmin: true
        ,area: ['400px', '620px']
        ,btn: ['确定', '取消']
        ,yes: function(index, layero){
			
          var iframeWindow = window['layui-layer-iframe'+ index]
          ,submitID = 'LAY-addons-submit'
          ,submit = layero.find('iframe').contents().find('#'+ submitID);

          //监听提交
          iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
            var field = data.field; //获取提交的字段
			
            //提交 Ajax 成功后，静态更新表格中的数据
            $.ajax({
				type:"post",
				url:addonsEdit,
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
			
            table.reload('addons-list'); //数据刷新
            layer.close(index); //关闭弹层
          });  
          
          submit.trigger('click');
        }
        ,success: function(layero, index){
          
        }
      });
    }
  });

  exports('addons', {})
});