<?php /*a:1:{s:68:"E:\github\TaoLer\app\admin\view\addons\addonfactory\index\index.html";i:1730971718;}*/ ?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>用户管理</title><link rel="stylesheet" href="/static/component/pear/css/pear.css" /></head><body class="pear-container"><div class="layui-card"><div class="layui-card-body"><form class="layui-form" action=""><div class="layui-row layui-col-space15 "><div class="layui-col-md3"><label class="layui-form-label">插件名</label><div class="layui-input-block"><input type="text" name="name" placeholder="请输入" autocomplete="off" class="layui-input"></div></div><div class="layui-col-md3"><button class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="addon-query"><i class="layui-icon layui-icon-search"></i>                            查询
                        </button><button type="reset" class="pear-btn pear-btn-md"><i class="layui-icon layui-icon-refresh"></i>                            重置
                        </button></div></div></form></div></div><div class="layui-card"><div class="layui-card-body"><table id="addon-table" lay-filter="addon-table"></table></div></div><script type="text/html" id="addon-toolbar"><button class="pear-btn pear-btn-primary pear-btn-md" lay-event="add"><i class="layui-icon layui-icon-add-1"></i>                新增
            </button><button class="pear-btn pear-btn-danger pear-btn-md" lay-event="batchRemove"><i class="layui-icon layui-icon-delete"></i>                删除
            </button></script><script type="text/html" id="addon-bar"><button class="pear-btn pear-btn-primary pear-btn-sm" lay-event="zip"><i class="layui-icon layui-icon-engine">打包</i></button><button class="pear-btn pear-btn-danger pear-btn-sm" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button></script><script src="/static/component/layui/layui.js"></script><script src="/static/component/pear/pear.js"></script><script>            layui.use(['table','form','common'], function(){
                var $ = layui.jquery
                ,table = layui.table
                ,form = layui.form;
                let common = layui.common;

                table.render({
                    elem: '#addon-table',
                    url: "<?php echo url('addons.addonfactory.index/index'); ?>",
                    cols:[[
                        {type: 'numbers', fixed: 'left'},
                        {field: 'name',title: '插件', width: 150},
                        {field: 'title',title: '插件名', width: 150},
                        {field: 'author',title: '作者', width: 100},
                        {field: 'description',title: '简介', minWidth: 200},
                        {field: 'create_time',title: '时间', width: 150},
                        {title: '操作', width: 250, align:'center', toolbar: '#addon-bar'}
                    ]],
                    skin: 'line',
                    toolbar: '#addon-toolbar'
                    ,page: true
                    ,limit: 10
                    ,text: '对不起，加载出现异常！'
                });

                form.on('submit(addon-query)', function(data) {
                    table.reload('addon-table', {
                        where: data.field,
                        page: {
                            curr: 1 //重新从第 1 页开始
                        }
                    })
                    return false;
                });

                table.on('tool(addon-table)', function(obj) {
                    if (obj.event === 'remove') {
                      window.remove(obj);
                    } else if (obj.event === 'zip') {
                      window.zip(obj);
                    }
                });

                table.on('toolbar(addon-table)', function(obj) {
                    if (obj.event === 'add') {
                      window.add();
                    } else if (obj.event === 'refresh') {
                      window.refresh();
                    } else if (obj.event === 'batchRemove') {
                      window.batchRemove(obj);
                    }
                });

                window.add = function() {
                    layer.open({
                      type: 2,
                      title: '新增',
                      shade: 0.1,
                      area: [common.isModile()?'100%':'600px', common.isModile()?'100%':'700px'],
                      content: 'add.html'
                    });
                }

                window.zip = function (obj) {
                    // console.log(obj)
                    layer.confirm('确定打包文件?', function(index){
                        $.ajax({
                            type:'post',
                            url: "<?php echo url('addons.addonfactory.index/addonZip'); ?>",
                            data: {id:obj.data.id, name: obj.data.name, version: obj.data.version},
                            dataType:'json',
                            success:function(data){
                                if(data.code === 0){
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
                        table.reload('addon-table');
                        layer.close(index);
                    });
                }

                window.remove = function () {
                    layer.confirm('真的删除行么', function(index){
                        $.ajax({
                            type:'post',
                            url:"<?php echo url('addons.addonfactory.index/delete'); ?>",
                            data:{id:data.id, name: data.name},
                            dataType:'json',
                            success:function(data){
                                if(data.code === 0){
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
                        table.reload('addon-table');
                        layer.close(index);
                    });
                }

            });
        </script></body></html>