/**
 * 网站app版本发布
 * TaoLer社区
 * www.aieok.com
 */

layui.define(['toast','loading','storage'], function (exports) {
	let $ = layui.jquery;
	let table = layui.table;
	let toast = layui.toast;
	let layer = layui.layer;
	let loading = layui.loading;
	let form = layui.form;


	// 插件接口
	var api = {
		local: {
			get: function (name) {
				var data = localStorage.getItem(name);
				return data ? JSON.parse(data) : null;
			},
			set: function (name, data) {
				if (data) {
					localStorage.setItem(name, JSON.stringify(data));
				}
			},
			remove: function (name) {
				if(name) {
					localStorage.removeItem(name);
				}
			}
		}
	}

	// 登录
	var goLogin = function (data, event) {

		layer.confirm('你还未登录TaoLer社区账号, 请登录后操作!', {
			title : '温馨提示',
			btnAlign: 'c',
			btn: ['立即登录']
		}, function (confirmIndex) {
			layer.open({
				type: 1,
				shadeClose: true,
				title: '登录账号',
				content: $("#user-info").html(),
				area: ['400px', '380px'],
				btn: ['登录', '注册'],
				yes: function (index, layero, that) {
					
					var name = $("#username", layero).val();
					var password = $("#password", layero).val();

					if (!name || !password) {
						toast.error({title:"安装失败", message:'Account Or Password Cannot Empty', position: 'topRight'});
						return false;
					}

					$.ajax({
						url: USER_LOGIN_URL,
						type: 'POST',
						data: { name: name, password: password },
						dataType: "json",
						success: function (res) {
							if (res.code === 0) { // 登录成功
								layer.close(index); // 关闭弹层
								// 登录成功后，设置用户信息
								//api.userinfo.set(res.data);
								api.local.set("tao-admin-info", res.data);

								// 安装或者升级插件
								doEvent(data, event);

							} else {
								toast.warning({title:"警告消息", message:res.msg, position: 'topRight'});
								return false;
							}
						}, error: function (res) {
							toast.error({title:"登录失败", message: res.msg,position: 'topRight'});
							return false;
						}
					})
				},

				btn2: function (layero, index, that) {
					window.open("https://www.aieok.com/article/user_reg", "_blank");
					return false;
				}
			})
			layer.close(confirmIndex)
		});
	}

	// 支付html
	var payHtml = function (data) {

			let html = `
			<div class="layui-card">
				<div class="layui-card-header">授权</div>
				<div class="layui-card-body">
					<div class="layui-row">
						<div class="layui-col-sm6">
							<div class="order-info" style="margin:10px 0px; line-height: 30px;">
								<p>订单内容：<em>${data.subject}</em></p>
								<p>订单编号：<em>${data.out_order_no}</em></p>
								<p>订单价格：<em>￥${data.total_amount}</em> 元</p>
							</div>
							<div class="pay-type" style="margin-bottom: 20px;">
								<div style="padding: 5px; text-align: center;"><img src="/static/admin/images/alipay.jpg" style="height:80px;"></div>
							</div>
							<div class="soft-info" style="margin-bottom: 20px;">
								<p>注意：软件为虚拟商品，购买后不支持退款！</p>
								<br />
								<p>软件协议: 本软件版权为作者所有，购买软件可以商用，但禁止出售或分享给第三方使用及进行违法为目的的活动，否则一切后果将自负。</p>
							</div>
						</div>
						<div class="layui-col-sm6">
							<div data-text="支付宝当面付" style="padding: 5px; text-align: center;">
								<img src="${data.qr_code_img}">
							</div>
							<div style="line-height:20px;text-align: center;margin-bottom: 20px;">
								<p>请使用支付宝扫一扫<br>扫描二维码进行支付</p>
							</div>
						</div>
					</div>
				</div>
			</div>`;

		return html;
	}

	// 安装插件
	var goInstall = function (URL, data) {

		$.post(URL, {name: data.name, version: data.version, token: data.token}, function (res) {

			loading.loadRemove(1000);

			// 未支付
			if (res.code === -2) {
				// 去支付
				goPay(URL, data);
				return false;
			}

			// 安装成功
			if (res.code === 0) {
				toast.success({title:"安装成功", message: res.msg, position: 'topRight'});
				// 重载
				table.reloadData("plugin-list",{},'deep');
			}

			// 安装失败
			if (res.code === -1) {
				toast.error({title: "安装失败", message: res.msg, position: 'topRight'});
				return false;
			}

		});
	}
	// 支付
	var goPay = function (URL, data) {

		$.post(PAY_URL, {id: data.id, name: data.name, token: data.token}, function (ress){
			if(ress.code === 0) {

				let HTML = payHtml(ress.data);
				let intervalPay = null;
				let outOrderNo = ress.data.out_order_no;

				layer.open({
					type: 1,
					title: '支付信息',
					area: ['800px', '600px'],
					fixed: false, //不固定
					maxmin: true,
					content: HTML,
					success: function (layero, openIndex){
						// 订单轮询
						intervalPay = setInterval(function() {

							$.post(IS_PAY_URL, {out_order_no: outOrderNo, token: data.token}, function (result){
								if(result.code === 0) {
									layer.close(openIndex);
									clearInterval(intervalPay);
									// 安装插件
									goInstall(URL, data);

								} else if(result.code === 1) { // 待付款
									console.log(result.msg);
								} else if(result.code === -1 || result.code === 2) { // 交易关闭、交易结束等状态
									clearInterval(intervalPay);
									layer.close(openIndex);
									layer.alert(result.msg);
								}
							});
						}, 3000);

					},
					// 关闭弹层时清除定时询
					end: function () {
						clearInterval(intervalPay);
					}
				});
			}

			// 已支付过
			if(ress.code === 1) {
				// 安装插件
				goInstall(URL, data);
			}

		});
	}


	// 安装
	var doEvent = function (data, event) {

		// 检测权限
		//var userinfo = api.userinfo.get();
		var adminInfo = api.local.get("tao-admin-info");

		// 未登录
		if(!adminInfo) {
			goLogin(data, event);
			return false;
		}

		// 登陆后把token放data中
		if(!data.token) {
			data.token = adminInfo.token;
		}

		var URL = "";
		var CONTENT = "";
		
		// 安装
		if(event === "install") {
			URL = INSTALL_URL;
			CONTENT = "立即安装？";
		}
		// 升级
		if(event === "upgrade") {
			URL = UPGRADE_URL;
			CONTENT = "立即升级？";
		}

		// 已登录时
		layer.confirm(CONTENT, "vcenter", function(index){
			layer.close(index);
			loading.Load(1, '安装中...');
			// 安装插件
			goInstall(URL, data);
			// loading.loadRemove(1000);
		});
    }

	//监听工具条
	table.on("tool(plugin-list)", function (obj) {
		var data = obj.data;
		var event = obj.event;

		//安装插件
		if (event === "install" || event === "upgrade") {
			doEvent(data, event);
		}

		// 卸载插件
		if (event === "uninstall") {
			layer.confirm("是否卸载？", "vcenter",function(index) {
				$.post(UNINSTALL_URL, { name: data.name }, function (res) {
					if (res.code === 0) {
						table.reload("plugin-list");
						toast.success({title:"卸载成功",message:res.msg,position: 'topRight'});
					} else {
						toast.error({title:"卸载失败",message:res.msg,position: 'topRight'});
					}
				});

				layer.close(index);
			});
		}

		// 配置插件
		if (event === "config") {

			$.get(CONFIG_URL, {name: data.name}, function (res){
				// 无配置项拦截
				if (res.code === -1) {
					toast.warning({title:"警告消息", message: res.msg, position: 'topRight'})
					return false;
				}

				layer.open({
					type: 2,
					title: '插件配置',
					content: CONFIG_URL + "?name=" + data.name,
					maxmin: true,
					area: ["780px", "90%"],
					btn: ["确定", "取消"],
					yes: function (index, layero) {
						var iframeWindow = window["layui-layer-iframe" + index]; 
						var submitID = "plugin-config-submit";
						var submit = layero.find("iframe").contents().find("#" + submitID);

						//监听提交
						iframeWindow.layui.form.on("submit(" + submitID + ")", function (data) {
							var field = data.field; //获取提交的字段
							$.ajax({
								type: "POST",
								url: CONFIG_SET_URL,
								data: field,
								daType: "json",
								success: function (res) {
									if (res.code === 0) {
										toast.success({title:"成功消息", message:res.msg, position: 'topRight'});
									} else {
										toast.error({title:"失败消息", message:res.msg, position: 'topRight'});
									}
								}
							});
							return false;
						});

						layer.close(index); //关闭弹层
						submit.trigger("click");
					},

					// success: function (layero, index) {
					// 	var forms = layero.find("iframe").contents().find(".layui-form");
					// 	var button = forms.find("button");
					// 	//事件委托
					// 	forms.on("click", "button", function (data) {
					// 		var even = this.getAttribute("lay-event");
					// 		var names = this.dataset.name;
					// 	// if (even == "addInput") {
					// 	//   var html = '<div class="layui-form-item">\n' +
					// 	//       '<label class="layui-form-label"></label>\n' +
					// 	//       '<div class="layui-input-inline">\n' +
					// 	//       ' <input type="text" name="'+ names +'[key][]" value="" placeholder="key" autocomplete="off" class="layui-input input-double-width">\n' +
					// 	//       '</div>\n' +
					// 	//       '<div class="layui-input-inline">\n' +
					// 	//       ' <input type="text" name="'+ names +'[value][]" value="" placeholder="value" autocomplete="off" class="layui-input input-double-width">\n' +
					// 	//       '</div>\n' +
					// 	//       '<button data-name="'+ names +'" type="button" class="layui-btn layui-btn-danger layui-btn-sm removeInupt" lay-event="removeInupt">\n' +
					// 	//       ' <i class="layui-icon"></i>\n' +
					// 	//       '</button>\n' +
					// 	//       '</div>';
					// 	//   $(this).parent().parent().append(html);
					// 	// } else {
					// 	//   $(this).parent().remove();
					// 	// }
					// 	});
					// },

				})
				
			});
		}

	});

  exports("plugin", {});
});
