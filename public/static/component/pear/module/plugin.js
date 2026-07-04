/**
 * 网站app版本发布
 * TaoLer社区
 * www.aieok.com
 */

layui.define(['toast','loading'], function (exports) {
	let $ = layui.jquery;
	let table = layui.table;
	let toast = layui.toast;
	let layer = layui.layer;
	let loading = layui.loading;
	let form = layui.form;

	var api = {
		userinfo: {
			get: function () {
				var userinfo = localStorage.getItem("taoleradmin_userinfo");
				return userinfo ? JSON.parse(userinfo) : null;
			},
			set: function (data) {
				if (data) {
					localStorage.setItem("taoleradmin_userinfo", JSON.stringify(data));
				}
			},
			remove: function () {
				localStorage.removeItem("taoleradmin_userinfo");
			}
		}
	}

	// 登录
	var goLogin = function (data) {
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
								api.userinfo.set(res.data);
								// 安装插件
								install(data);
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

	var payHtml = function (data) {
			let html = `
				<div class="layui-card">
					<div class="layui-card-header">授权</div>
					<div class="layui-card-body">
						<div class="layui-row">
							<div class="order-info" style="margin:10px 0px; line-height: 30px;">
								<p>订单标题：<em>${data.subject}</em></p>
								<p>订单编号：<em>${data.out_order_no}</em></p>
								<p>订单价格：<em class="scanpay-price">￥${data.total_amount}</em> 元</p>
							</div>
						</div>
						<div class="layui-row">
							<div class="layui-col-sm6">
								<div class="pay-type" style="margin-bottom: 20px;">
									<div style="padding: 5px; text-align: center;"><img src="/static/admin/images/alipay.jpg" style="height:80px;"></div>
								</div>
								<div class="soft-info" style="margin-bottom: 20px;">
									<div>不支持退款</div>
									<br />
									<div>软件协议:本软件为原作者拥有版权权限，购买软件可以商用，禁止出售第三方行为。</div>
								</div>
							</div>
							<div class="layui-col-sm6">
								<div class="qrcode" data-text="支付宝当面付" style="padding: 5px; text-align: center;">
									<img src="${data.qr_code_img}">
								</div>
								<div class="pay-tips" style="line-height:20px;text-align: center;margin-bottom: 20px;">
									<p>请使用支付宝扫一扫<br>扫描二维码进行支付</p>
								</div>
							</div>
						</div>
					</div>

				</div>
			`;

			return html;
		}


	// 安装
	var install = function (data) {

		// 检测权限
		var userinfo = api.userinfo.get();
		if(!userinfo) {
			goLogin(data);
			return false;
		}

		// 已登录时
		layer.confirm("立即安装？", "vcenter", function(index){
			layer.close(index);
			loading.Load(1, '安装中...');

			$.post(INSTALL_URL, {name: data.name, version: data.version, token: userinfo.token}, function (res) {
				loading.loadRemove(1000);

				// 需要支付
				if (res.code === -2) {
					// 支付
					$.post(PAY_URL, {id: data.id, name: data.name, token: userinfo.token}, function (result){
						if(result.code === 0) {
							console.log(result.data);

							let HTML = payHtml(result.data);

							layer.open({
								type: 1,
								area: ['800px', '600px'],
								fixed: false, //不固定
								maxmin: true,
								content: HTML,
								success: function (layero, index){

									// 订单轮询
									var intervalPay = setInterval(function() {
										$.post(IS_PAY_URL, {name: data.name, token: userinfo.token}, function (result){
											if(result.code === 0) {
												layer.close(index);
												clearInterval(intervalPay);
												// 安装插件
												install(data);
											}
										});
									}, 3000);

								}
							});
						}
					});
				}

				// 安装成功
				if (res.code === 0) {
					toast.success({title:"安装成功",message:res.msg,position: 'topRight'});
					// 重载
					table.reloadData("plugin-list",{},'deep');
				}

				// 安装失败
				if (res.code === -1) {
					toast.error({title:"安装失败",message:res.msg,position: 'topRight'});
				}

			});

		});
    }

	//监听工具条
	table.on("tool(plugin-list)", function (obj) {
		var data = obj.data;
		var event = obj.event;

		console.log(data,event);

		//安装插件
		if (event === "install" || event === "upgrade") {
			install(data);
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
