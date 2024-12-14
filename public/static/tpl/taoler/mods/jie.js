/**

 @Name: 求解板块

 */
 
layui.define('fly', function(exports){

  var $ = layui.jquery;
  var layer = layui.layer;
  var util = layui.util;
  var laytpl = layui.laytpl;
  var form = layui.form;
  var fly = layui.fly;

  var uid = layui.cache.user.uid;

  var gather = {}, dom = {
    jieda: $('#jieda')
    ,content: $('#L_content')
    ,jiedaCount: $('#jiedaCount')
  };

  //监听专栏选择
  form.on('select(column)', function(obj){
    var value = obj.value
    ,elemQuiz = $('#LAY_quiz')
    ,tips = {
      tips: 1
      ,maxWidth: 250
      ,time: 10000
    };
    elemQuiz.addClass('layui-hide');
    if(value === '0'){
      layer.tips('下面的信息将便于您获得更好的答案', obj.othis, tips);
      elemQuiz.removeClass('layui-hide');
    } else if(value === '99'){
      layer.tips('系统会对【分享】类型的帖子予以飞吻奖励，但我们需要审核，通过后方可展示', obj.othis, tips);
    }
  });

  //提交回答
  fly.form['/jie/reply/'] = function(data, required){
    var tpl = '<li>\
      <div class="detail-about detail-about-reply">\
        <a class="fly-avatar" href="/u/{{ layui.cache.user.uid }}" target="_blank">\
          <img src="{{= d.user.avatar}}" alt="{{= d.user.username}}">\
        </a>\
        <div class="fly-detail-user">\
          <a href="/u/{{ layui.cache.user.uid }}" target="_blank" class="fly-link">\
            <cite>{{d.user.username}}</cite>\
          </a>\
        </div>\
        <div class="detail-hits">\
          <span>刚刚</span>\
        </div>\
      </div>\
      <div class="detail-body jieda-body photos">\
        {{ d.content}}\
      </div>\
    </li>'
    data.content = fly.content(data.content);
    laytpl(tpl).render($.extend(data, {
      user: layui.cache.user
    }), function(html){
      required[0].value = '';
      dom.jieda.find('.fly-none').remove();
      dom.jieda.append(html);
      
      var count = dom.jiedaCount.text()|0;
      dom.jiedaCount.html(++count);
    });
  };

  //帖子管理
  gather.jieAdmin = {
    //删帖子
    del: function(div){
      layer.confirm('确认删除该贴么？', function(index){
      layer.close(index);
      $.ajax({
          type:'post',
          url:articleDelete,
          data:{id: div.data('id')},
          dataType:'json',
          success:function(data){
            if(data.code === 0){
              layer.msg(data.msg,{
                icon:6,
                time:2000
              },function(){
                location.href = '/';
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
		});
    }
    
    // 设置置顶、加精、禁评、状态
    ,set: function(div){
      var othis = $(this);
      fly.json(articleJieset, {
        id: div.data('id')
        ,rank: othis.attr('rank')
        ,field: othis.attr('field')
      }, function(res){
        if(res.status === 0){
          layer.msg(res.msg);
            location.reload();
          }
      });
    }

    // 收藏
    ,collect: function(div){
      var othis = $(this), type = othis.data('type');
      fly.json(collection+ type +'/', {
        cid: div.data('id')
      }, function(res){
        if(type === 'add'){
          othis.data('type', 'remove').html(langCancelCollection).addClass('layui-btn-danger');
        } else if(type === 'remove'){
          othis.data('type', 'add').html(langCollection).removeClass('layui-btn-danger');
        }
      });
    }
  };

  $('body').on('click', '.jie-admin', function(){
    var othis = $(this), type = othis.attr('type');
    console.log(type)
    gather.jieAdmin[type] && gather.jieAdmin[type].call(this, othis.parent());
  });

  //异步渲染
  var asyncRender = function(){
    var div = $('.fly-admin-box'), jieAdmin = $('#LAY_jieAdmin');
    //查询帖子是否收藏
    // if(jieAdmin[0] && layui.cache.user.uid != -1){
    //   fly.json(collectionFind, {
    //     cid: div.data('id')
    //   }, function(res){
    //     jieAdmin.append('<span class="layui-btn layui-btn-xs jie-admin '+ (res.data.collection ? 'layui-btn-danger' : '') +'" type="collect" data-type="'+ (res.data.collection ? 'remove' : 'add') +'">'+ (res.data.collection ? langCancelCollection : langCollection) +'</span>');
    //   });
    // }
  }();

  //解答操作
  gather.jiedaActive = {
    zan: function(li){ //赞
      var othis = $(this), ok = othis.hasClass('zanok');
      fly.json(commentJiedaZan, {
        ok: ok
        ,id: li.data('id')
      }, function(res){
        if(res.status === 0){
          var zans = othis.find('em').html()|0;
          othis[ok ? 'removeClass' : 'addClass']('zanok');
          othis.find('em').html(ok ? (--zans) : (++zans));
        } else {
          layer.msg(res.msg, {icon: 6});
        }
      });
    }
    ,reply: function(li){ //回复
      //判断登陆
      if(uid == -1){
        layer.msg('请登录再回复', {icon: 6})
        return false;
      }
      var val = dom.content.val();
      var aite = '@'+ li.find('.fly-detail-user cite').text().replace(/\s/g, '');
      dom.content.focus()
      if(val.indexOf(aite) !== -1) return;
      // 切换编辑器 回复@赋值
      // if(taonystatus == 0) {
        dom.content.val(aite +' ' + val);
      // } else { //编辑器插件赋值
      //   tinymce.activeEditor.setContent(aite + ' .' + val);
      // }
    }
    ,accept: function(li){ //采纳
      var othis = $(this);
      layer.confirm('是否采纳该回答为最佳答案？', function(index){
        layer.close(index);
        fly.json(commentJiedaCai, {
          id: li.data('id')
        }, function(res){
          if(res.status === 0){
            $('.jieda-accept').remove();
            li.addClass('jieda-daan');
            li.find('.detail-about').append('<i class="iconfont icon-caina" title="最佳答案"></i>');
			      location.reload();
          } else {
            layer.msg(res.msg);
          }
        });
      });
    }
    ,edit: function(li){ //编辑评论
        fly.json(commentGetDa, {
          id: li.data('id')
        }, function (res) {
          var data = res.rows;
          layer.prompt({
            formType: 2
            , value: data.content
            , maxlength: 100000
            , title: '编辑回帖'
            , area: ['738px', '310px']
            , success: function (layero) {
                // fly.layEditor({
                //   elem: layero.find('textarea')
                // });
            }
          }, function (value, index) {
            fly.json(commentUpdateDa, {
              id: li.data('id')
              , content: value
            }, function (res) {
              layer.close(index);
              layer.msg(res.msg);
              li.find('.detail-body').html(fly.content(value));
            });
          });
        });
      
    }
    ,del: function(li){ //删除评论
        layer.confirm('确认删除该回答么？', function(index){
          layer.close(index);
          fly.json(commentJiedaDelete, {
            id: li.data('id')
          }, function(res){
            if(res.status === 0){
              var count = dom.jiedaCount.text()|0;
              dom.jiedaCount.html(--count);
              li.remove();
              //如果删除了最佳答案
              if(li.hasClass('jieda-daan')){
                $('.jie-status').removeClass('jie-status-ok').text('求解中');
              }
            } else {
              layer.msg(res.msg);
            }
          });
        });
      

    }
  };

  $('.jieda-reply span').on('click', function(){
    var othis = $(this), type = othis.attr('type');
    gather.jiedaActive[type].call(this, othis.parents('li'));
  });


  //定位分页
  if(/\/page\//.test(location.href) && !location.hash){
    var replyTop = $('#flyReply').offset().top - 80;
    $('html,body').scrollTop(replyTop);
  }

  exports('jie', null);
});