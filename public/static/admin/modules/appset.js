//网站app版本发布

layui.define(['table', 'form','upload'], function(exports){
  var $ = layui.$
  ,table = layui.table
  ,form = layui.form
  ,upload = layui.upload;

  //版本推送
	table.render({
		elem: '#version-list',
		url: versionIndex,
		limit: 5,
		cols:[[
			{type: 'numbers', fixed: 'left'},
			{field: 'pname',title: '应用名', width: 100},
			{field: 'version_name',title: '版本', width: 100},
			{field: 'version_resume',title: '简介', minWidth: 200},
			{field: 'check',title: '状态', templet: '#buttonCheck', width: 95, align: 'center'},
			{field: 'ctime',title: '时间', width: 150},
			{title: '操作', width: 150, align:'center', fixed: 'right', toolbar: '#version-tool'}
		]]
		,page: true
		,limit: 15
		,height: 'full-220'
		,text: '对不起，加载出现异常！'
	});
	
//监听工具条
  table.on('tool(version-list)', function(obj){
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
				url:versionDelete,
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
		  table.reload('version-list');
          layer.close(index);
        });
      });
    } else if(obj.event === 'edit'){
      var tr = $(obj.tr);
      layer.open({
        type: 2
        ,title: '编辑版本'
        ,content: versionEdit + '?id='+ data.id
        ,maxmin: true
        ,area: ['480px', '420px']
        ,btn: ['确定', '取消']
        ,yes: function(index, layero){
			
          var iframeWindow = window['layui-layer-iframe'+ index]
          ,submitID = 'LAY-version-submit'
          ,submit = layero.find('iframe').contents().find('#'+ submitID);

          //监听提交
          iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
            var field = data.field; //获取提交的字段
			
            //提交 Ajax 成功后，静态更新表格中的数据
            $.ajax({
				type:"post",
				url:versionEdit,
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
			
            table.reload('version-list'); //数据刷新
            layer.close(index); //关闭弹层
          });  
          
          submit.trigger('click');
        }
        ,success: function(layero, index){
          
        }
      });
    }
  });

  exports('appset', {})
});