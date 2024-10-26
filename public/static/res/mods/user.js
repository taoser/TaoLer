/**

 @Name: 用户模块

 */
 
layui.define(['laypage', 'fly', 'element', 'flow', 'imgcom','common'], function(exports){

  var $ = layui.jquery;
  var layer = layui.layer;
  var util = layui.util;
  var laytpl = layui.laytpl;
  var form = layui.form;
  var laypage = layui.laypage;
  var fly = layui.fly;
  var flow = layui.flow;
  var element = layui.element;
  var upload = layui.upload;
  var imgcom = layui.imgcom;
  var table = layui.table;
  let common = layui.common;

  var gather = {}, dom = {
    mine: $('#LAY_mine')
    ,mineview: $('.mine-view')
    ,minemsg: $('#LAY_minemsg')
    ,infobtn: $('#LAY_btninfo')
  };


  //我的相关数据
  
    //发贴list
    var post = table.render({
        elem: '#art-post'
        ,url: artListUrl
        ,toolbar: '#toolbarPost'
		    ,title: ''
        ,height: function(){
          var otherHeight = $('.fly-header').outerHeight(); // 自定义其他区域的高度
          var footHeight = $('.footer').outerHeight(); 
          return $(window).height() - otherHeight - footHeight - 150; // 返回 number 类型
        }
        ,cols: [[
            {type: 'checkbox', fixed: 'left'},
            {type: 'numbers', fixed: 'left', title: '序号'}
            ,{field: 'title', title: '标题',minWidth: 250 ,templet: '<div><a href="{{d.url}}" target="_blank">{{-d.title}}</a></div>'}
            ,{field: 'pv', title:'浏览 <i class="layui-icon layui-icon-tips layui-font-14" lay-event="pv-tips" title="该字段开启了编辑功能" style="margin-left: 5px;"></i>', fieldTitle: 'pv', align: 'center', hide: 0, width:100, expandedMode: 'tips', edit: 'text', sort: true}
			      ,{field: 'status', title: '状态', width: 60}
			      ,{field: 'ctime', title: '发布时间', width: 160}
            ,{field: 'utime', title: '更新时间', width:160}
            ,{field: 'datas', title: '数据', width: 80}
            ,{field: 'url', title: 'link', width:100}
            ,{title: '操作', width: 150, align: 'center', toolbar: '#artTool'}
        ]]
        ,text: '对不起，加载出现异常！'
		    ,page: true
        ,limit: 20
				,limits: [20,50,100,200,300]
    });

    // 工具栏事件
    table.on('toolbar(art-post)', function(obj){
      var id = obj.config.id;
      var checkStatus = table.checkStatus(id);
      var othis = lay(this);
      var checkIds = common.checkField(obj,'id');

			if (checkIds === "") {
				layer.msg("未选中数据", {
					icon: 3,
					time: 1000
				});
				return false;
			}

      switch(obj.event){
        case 'getCheckData':
          var data = checkStatus.data;
          // layer.alert(layui.util.escape(JSON.stringify(data)));
          $.post(updateTime,{"data":data},function(res){
            if(res.code === 0){
              layer.msg(res.msg,{icon:6,time:2000});
              table.reload('art-post')
            } else {
              layer.open({title:'刷新失败',content:res.msg,icon:5,adim:6})
            }
          }
        );
        break;
        case 'deleteAll':
          var getData = table.getData(id);
          layer.confirm('确定删除吗?',{
            title:'批量删除',
            icon:3
          },function(index){
            layer.close(index);
            $.post(atrDelUrl,{"id": checkIds},function(res){
                if(res.code === 0){
                  table.reload('art-post');
                  layer.msg(res.msg,{icon:6,time:2000});
                } else {
                  layer.open({title:'删除失败',content:res.msg,icon:5,adim:6})
                }
              }
            );
          });
        break;
        case 'LAYTABLE_TIPS':
          layer.alert('自定义工具栏图标按钮');
        break;
      };
      return false
    });
  
  //收藏list
    table.render({
        elem: '#coll-post'
        ,url: collListUrl
		    ,title: ''
        ,height: function(){
          var otherHeight = $('.fly-header').outerHeight(); // 自定义其他区域的高度
          var footHeight = $('.footer').outerHeight(); 
          return $(window).height() - otherHeight - footHeight - 170; // 返回 number 类型
        }
        ,cols: [[
            {type: 'numbers', fixed: 'left'}
            ,{field: 'title', title: '标题',minWidth: 250,templet: '<div><a href="{{d.url}}" target="_blank">{{d.title}}</a></div>'}
			      ,{field: 'auther', title: '作者', width: 120}
			      ,{field: 'status', title: '状态', width: 80}
			      ,{field: 'ctime', title: '时间', width: 120}
            ,{title: '取消', width: 80, align: 'center', toolbar: '#collTool'}
        ]]
        ,text: '对不起，加载出现异常！'
		    ,page: true
        ,limit: 20
				,limits: [20,50,100,200]
    });

    // 单元格编辑事件
    table.on('edit(art-post)', function(obj){
      var field = obj.field; // 得到字段
      var value = obj.value; // 得到修改后的值
      var data = obj.data; // 得到所在行所有键值

      // 值的校验
      if(field === 'email'){
        if(!/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test(obj.value)){
          layer.tips('输入的邮箱格式不正确，请重新编辑', this, {tips: 1});
          return obj.reedit(); // 重新编辑 -- v2.8.0 新增
        }
      }

      // 编辑后续操作，如提交更新请求，以完成真实的数据更新
      $.ajax({
        type: "post",
        url: pvEdit,
        data: data,
        dataType: 'json',
        success: (res) => {
          if(res.code === 0) {
            layer.msg(res.msg, {icon: 1});
          } else {
            layer.msg(res.msg, {icon: 2});
            return false;
          }
        }
      })
      // …
            
      // 其他更新操作
      var update = {};
      update[field] = value;
      obj.update(update);
    });
 
 
  //监听行工具事件
  table.on('tool(art-post)', function(obj){
    var data = obj.data;
    var id = data.id;
    if(obj.event === 'del'){	  
      layer.confirm('确定删除吗?',{
        title:'删除文章',
        icon:3
      },function(index){
        layer.close(index);
        $.post(atrDelUrl,{"id":id},function(data){
            if(data.code == 0){
              layer.msg(data.msg,{icon:6,time:2000});
            } else {
              layer.open({title:'删除失败',content:data.msg,icon:5,adim:6})
            }
          }
        );
        table.reload('art-post');
      });
    } else if(obj.event === 'edit'){//编辑
			  location.href = artEditUrl + '?id=' + id;
		}
  
  });
  
   //监听行工具事件
  table.on('tool(coll-post)', function(obj){
	var id = obj.data.id;
	 if(obj.event === 'del'){	  
	  layer.confirm('确定取消收藏?',{
			title:'取消收藏',
			icon:3
		},function(index){
			layer.close(index);
			$.post(collDelUrl,{"id":id},function(data){
					if(data.code == 0){
						layer.msg(data.msg,{
							icon:6,
							time:2000
						});
					} else {
						layer.open({
							title:'取消失败',
							content:data.msg,
							icon:5,
							adim:6
						})
					}
				}
			);
			table.reload('coll-post');
		});
     }
  });

  //显示当前tab
  if(location.hash){
    element.tabChange('user', location.hash.replace(/^#/, ''));
  }

  element.on('tab(user)', function(){
    var othis = $(this), layid = othis.attr('lay-id');
    if(layid){
      location.hash = layid;
    }
  });
/*
  //根据ip获取城市
  if($('#L_city').val() === ''){
    $.getScript('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js', function(){
      $('#L_city').val(remote_ip_info.city||'');
    });
  }
*/
  //上传图片
  if($('.upload-img')[0]){
    layui.use('upload', function(upload){
      var avatarAdd = $('.avatar-add');

      upload.render({
        elem: '.upload-img'
		    ,accept: 'images'
		    ,acceptMime: 'image/*'
		    ,exts: 'jpg|png|gif|bmp|jpeg'
        ,url: uploadHeadImg
        ,size: 10240
		    ,auto: false
		    ,choose: function (obj) { //选择文件后的回调
				imgcom.uploads(obj);
			}
        ,before: function(){
          avatarAdd.find('.loading').show();
        }
        ,done: function(res){
          if(res.code == 0){
            layer.msg(res.msg, { icon: 6, time: 2000 },function(){
              location.reload();
            });
          } else {
            layer.open({ content: data.msg, icon: 5, adim: 6 });
          }
          avatarAdd.find('.loading').hide();
        }
        ,error: function(){
          avatarAdd.find('.loading').hide();
        }
      });
    });
  }

  //合作平台
  if($('#LAY_coop')[0]){

    //资源上传
    $('#LAY_coop .uploadRes').each(function(index, item){
      var othis = $(this);
      upload.render({
        elem: item
        ,url: '/api/upload/cooperation/?filename='+ othis.data('filename')
        ,accept: 'file'
        ,exts: 'zip'
        ,size: 30*1024
        ,before: function(){
          layer.msg('正在上传', {
            icon: 16
            ,time: -1
            ,shade: 0.7
          });
        }
        ,done: function(res){
          if(res.code == 0){
            layer.msg(res.msg, {icon: 6})
          } else {
            layer.msg(res.msg)
          }
        }
      });
    });

    //成效展示
    var effectTpl = ['{{# layui.each(d.data, function(index, item){ }}'
    ,'<tr>'
      ,'<td><a href="/u/{{ item.uid }}" target="_blank" style="color: #01AAED;">{{ item.uid }}</a></td>'
      ,'<td>{{ item.authProduct }}</td>'
      ,'<td>￥{{ item.rmb }}</td>'
      ,'<td>{{ item.create_time }}</td>'
      ,'</tr>'
    ,'{{# }); }}'].join('');

    var effectView = function(res){
      var html = laytpl(effectTpl).render(res);
      $('#LAY_coop_effect').html(html);
      $('#LAY_effect_count').html('你共有 <strong style="color: #FF5722;">'+ (res.count||0) +'</strong> 笔合作授权订单');
    };

    var effectShow = function(page){
      fly.json('/cooperation/effect', {
        page: page||1
      }, function(res){
        effectView(res);
        laypage.render({
          elem: 'LAY_effect_page'
          ,count: res.count
          ,curr: page
          ,jump: function(e, first){
            if(!first){
              effectShow(e.curr);
            }
          }
        });
      });
    };

    effectShow();

  }

  //提交成功后刷新
  fly.form['set-mine'] = function(data, required){
    layer.msg('修改成功', {
      icon: 1
      ,time: 1000
      ,shade: 0.1
    }, function(){
      location.reload();
    });
  }

  //帐号绑定
  $('.acc-unbind').on('click', function(){
    var othis = $(this), type = othis.attr('type');
    layer.confirm('整的要解绑'+ ({
      qq_id: 'QQ'
      ,weibo_id: '微博'
    })[type] + '吗？', {icon: 5}, function(){
      fly.json('/api/unbind', {
        type: type
      }, function(res){
        if(res.status === 0){
          layer.alert('已成功解绑。', {
            icon: 1
            ,end: function(){
              location.reload();
            }
          });
        } else {
          layer.msg(res.msg);
        }
      });
    });
  });


//手机设备的简单适配
  var treeMobileUser = $('.site-tree-mobile-user')
  ,shadeMobileUser = $('.site-mobile-shade-user')

  treeMobileUser.on('click', function(){
    $('body').addClass('site-mobile');
  });

  shadeMobileUser.on('click', function(){
    $('body').removeClass('site-mobile');
  });

//我的消息
    gather.minemsg = function(){
        var delAll = $('#LAY_delallmsg')
        ,tpl = '{{# var len = d.rows.length;\
    if(len === 0){ }}\
      <div class="fly-none">您暂时没有最新消息</div>\
    {{# } else { }}\
      <ul class="mine-msg">\
      {{# for(var i = 0; i < len; i++){ }}\
        <li data-id="{{d.rows[i].id}}">\
		{{# if(d.rows[i].type == 1){ }}\
          <blockquote class="layui-elem-quote"><a href= "'+ userNameJump +'?username={{ d.rows[i].name}}" target="_blank"><cite>{{ d.rows[i].name}}</cite></a>给您发了站内信<a class="sys-title" id-data="{{ d.rows[i].id}}" href="javascript:;"><cite>{{ d.rows[i].title}}</cite></a> <span class="float:right">{{ d.rows[i].read}}</span></blockquote>\
		{{# } }}\
		{{# if(d.rows[i].type == 2){ }}\
          <blockquote class="layui-elem-quote"><a href= "'+ userNameJump +'?username={{ d.rows[i].name}}" target="_blank"><cite>{{ d.rows[i].name}}</cite></a>回答了您的帖子<a target="_blank" class="art-title" id-data="{{ d.rows[i].id}}" href="{{ d.rows[i].link}}"><cite>{{ d.rows[i].title}}</cite></a> <span class="float:right">{{ d.rows[i].read}}</span></blockquote>\
		{{# } }}\
		{{# if(d.rows[i].type == 0){ }}\
		<blockquote class="layui-elem-quote">系统消息：<a class="sys-title" id-data="{{ d.rows[i].id}}" href="javascript:;"><cite>{{ d.rows[i].title}}</cite></a> <span class="float:right">{{ d.rows[i].read}}</span></blockquote>\
		{{# } }}\
          <p><span>{{d.rows[i].time}}</span><a href="javascript:;" class="layui-btn layui-btn-sm layui-btn-danger fly-delete">删除</a></p>\
        </li>\
      {{# } }}\
      </ul>\
    {{# } }}'
            ,delEnd = function(clear){
            if(clear || dom.minemsg.find('.mine-msg li').length === 0){
                dom.minemsg.html('<div class="fly-none">您暂时没有最新消息</div>');
            }
        }
    
    
    fly.json(messageFind, {}, function(res){
      var html = laytpl(tpl).render(res);
      dom.minemsg.html(html);
      if(res.rows.length > 0){
        delAll.removeClass('layui-hide');
      } else {
		  delAll.addClass('layui-hide');
	  }
    });
    
    
    //阅读后删除
    dom.minemsg.on('click', '.mine-msg li .fly-delete', function(){
      var othis = $(this).parents('li'), id = othis.data('id');
      fly.json(messageRemove, {
        id: id
      }, function(res){
        if(res.status === 0){
          othis.remove();
          delEnd();
        }
      });
    });

    //删除全部
    $('#LAY_delallmsg').on('click', function(){
      var othis = $(this);
      layer.confirm('确定清空吗？', function(index){
        fly.json(messageRemove, {
          id: true
        }, function(res){
          if(res.status === 0){
            layer.close(index);
            othis.addClass('layui-hide');
            delEnd(true);
          }
        });
      });
    });

  };

  dom.minemsg[0] && gather.minemsg();

  exports('user', null);
  
});