//网站app版本发布

layui.define(["table", "form", "upload"], function (exports) {
  var $ = layui.jquery,
    table = layui.table,
    form = layui.form,
    upload = layui.upload;

  function addList(type) {
    $.ajax({
      type: "post",
      url: addonsList,
      data: { type: type },
      dataType: "json",
      success: function (res) {
        //渲染表格
        table.render({
          elem: "#addons-list",
          toolbar: "#toolbar",
          defaultToolbar: [],
          url: addonsList + "?type=" + type,
          cols: [res["col"]],
          page: true,
          limit: 10,
          height: "full-220",
          text: "对不起，加载出现异常！",
        });
      },
    });
  }

  addList("onlineAddons");

  //头工具栏事件
  table.on("toolbar(addons-list)", function (obj) {
    var checkStatus = table.checkStatus(obj.config.id);
    switch (obj.event) {
      case "installed":
        addList("installed");
        break;
      case "onlineAddons":
        addList("onlineAddons");
        break;
    }
  });

  //监听工具条
  table.on("tool(addons-list)", function (obj) {
    var data = obj.data;

    if (obj.event === "del") {
      layer.prompt(
        {
          formType: 1,
          title: "敏感操作，请验证口令",
        },
        function (value, index) {
          layer.close(index);
          layer.confirm("真的删除行么", function (index) {
            $.post(addonsDelete, { name: data.name }, function (res) {
              if (res.code == 0) {
                layer.msg(res.msg, { icon: 6, time: 2000 });
              } else {
                layer.open({
                  tiele: "修改失败",
                  content: res.msg,
                  icon: 5,
                  anim: 6,
                });
              }
            });

            table.reload("addons-list");
            layer.close(index);
          });
        }
      );
    } else if (obj.event === "edit") {
      var tr = $(obj.tr);
      layer.open({
        type: 2,
        title: "编辑插件",
        content: addonsEdit + "?id=" + data.id,
        maxmin: true,
        area: ["400px", "620px"],
        btn: ["确定", "取消"],
        yes: function (index, layero) {
          var iframeWindow = window["layui-layer-iframe" + index],
            submitID = "LAY-addons-submit",
            submit = layero
              .find("iframe")
              .contents()
              .find("#" + submitID);

          //监听提交
          iframeWindow.layui.form.on(
            "submit(" + submitID + ")",
            function (data) {
              var field = data.field; //获取提交的字段

              //提交 Ajax 成功后，静态更新表格中的数据
              $.ajax({
                type: "post",
                url: addonsEdit,
                data: field,
                daType: "json",
                success: function (res) {
                  if (res.code == 0) {
                    layer.msg(res.msg, { icon: 6, time: 2000 });
                  } else {
                    layer.open({
                      tiele: "修改失败",
                      content: res.msg,
                      icon: 5,
                      anim: 6,
                    });
                  }
                },
              });

              table.reload("addons-list"); //数据刷新
              layer.close(index); //关闭弹层
            }
          );

          submit.trigger("click");
        },
        success: function (layero, index) {},
      });
    } else if (obj.event === "start") {
      //提交 Ajax 成功后，静态更新表格中的数据
      $.ajax({
        type: "post",
        url: addonsStart,
        data: { name: data.name },
        daType: "json",
        success: function (res) {
          if (res.code == 0) {
            layer.msg(res.msg, { icon: 6, time: 2000 });
          } else {
            layer.open({
              tiele: "修改失败",
              content: res.msg,
              icon: 5,
              anim: 6,
            });
          }
        },
      });
    } else if (obj.event === "shutdown") {
      //提交 Ajax 成功后，静态更新表格中的数据
      $.ajax({
        type: "post",
        url: addonsShut,
        data: { name: data.name },
        daType: "json",
        success: function (res) {
          if (res.code == 0) {
            layer.msg(res.msg, { icon: 6, time: 2000 });
          } else {
            layer.open({
              tiele: "修改失败",
              content: res.msg,
              icon: 5,
              anim: 6,
            });
          }
        },
      });
    } else if (obj.event === "install") {
      //安装插件
      $.post(
        addonsInstall,
        { name: data.name, version: data.version },
        function (res) {
          if (res.code == 0) {
            layer.msg(res.msg, { icon: 6, time: 2000 });
          } else {
            layer.open({
              tiele: "修改失败",
              content: res.msg,
              icon: 5,
              anim: 6,
            });
          }
        }
      );
    } else if (obj.event === "config") {
      layer.open({
        type: 2,
        title: "配置插件",
        content: addonsConfig + "?name=" + data.name,
        maxmin: true,
        area: ["780px", "90%"],
        btn: ["确定", "取消"],
        yes: function (index, layero) {
          var iframeWindow = window["layui-layer-iframe" + index],
            submitID = "LAY-addons-config-submit",
            submit = layero
              .find("iframe")
              .contents()
              .find("#" + submitID);
          //监听提交
          iframeWindow.layui.form.on(
            "submit(" + submitID + ")",
            function (data) {
              var field = data.field; //获取提交的字段
              $.ajax({
                type: "post",
                url: addonsConfig,
                data: field,
                daType: "json",
                success: function (res) {
                  if (res.code == 0) {
                    layer.msg(res.msg, { icon: 6, time: 2000 });
                  } else {
                    layer.open({
                      tiele: "修改失败",
                      content: res.msg,
                      icon: 5,
                      anim: 6,
                    });
                  }
                },
              });
              layer.close(index); //关闭弹层
            }
          );
          submit.trigger("click");
        },
        success: function (layero, index) {
          var forms = layero.find("iframe").contents().find(".layui-form");
          var button = forms.find("button");
          //事件委托
          forms.on("click", "button", function (data) {
            var even = this.getAttribute("lay-event");
            var names = this.dataset.name;
            if (even == "addInput") {
              var html = '<div class="layui-form-item">\n' +
                          '<label class="layui-form-label"></label>\n' +
                          '<div class="layui-input-inline">\n' +
                          ' <input type="text" name="'+ names +'[key][]" value="" placeholder="key" autocomplete="off" class="layui-input input-double-width">\n' +
                          '</div>\n' +
                          '<div class="layui-input-inline">\n' +
                          ' <input type="text" name="'+ names +'[value][]" value="" placeholder="value" autocomplete="off" class="layui-input input-double-width">\n' +
                          '</div>\n' +
                          '<button data-name="'+ names +'" type="button" class="layui-btn layui-btn-danger layui-btn-sm removeInupt" lay-event="removeInupt">\n' +
                          ' <i class="layui-icon"></i>\n' +
                          '</button>\n' +
                          '</div>';

              $(this).parent().parent().append(html);
            } else {
              $(this).parent().remove();
            }
          });
        },
      });
    }
    table.reload("addons-list"); //数据刷新
  });

  exports("addons", {});
});
