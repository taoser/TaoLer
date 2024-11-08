<?php /*a:1:{s:62:"E:\github\TaoLer\app\admin\view\addons\ipusher\auth\index.html";i:1726281197;}*/ ?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>授权管理</title><link rel="stylesheet" href="/static/component/pear/css/pear.css" /></head><body class="pear-container"><?php if($reg): ?><div class="layui-card"><div class="layui-card-body"><form class="layui-form" action=""><div class="layui-row layui-col-space15 "><div class="layui-col-md3"><label class="layui-form-label">用户</label><div class="layui-input-block"><input class="layui-input" type="text" name="username" value=""></div></div><div class="layui-col-md3"><button class="pear-btn pear-btn-md pear-btn-primary" lay-submit lay-filter="forum-query"><i class="layui-icon layui-icon-search"></i>                    查询
                  </button><button type="reset" class="pear-btn pear-btn-md"><i class="layui-icon layui-icon-refresh"></i>                    重置
                  </button></div></div></form></div></div><div class="layui-card"><div class="layui-card-body"><table id="auth-table" lay-filter="auth-table" ></table></div></div><?php else: ?><div class="layui-card"><div class="layui-card-body"><form class="layui-form"><div class="layui-form-item"><label class="layui-form-label">应用Id</label><div class="layui-input-block"><input type="text" name="appid" lay-verify="required" placeholder="请输入appid" autocomplete="off" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">Key</label><div class="layui-input-block"><input type="text" name="appkey" lay-verify="required" placeholder="请输入appkey" autocomplete="off" class="layui-input"></div></div><div class="layui-form-item"><label class="layui-form-label">Secret</label><div class="layui-input-block"><input type="text" name="secretkey" lay-verify="required" placeholder="请输入secretKey" autocomplete="off" class="layui-input"></div></div><div class="layui-form-item"><div class="layui-input-block"><button type="submit" class="layui-btn" lay-submit lay-filter="reg">注册应用</button><button type="reset" class="layui-btn layui-btn-primary">重置</button></div></div><div class="layui-form-item"><div class="layui-input-block"><a href="https://www.aieok.com/addons/myauth/myapp/index" target="_blank" style="color:red;">获取key,请去申请！</a></div></form></div></div><?php endif; ?><script type="text/html" id="forum-toolbar"><button class="pear-btn pear-btn-primary pear-btn-md" lay-event="reset">            认证重置
          </button><!-- <button class="pear-btn pear-btn-danger pear-btn-md" lay-event="batchRemove"><i class="layui-icon layui-icon-delete"></i>            删除
          </button> --></script><script type="text/html" id="activeTpl"><div>            {{#  if(d.active === 1){ }}
            <span style="color:rgb(242, 144, 23)">已申请 待审核</span>            {{# } else if(d.active === 2) { }}
            <span>已审核 待激活</span>            {{# } else if(d.active === 3) { }}
            <span>已激活</span>            {{# } else { }}
            <span>未申请</span>            {{#  } }}
          </div></script><script type="text/html" id="buttonCheck"><input type="checkbox" name="status" value="{{d.id}}" lay-skin="switch" lay-filter="status" lay-text="正常|{{ d.status == 0 ? '待审' : '禁用' }}" {{ d.status == 1 ? 'checked' : '' }}></script><script type="text/html" id="auth-table-bar"><button class="pear-btn pear-btn-primary pear-btn-sm" lay-event="edit"><i class="layui-icon layui-icon-edit"></i></button><button class="pear-btn pear-btn-danger pear-btn-sm" lay-event="remove"><i class="layui-icon layui-icon-delete"></i></button></script><script src="/static/component/layui/layui.js"></script><script src="/static/component/pear/pear.js"></script><script>        const HUODONG_List = "<?php echo url('addons.ipusher.auth/list'); ?>";

        layui.use(['toast','jquery','form', 'table','common'], function(){
                var $ = layui.jquery
                ,form = layui.form
                ,table = layui.table;
                let common = layui.common;
                var toast = layui.toast;
                let laydate = layui.laydate;

                //执行一个laydate实例
                laydate.render({
                  elem: '#time' //指定元素
                  ,type: 'date'
                });


                let cols = [
                  [
                    {title: '序号', type: 'numbers'}
                    //,{title: '序号', type: 'checkbox'}
                    ,{field: 'id', title: 'ID', width: 60}
                    ,{field: 'name', title: '用户', minWidth: 100}
                    ,{field: 'code', title: '认证码',  width: 120}
                    ,{field: 'month', title: '申请周期/月',  width: 120, align: 'center'}
                    ,{field: 'active', title: '激活状态', align: 'center', minWidth: 250, templet: '#activeTpl'}
                    ,{field: 'expires_time', title: '到期时间', align: 'center',width: 180}
                    ,{field: 'create_time', title: '申请时间', width: 180, align: 'center'}
                    ,{field: 'status', title: '状态', templet: '#buttonCheck', width: 95, align: 'center'}
                    ,{title: '操作', width: 150, align: 'center', toolbar: '#auth-table-bar'}
                  ]
                ];

              table.render({
                elem: '#auth-table',
                url: HUODONG_List,
                page: true,
                cols: cols,
                limit: 15,
                skin: 'line',
                lineStyle: 'height: 100px;',
                toolbar: '#forum-toolbar',
                defaultToolbar: [{
                  title: '刷新',
                  layEvent: 'refresh',
                  icon: 'layui-icon-refresh',
                }, 'filter', 'print', 'exports']
              });

              table.on('tool(auth-table)', function(obj) {
                if (obj.event === 'remove') {
                  window.remove(obj);
                } else if (obj.event === 'edit') {
                  window.edit(obj);
                }
              });

              table.on('toolbar(auth-table)', function(obj) {
                if (obj.event === 'add') {
                  window.add();
                } else if (obj.event === 'refresh') {
                  window.refresh();
                } else if (obj.event === 'batchRemove') {
                  window.batchRemove(obj);
                } else if (obj.event === 'reset') {
                  window.reset(obj);
                }

              });

              form.on('submit(forum-query)', function(data) {
                table.reload('auth-table', {
                  where: data.field,
                    page: {
                      curr: 1 //重新从第 1 页开始
                  }
                })
                return false;
              });

			form.on('submit(reg)', function(data) {
                $.post("<?php echo url('addons.ipusher.auth/reg'); ?>", data.field, function(res){
					if(res.code === 0){
                        layer.msg(res.msg, {
                                icon: 1,
                                time: 1000
                            }, function() {
                                location.reload();
                            });
						} else {
							layer.open({title:'注册失败',content:res.msg,icon:5,adim:6})
						}
				    })
                    return false;
              });

              // 监听审核
              form.on('switch(status)', function(obj){
                $.post("<?php echo url('addons.ipusher.auth/check'); ?>",{id:obj.value, status: obj.elem.checked ? 1 : -1},function(res){
                  if(res.code === 0){
                    layer.msg(res.msg,{icon:res.icon,time:2000})
                  } else {
                    layer.open({title:'审核失败',content:res.msg,icon:5,adim:6})
                  }
                  table.reload('auth-table');
                });
              });

              window.edit = function(obj) {
                layer.open({
                  type: 2,
                  title: '授权操作',
                  shade: 0.1,
                  area: ['500px', '450px'],
                  content: `<?php echo url('addons.ipusher.auth/edit'); ?>?id=${obj.data.id}`
                });
              }

                window.remove = function(obj) {

                  layer.confirm('确定要删除?', {
                      icon: 3,
                      title: '提示'
                    }, function(index) {
                      layer.close(index);
                      let loading = layer.load();
                      $.ajax({
                          url: "<?php echo url('addons.ipusher.auth/delete'); ?>?id=" + obj.data['id'],
                          dataType: 'json',
                          type: 'delete',
                          success: function(result) {
                              layer.close(loading);
                              if (result.code === 0) {
                                  layer.msg(result.msg, {
                                      icon: 1,
                                      time: 1000
                                  }, function() {
                                      obj.del();
                                  });
                              } else {
                                  layer.msg(result.msg, {
                                      icon: 2,
                                      time: 1000
                                  });
                              }
                          }
                      })
                  });
              }

                window.batchRemove = function(obj) {
                  var checkIds = common.checkField(obj,'id');
                  if (checkIds === "") {
                      layer.msg("未选中数据", {
                          icon: 3,
                          time: 1000
                      });
                      return false;
                  }

                  layer.confirm('确定要删除?', {
                      icon: 3,
                      title: '提示'
                  }, function(index) {
                      layer.close(index);
                      let loading = layer.load();
                      $.ajax({
                          url: "<?php echo url('addons.ipusher.auth/delete'); ?>?id=" + checkIds,
                          dataType: 'json',
                          type: 'delete',
                          data:{"id":checkIds},
                          success: function(result) {
                              layer.close(loading);
                              if (result.code === 0) {
                                  layer.msg(result.msg, {
                                      icon: 1,
                                      time: 1000
                                  }, function() {
                                      table.reload('auth-table');
                                  });
                              } else {
                                  layer.msg(result.msg, {
                                      icon: 2,
                                      time: 1000
                                  });
                              }
                          }
                      })
                  });
              }

              window.reset = function(obj) {
                $.get("<?php echo url('addons.ipusher.auth/regCheck'); ?>",function(res){
                  if(res.code == -1) {
                      layer.confirm(res.msg + '确定要重置?', {
                        icon: 3,
                        title: '提示'
                    }, function(index) {
                        layer.close(index);
                        let loading = layer.load();
                        $.ajax({
                            url: "<?php echo url('addons.ipusher.auth/reReg'); ?>",
                            dataType: 'json',
                            type: 'delete',
                            data:{"id":1},
                            success: function(result) {
                                layer.close(loading);
                                if (result.code === 0) {
                                    layer.msg(result.msg, {
                                        icon: 1,
                                        time: 1000
                                    }, function() {
                                      location.reload();
                                    });
                                } else {
                                    layer.msg(result.msg, {
                                        icon: 2,
                                        time: 1000
                                    });
                                }
                            }
                        })
                    });
                  } else {
                    layer.msg('认证正常，无需重置', {
                        icon: 1,
                        time: 1000
                    });
                  }
                })
                return false;
                
            }

                window.refresh = function(param) {
                  table.reload('auth-table');
                }

            });
        </script></body></html>