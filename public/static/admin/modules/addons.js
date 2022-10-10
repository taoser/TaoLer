//网站app版本发布

layui.define(["table", "form", "upload","notify","hxNav"], function (exports) {
  var $ = layui.jquery,
    table = layui.table,
    form = layui.form,
    upload = layui.upload;
  var notify = layui.notify;

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
    var event = obj.event;
    var url = $(this).data('url')

    //安装插件
    if (event === "install") {
      var index = layer.load(1);
      $.post(url, { name: data.name, version: data.version }, function (res) {
        if (res.code == 0) {
          notify.success(res.msg, "topRight");
        } else {
          notify.error(res.msg, "topRight");
        }
          layer.close(index);
      });
    }

    // 启用禁用
    if(event == 'status') {
      notify.confirm("确认框", "vcenter", function(){
        $.post(url,{ name: data.name },function(res){
          if (res.code == 0) {
            notify.success(res.msg, "topRight");
          } else {
            notify.error(res.msg, "topRight");
          }
          table.reloadData("addons-list",{},'deep');
          // addList("installed");
        });

      });


    }

    // 卸载插件
    if (event === "uninstall") {
      notify.confirm("确认框", "vcenter",function() {
        var index = layer.load(1);
        $.post(url, { name: data.name }, function (res) {
          if (res.code == 0) {
            notify.success(res.msg, "topRight");
          } else {
            notify.error(res.msg, "topRight");
          }
        });
        table.reload("addons-list");
        layer.close(index);
      });
    }

    // 配置插件
    if (event === "config") {
      layer.open({
        type: 2,
        title: '配置插件',
        content: url + "?name=" + data.name,
        maxmin: true,
        area: ["780px", "90%"],
        btn: ["确定", "取消"],
        yes: function (index, layero) {
          var iframeWindow = window["layui-layer-iframe" + index],
              submitID = "LAY-addons-config-submit",
              submit = layero.find("iframe").contents().find("#" + submitID);
          //监听提交
          iframeWindow.layui.form.on(
              "submit(" + submitID + ")",
              function (data) {
                var field = data.field; //获取提交的字段
                $.ajax({
                  type: "post",
                  url: url,
                  data: field,
                  daType: "json",
                  success: function (res) {
                    if (res.code == 0) {
                      notify.success(res.msg, "topRight");
                    } else {
                      notify.error(res.msg, "topRight");
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
            // if (even == "addInput") {
            //   var html = '<div class="layui-form-item">\n' +
            //       '<label class="layui-form-label"></label>\n' +
            //       '<div class="layui-input-inline">\n' +
            //       ' <input type="text" name="'+ names +'[key][]" value="" placeholder="key" autocomplete="off" class="layui-input input-double-width">\n' +
            //       '</div>\n' +
            //       '<div class="layui-input-inline">\n' +
            //       ' <input type="text" name="'+ names +'[value][]" value="" placeholder="value" autocomplete="off" class="layui-input input-double-width">\n' +
            //       '</div>\n' +
            //       '<button data-name="'+ names +'" type="button" class="layui-btn layui-btn-danger layui-btn-sm removeInupt" lay-event="removeInupt">\n' +
            //       ' <i class="layui-icon"></i>\n' +
            //       '</button>\n' +
            //       '</div>';
            //   $(this).parent().parent().append(html);
            // } else {
            //   $(this).parent().remove();
            // }
          });
        },
      });
    }

    if (event === "edit") {
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
  }

  });

  exports("addons", {});
});
