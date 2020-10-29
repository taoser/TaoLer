//网站app版本发布

layui.define(['table', 'form','upload'], function(exports){
  var $ = layui.$
  ,table = layui.table
  ,form = layui.form
  ,upload = layui.upload;

  //版本推送
	table.render({
		elem: '#addons-list',
		url: addonsIndex,
		limit: 5,
		cols:[[
			{type: 'numbers', fixed: 'left'},
			{field: 'addons_name',title: '插件', width: 150},
			{field: 'addons_version',title: '版本', width: 100},
			{field: 'addons_auther',title: '作者', width: 100},
			{field: 'addons_resume',title: '简介', minWidth: 200},
			{field: 'addons_price',title: '价格(元)'},
			{field: 'addons_status',title: '状态', width: 100},
			{field: 'ctime',title: '时间', width: 150},
			{title: '操作', width: 250, align:'center', fixed: 'right', toolbar: '#addons-tool'}
		]]
		,page: true
		,limit: 10
		,height: 'full-220'
		,text: '对不起，加载出现异常！'
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