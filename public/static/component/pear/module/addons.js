//网站app版本发布

layui.define(['toast','common'], function (exports) {
	var $ = layui.jquery, table = layui.table;
	let toast = layui.toast;
	let layer = layui.layer;
	let common = layui.common;

  // 渲染表格
  // table.render({
  //   elem: "#addons-list",
  //   toolbar: "#toolbar",
  //   defaultToolbar: [],
  //   url: addonList,
  //   cols: [[
  //     {type: 'checkbox'},
  //     {title: '序号', type: 'numbers'},
  //     {field: 'title', title: '插件', width: 200},
  //     {field: 'description', title: '简介', minWidth: 200},
  //     {field: 'author', title: '作者', width: 100},
  //     {field: 'price', title: '价格(元)', width: 85},
  //     {field: 'downloads', title: '下载', width: 70},
  //     {field: 'version', title: '版本', templet: '<div>{{d.version}} {{#  if(d.have_newversion == 1){ }}<span class="layui-badge-dot"></span>{{#  } }}</div>', width: 75},
  //     {field: 'status', title: '在线', width: 70},
  //     {title: '操作', width: 165, align: 'center', toolbar: '#addons-bar'}
  //   ]],
  //   page: true,
  //   limit: 15,
  //   text: "对不起，加载出现异常！",
  // });
  //
  // // 重载数据
  // var addonReload = function (type,selector) {
  //   $.ajax({
  //     type: "post",
  //     url: addonList,
  //     data: { type: type, selector: selector },
  //     dataType: "json",
  //     success: function (res) {
  //       //渲染表格
  //       table.reload('addons-list',{
  //         url: addonList,
  //         where: {
  //           type: type, selector: selector
  //         },
  //         cols: [res["col"]],
  //       });
  //     },
  //   });
  // }
  //
  // //头工具栏事件
  // table.on("toolbar(addons-list)", function (obj) {
  //   var checkStatus = table.checkStatus(obj.config.id);
  //
  //   switch (obj.event) {
  //     case "installed":
  //       addonReload("installed","all");
  //       break;
  //     case "allAddons":
  //       addonReload("onlineAddons","all");
  //       break;
  //     case "freeAddons":
  //       addonReload("onlineAddons","free");
  //       break;
  //     case "payAddons":
  //       addonReload("onlineAddons","pay");
  //       break;
  //   }
  // });

  var api = {
      userinfo: {
          get: function () {
              var userinfo = localStorage.getItem("taoleradmin_userinfo");
              return userinfo ? JSON.parse(userinfo) : null;
          },
          set: function (data) {
              if (data) {
                  localStorage.setItem("taoleradmin_userinfo", JSON.stringify(data));
              } else {
                  localStorage.removeItem("taoleradmin_userinfo");
              }
          }
      }
  }

  //监听工具条
  table.on("tool(addons-list)", function (obj) {
	var data = obj.data;
	var event = obj.event;
	var url = $(this).data('url')

	// 安装
	var install = function (data, url, userLoginUrl, userIsPayUrl) {

		var userinfo = api.userinfo.get(); // 检测权限
		if(userinfo) {

			layer.confirm("确认安装吗？", "vcenter",function(index){
				layer.close(index);
				let loading = layer.load();

				$.post(url, { name: data.name, version: data.version, uid: userinfo.uid, token: userinfo.token }, function (res) {
					layer.close(loading);

					// 需要支付
					if (res.code === -2) {
						layer.open({
							type: 2,
							area: [common.isModile()?'100%':'800px', common.isModile()?'100%':'600px'],
							fixed: false, //不固定
							maxmin: true,
							content: 'pay.html'+ "?id=" + data.id+ "&name=" + data.name + "&version=" + data.version + "&uid=" + userinfo.uid + "&price=" + data.price,
							success: function (layero, index){
								// 订单轮询
								var intervalPay = setInterval(function() {
									$.post(userIsPayUrl,{name:data.name, userinfo:userinfo}, function (result){
										if(result.code === 0) {
											layer.close(index);
											clearInterval(intervalPay);
											install(data, url, userLoginUrl, userIsPayUrl);
										}
									});
								}, 3000);
							}
						});
					}

					// 安装成功
					if (res.code === 0) {
						toast.success({title:"安装成功",message:res.msg,position: 'topRight'});
					}

					// 安装失败
					if (res.code === -1) {
						toast.error({title:"安装失败",message:res.msg,position: 'topRight'});
					}

					// 重载
					table.reloadData("addons-list",{},'deep');

				});
			});
		} else {
			// 未登录时
			layer.confirm('你当前还未登录TaoLer社区账号,请登录后操作!', {
				title : '温馨提示',
				btnAlign: 'c',
				btn: ['立即登录'] //按钮
			},function (index) {
				layer.close(index);
				// 登录窗口
				layer.open({
					type: 1,
					shadeClose: true,
					title: '登录账号',
					content: $("#user-info").html(),
					area: ['400px','300px'],
					btn: ['登录','注册'],
					yes:function (index, layero) {
						var url = userLoginUrl;
						var data = {
							name: $("#username", layero).val(),
							password: $("#password", layero).val(),
						};

						if (!data.name || !data.password) {
							toast.error({title:"安装失败",message:'Account Or Password Cannot Empty',position: 'topRight'});
							return false;
						}

						$.ajax({
							url: url,
							type: 'post',
							data: data,
							dataType: "json",
							success: function (res) {
								if (res.code === 0) {
									layer.close(index);
									api.userinfo.set(res.data);
									toast.success({title:"登录成功",message:res.msg,position: 'topRight'}, function () {
										location.reload();
									});
								} else {
									toast.warning({title:"警告消息",message:res.msg});
								}
							}, error: function (res) {
								toast.error({title:"登录失败",message:res.msg,position: 'topRight'});
							}
						})
					},
					btn2: function () {
						return false;
					},
					success: function (layero, index) {
						$(".layui-layer-btn1", layero).prop("href", "https://www.aieok.com/article/reg.html").prop("target", "_blank");
					}
				});
			});
		}
    }

      //安装插件
	if (event === "install" || event === "upgrade") {
		var userLoginUrl = $(this).data('userlogin');
		var userIsPayUrl = $(this).data('ispay');
    	install(data,url,userLoginUrl,userIsPayUrl);
    }

      // 卸载插件
      if (event === "uninstall") {
      layer.confirm("是否卸载？", "vcenter",function(index) {
        $.post(url, { name: data.name }, function (res) {
          if (res.code === 0) {
            toast.success({title:"卸载成功",message:res.msg,position: 'topRight'});
          } else {
            toast.error({title:"卸载失败",message:res.msg,position: 'topRight'});
          }
        });
        layer.close(index);
        table.reload("addons-list");

      });
    }

    // 配置插件
    if (event === "config") {
    	$.post(url,{name:data.name},function (res){
			// 无配置项拦截
			if (res.code === -1) {
				toast.warning({title:"警告消息",message:res.msg,position: 'topRight'})
				return false;
			}
			layer.open({
				type: 2,
				title: '配置插件',
				content: url + "?name=" + data.name,
				maxmin: true,
				area: ["780px", "90%"],
				btn: ["确定", "取消"],
				yes: function (index, layero) {
					var iframeWindow = window["layui-layer-iframe" + index], submitID = "LAY-addons-config-submit", submit = layero.find("iframe").contents().find("#" + submitID);
					//监听提交
					iframeWindow.layui.form.on("submit(" + submitID + ")",
						function (data) {
						var field = data.field; //获取提交的字段
						$.ajax({
							type: "post",
							url: url,
							data: field,
							daType: "json",
							success: function (res) {
							if (res.code === 0) {
								toast.success({title:"成功消息",message:res.msg,position: 'topRight'});
							} else {
								toast.error({title:"失败消息",message:res.msg,position: 'topRight'});
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
    	});
    }

  });

  exports("addons", {});
});
