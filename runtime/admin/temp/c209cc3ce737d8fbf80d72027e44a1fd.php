<?php /*a:1:{s:66:"E:\github\TaoLer\app\admin\view\addons\addonfactory\index\add.html";i:1730971718;}*/ ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>新增页面</title><link rel="stylesheet" href="/static/component/pear/css/pear.css" /></head><body><form class="layui-form" action=""><div class="mainBox"><div class="main-container"><div class="layui-form-item"><label class="layui-form-label">名称</label><div class="layui-input-block"><input type="text" name="name" lay-verify="required" placeholder="请输入小写纯英文字符" autocomplete="off" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">标题</label><div class="layui-input-block"><input type="text" name="title" lay-verify="required" placeholder="请输入插件标题" autocomplete="off" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">简介</label><div class="layui-input-block"><textarea name="description" lay-verify="required" placeholder="请输入简介"  class="layui-textarea"></textarea></div></div><div class="layui-form-item"><label class="layui-form-label">作者</label><div class="layui-input-block"><input type="text" name="author" lay-verify="required" placeholder="请输入作者名" autocomplete="off" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">版本</label><div class="layui-input-block"><input type="text" name="version" lay-verify="required" placeholder="请输入版本号" autocomplete="off" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">插件前端</label><div class="layui-input-block"><input type="checkbox" name="controller" title="控制器"  value="1"><input type="checkbox" name="model" title="模型"  value="1"><input type="checkbox" name="view" title="视图"  value="1"><input type="checkbox" name="route" title="路由"  value="1"><input type="checkbox" name="menu" title="菜单"  value="1"></div></div><div class="layui-form-item"><label class="layui-form-label">管理后端</label><div class="layui-input-block"><input type="checkbox" name="admin_controller" title="控制器"  value="1"><input type="checkbox" name="admin_model" title="模型"  value="1"><input type="checkbox" name="admin_view" title="视图"  value="1"><input type="checkbox" name="admin_validate" title="验证"  value="1"></div></div><div class="layui-form-item  layui-form-text"><label class="layui-form-label">静态资源</label><div class="layui-input-block"><input type="checkbox" name="js" title="js" value="1"><input type="checkbox" name="css" title="css"  value="1"><input type="checkbox" name="img" title="img"  value="1"></div></div><div class="layui-form-item  layui-form-text"><label class="layui-form-label">应用插件</label><div class="layui-input-block"><input type="checkbox" name="app" title="app" value="1"></div></div></div></div><div class="bottom"><div class="button-container"><button type="submit" class="pear-btn pear-btn-primary pear-btn-sm" lay-submit="" lay-filter="addon-save"><i class="layui-icon layui-icon-ok"></i>          提交
        </button><button type="reset" class="pear-btn pear-btn-sm"><i class="layui-icon layui-icon-refresh"></i>          重置
        </button></div></div></form><script src="/static/component/layui/layui.js"></script><script src="/static/component/pear/pear.js"></script><script>      layui.use(['upload'], function(){
        var $ = layui.jquery
        ,upload = layui.upload ;
        let form = layui.form;

        let ADD_URL = "<?php echo url('addons.addonfactory.index/add'); ?>";

        form.on('submit(addon-save)', function(data) {
          $.ajax({
            url: ADD_URL,
            data: JSON.stringify(data.field),
            dataType: 'json',
            contentType: 'application/json',
            type: 'post',
            success: function(result) {
              if (result.code === 0) {
                layer.msg(result.msg, {
                  icon: 1,
                  time: 1000
                }, function() {
                  parent.layer.close(parent.layer.getFrameIndex(window.name)); //关闭当前页
                  parent.layui.table.reload("addon-table");
                });
              } else {
                layer.msg(result.msg, {
                  icon: 2,
                  time: 1000
                });
              }
            }
          })
          return false;
        });

        upload.render({
          elem: '#layuiadmin-upload-app'
          ,url: 'uploadImg'
          ,accept: 'images'
          ,data: {type:'image'}
          ,field: 'file'
          ,method: 'get'
          ,acceptMime: 'image/*'
          ,done: function(res){
              $("input[name='app_image']").val(res.image);
              if(res.code == 0){
                  layer.msg(res.msg,{
                      icon:6,
                      tiye:2000
                  });
              } else {
                  layer.open({
                      title:"上传失败",
                      content:res.msg,
                      icon:5,
                      anim:6
                  });
              }
          }
        });
      })
      </script></body></html>