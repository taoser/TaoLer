//网站通知

layui.define(['table', 'form', 'layedit','upload'], function(exports){
  var $ = layui.$
  ,table = layui.table
  ,form = layui.form
  ,layedit = layui.layedit
  ,upload = layui.upload;

  //编辑器
  	var index = layedit.build('L_content',{
		height: 180 //设置编辑器高度
		,tool: [
		  'strong' //加粗
		  ,'italic' //斜体
		  ,'underline' //下划线
		  ,'del' //删除线
		  ,'|' //分割线
		  ,'left' //左对齐
		  ,'center' //居中对齐
		  ,'right' //右对齐
		  ,'link' //超链接
		  ,'unlink' //清除链接
		  ,'face' //表情
		  ,'image' //插入图片
		],
	});
	
	//得到编辑器内容异步到表单中
	form.verify({
		content: function(value){
			return layedit.sync(index);
		}
	});
	
	//通知列表
		table.render({
			elem: '#notice-list',
			url: noticeIndex,
			limit: 5,
			cols:[[
				{type: 'numbers', fixed: 'left'},
				{field: 'type',title: '类型'},
				{field: 'title',title: '标题'},
				{field: 'user_id',title: '发信ID'},
				{field: 'content',title: '内容'},
				{field: 'ctime',title: '时间'},
				{title: '操作', width: 150, align:'center', fixed: 'right', toolbar: '#notice-tool'}
			]]
			,page: true
			,limit: 15
			,height: 'full-220'
			,text: '对不起，加载出现异常！'
		});
	//发站内通知信息
	form.on('select(type)', function(data){
	var tpl = '<div class="layui-col-md12">\
				   <label for="L_title" class="layui-form-label">收件人</label>\
				   <div class="layui-input-block">\
						<input type="text" id="receve_id" name="receve_id" required lay-verify="required" autocomplete="off" class="layui-input" >\
				   </div>\
				</div>';
		//如果选择是用户追加收件人		
		if(data.value == 1){
			$(this).parents('div .layui-col-md3').next('div').after(tpl);
		}else{
			$(this).parents('div .layui-col-md3').nextAll('div .layui-col-md12').remove();
		}
	});
	
	//发布通知
	 form.on('submit(notice-add)', function(data){	
		var field = data.field;
			$.ajax({
				type:"post",
				url:noticeAdd,
				data:field,
				dataType:"json",
				success:function (data){
					if (data.code == 0) {
                        //$('#L_title').text('');
						//$('#L_content').text('');
						layer.msg(data.msg,{
							icon:6,
							time:2000
						});
					} else {
						layer.open({
							content:data.msg,
							icon:5,
							anim:6
						});
					}
				}
			});
			
		$('#L_title').val('');
		$('#L_content').val('');
		table.reload('notice-list'); //数据刷新	
	return false;	
	});

	
		 //监听工具条
  table.on('tool(notice-list)', function(obj){
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
				url:noticeDelete,
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
		  table.reload('notice-list');
          layer.close(index);
        });
      });
    } else if(obj.event === 'edit'){
      var tr = $(obj.tr);
      layer.open({
        type: 2
        ,title: '编辑通知'
        ,content: noticeEdit +'?id='+ data.id
        ,maxmin: true
        ,area: ['500px', '450px']
        ,btn: ['确定', '取消']
        ,yes: function(index, layero){
			
          var iframeWindow = window['layui-layer-iframe'+ index]
          ,submitID = 'notice-edit'
          ,submit = layero.find('iframe').contents().find('#'+ submitID);

          //监听提交
          iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
            var field = data.field; //获取提交的字段
			
            //提交 Ajax 成功后，静态更新表格中的数据
            $.ajax({
				type:"post",
				url:noticeEdit,
				data:{id:field.id,title:field.title,content:field.content,type:field.type},
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
			
            table.reload('notice-list'); //数据刷新
            layer.close(index); //关闭弹层
          });  
          
          submit.trigger('click');
        }
        ,success: function(layero, index){
          
        }
      });
    }
  });
   

  exports('notice', {})
});