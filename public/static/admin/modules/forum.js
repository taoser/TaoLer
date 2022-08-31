
layui.define(['table', 'form', 'treeTable'], function(exports){
  var $ = layui.$
  ,table = layui.table
  ,form = layui.form;
  var treeTable = layui.treeTable;

  //帖子管理
var forms = table.render({
    elem: '#LAY-app-forum-list'
    ,url: forumList //帖子数据接口
    ,cols: [[
      {type: 'checkbox'}
      ,{field: 'id', width: 60, title: 'ID', sort: true}
      ,{field: 'poster', title: '账号',width: 80}
      ,{field: 'avatar', title: '头像', width: 60, templet: '#avatarTpl'}
      ,{field: 'title', title: '标题', minWidth: 180,templet: '<div><a href="{{ d.url }}" target="_blank">{{d.title}}</a></div>'}
      ,{field: 'content', title: '内容', minWidth: 200}
      ,{field: 'posttime', title: '时间',width: 120, sort: true}
      ,{field: 'top', title: '置顶', templet: '#buttonTpl', width: 80, align: 'center'}
      ,{field: 'hot', title: '加精', templet: '#buttonHot', width: 80, align: 'center'}
      ,{field: 'reply', title: '禁评', templet: '#buttonReply', width: 80, align: 'center'}
      ,{field: 'check', title: '审帖', templet: '#buttonCheck', width: 95, align: 'center'}
      ,{title: '操作', width: 110, align: 'center', toolbar: '#table-forum-list'}
    ]]
    ,page: true
    ,limit: 15
    ,limits: [10, 15, 20, 25, 30]
    ,text: '对不起，加载出现异常！'
  });
  
  //监听工具条
  table.on('tool(LAY-app-forum-list)', function(obj){
    var data = obj.data;
    if(obj.event === 'del'){
      layer.confirm('确定删除此条帖子？', function(index){
        //obj.del();
        $.ajax({
          type:'post',
          url:forumListdel,
          data:{id:data.id},
          dataType:'json',
          success:function(data){
            if(data.code == 0){
              layer.msg(data.msg,{
                icon:6,
                time:2000
              },function(){
                forms.reload();
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
    } else if(obj.event === 'edit'){
      var tr = $(obj.tr);

      layer.open({
        type: 2
        ,title: '编辑帖子'
        ,content: forumEdit + '?id='+ data.id
        ,area: ['100%', '100%']
        ,btn: ['确定', '取消']
        ,resize: false
        ,yes: function(index, layero){
          var iframeWindow = window['layui-layer-iframe'+ index]
          ,submitID = 'article-edit'
          ,submit = layero.find('iframe').contents().find('#'+ submitID)

          //监听提交
          iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
            var field = data.field; //获取提交的字段
            //tag
            var numArr = new Array();
            layero.find('iframe').contents().find(".layui-btn-container").children("button").each(function () {
                numArr.push($(this).val()); //添加至数组
              });
            field.tags = numArr.lenth ? "" : numArr.join(",");

            $.ajax({
              type:"post",
              url: forumEdit,
              data: field,
              daType:"json",
              success:function (data){
                if (data.code == 0) {
                  layer.msg(data.msg,{icon:6,time:2000});
                } else {
                  layer.open({title:'编辑失败',content:data.msg,icon:5,anim:6});
                };
              }
            });

            table.reload('LAY-app-forum-list'); //数据刷新
            layer.close(index); //关闭弹层
          });  
          
          submit.trigger('click');
        }
      });
    }
  });

  //评论管理
  table.render({
    elem: '#LAY-app-forumreply-list'
    ,url: forumReplys
    ,cols: [[
      {type: 'checkbox'}
      ,{field: 'id', width: 80, title: 'ID', sort: true}
      ,{field: 'title', title: '标题',minWidth: 150, templet: '<div><a href="{{d.url}}" target="_blank">{{d.title}}</a></div>'}
      ,{field: 'replyer', title: '账号', width: 80}
      ,{field: 'avatar', title: '头像', width: 60, templet: '#imgTpl'}
      ,{field: 'content', title: '评论', minWidth: 200}
      ,{field: 'replytime', title: '回复时间', width: 120, sort: true}
	    ,{field: 'check', title: '审核', templet: '#buttonCheck', width: 100}
      ,{title: '操作', width: 60, align: 'center', toolbar: '#table-forum-replys'}
    ]]
    ,page: true
    ,limit: 15
    ,limits: [10, 15, 20, 25, 30]
    ,text: '对不起，加载出现异常！'
  });
  
  //监听工具条
  table.on('tool(LAY-app-forumreply-list)', function(obj){
    var data = obj.data;
    if(obj.event === 'del'){
      layer.confirm('确定删除此条评论？', function(index){
        //obj.del();
		$.ajax({
				type:'post',
				url:forumRedel,
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
    } else if(obj.event === 'edit'){
      var tr = $(obj.tr);

      layer.open({
        type: 2
        ,title: '编辑评论'
        ,content: '/admin/Forum/replysform.html'
        ,area: ['550px', '350px']
        ,btn: ['确定', '取消']
        ,resize: false
        ,yes: function(index, layero){
          //获取iframe元素的值
          var othis = layero.find('iframe').contents().find("#layuiadmin-form-replys");
          var content = othis.find('textarea[name="content"]').val();
          
          //数据更新
          obj.update({
            content: content
          });
          layer.close(index);
        }
        ,success: function(layero, index){
            
        }

      });
    }
  });

    // 渲染分类表格
    var insTb = treeTable.render({
      elem: '#Article-cate-list',
      url: forumTags,
      //toolbar: 'default',
      //height: 'full-200',
      tree: {
          iconIndex: 2,
          isPidData: true,
          idName: 'id',
          pidName: 'pid'
      },
      defaultToolbar: ['filter', 'print', 'exports'],
      cols: [
          [
            {type: 'numbers'},
            {type: 'checkbox'}
            ,{field: 'tags', title: '分类名', minWidth: 200}
            ,{field: 'ename', title: 'EN别名', width: 100}
            ,{field: 'detpl',title: '模板', align: 'center',width: 100,templet: '#inputSel'}
            ,{title: '图标', align: 'center',width: 50,templet: '<p><i class="layui-icon {{d.icon}}"></i></p>'}
            ,{field: 'is_hot', title: '热门', align: 'center',width: 50, templet: '#buttonHot'}
            ,{field: 'desc', title: '描述', minWidth: 200}
            ,{field: 'id', title: 'ID',width: 60}
            ,{field: 'sort', title: '排序', width: 80, sort: true}
            ,{title: '操作', width: 120, align: 'center', toolbar: '#layuiadmin-app-cont-tagsbar'}
          ]
      ],
      style: 'margin-top:0;'
  });

    //监听工具条
    treeTable.on('tool(Article-cate-list)', function(obj){
        var data = obj.data;
        if(obj.event === 'del'){
            layer.confirm('确定删除此分类？', function(index){
                $.ajax({
                    type:'post',
                    url:forumTagsDelete,
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
                //obj.del();
                layer.close(index);
            });
        } else if(obj.event === 'edit'){
            var tr = $(obj.tr);
            layer.open({
                type: 2
                ,title: '编辑分类'
                ,content: forumTagsForm + '?id='+ data.id
                ,area: ['400px', '450px']
                ,btn: ['确定', '取消']
                ,yes: function(index, layero){
                    //获取iframe元素的值
                    var othis = layero.find('iframe').contents().find("#layuiadmin-app-form-tags")
                        ,pid = othis.find('input[name="pid"]').val()
                        ,sort = othis.find('input[name="sort"]').val()
                        ,tags = othis.find('input[name="tags"]').val()
                        ,ename = othis.find('input[name="ename"]').val()
                        ,detpl = othis.find('select[name="detpl"]').val()
                        ,icon = othis.find('input[name="icon"]').val()
                        ,desc = othis.find('input[name="desc"]').val();

                    if(!tags.replace(/\s/g, '')) return;

                    $.ajax({
                        type:"post",
                        url:forumTagsForm,
                        data:{"id":data.id,"pid":pid,"sort":sort,"catename":tags,"ename":ename,"detpl":detpl,"icon":icon,"desc":desc},
                        daType:"json",
                        success:function (data){
                            if (data.code == 0) {
                                layer.msg(data.msg,{
                                    icon:6,
                                    time:2000
                                }, function(){
                                    location.reload();
                                });
                            } else {
                                layer.open({
                                    tiele:'修改失败',
                                    content:data.msg,
                                    icon:5,
                                    anim:6
                                });
                            }
                        }
                    });
                    /*
                              obj.update({
                                tags: tags
                                ,ename: ename
                                ,sort: sort
                              });
                    */
                    treeTable.reload('Article-cate-list'); //数据刷新
                    layer.close(index);
                }
                ,success: function(layero, index){
                    //给iframe元素赋值
                    var othis = layero.find('iframe').contents().find("#layuiadmin-app-form-tags").click();
                        othis.find('input[name="pid"]').val(data.pid)
                        ,othis.find('input[name="sort"]').val(data.sort)
                        ,othis.find('input[name="tags"]').val(data.tags)
                        ,othis.find('input[name="ename"]').val(data.ename)
						            ,othis.find('input[name="icon"]').val(data.icon)
                        ,othis.find('input[name="desc"]').val(data.desc);
                }
            });
        }
    });
  
  exports('forum', {})
});