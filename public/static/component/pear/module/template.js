/**
 * 网站app版本发布
 * TaoLer社区
 * www.aieok.com
 */

layui.define(['toast','loading','storage'], function (exports) {
	let $ = layui.jquery;
	let layer = layui.layer;

	let form = layui.form;
	let util = layui.util;
	let toast = layui.toast;
	let loading = layui.loading;
	var laypage = layui.laypage;

	// 按钮类型缓存
	layui.cache.tpl = {
		type: 'all'
	};


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

	// 模版列表插件
	var tpl = {
		render: function (obj) {
			const self = this;
			// 缓存完整原始配置
			self.cacheOpt = { ...obj };

			self.elem = obj.elem;
			self.url = obj.url;
			self.data = obj.data || {};
			self.page = obj.page || false;
			self.limit = obj.limit || 10;
			self.height = obj.height || 100;
			// 新增：保存自定义数据解析回调
			self.parseData = obj.parseData || null;
			// 存储当前页码
			self.currentPage = 1;

			self.renderList = function (datas, count) {
				const container = $(self.elem);
				container.empty();

				if (count == undefined) {
					count = datas.length;
				}

				let htmlStr = '';
				datas.map(function (vo) {
					let html = `
						<div class="layui-col-md3 layui-col-xs6 ew-datagrid-item">
							<div class="project-list-item">
								<img class="project-list-item-cover" src="${vo.image || ''}">
								<div class="project-list-item-body">
									<h2 class="layui-elip">${vo.name || ''}</h2>
									<div class="project-list-item-text">${vo.description || ''}</div>
									<div class="project-list-item-desc">
										<span class="time">版本:${vo.version || ''}</span>
										<div class="ew-head-list">
										<span class="time">${vo.time || ''}</span>
									</div>
								</div>
								<div class="project-list-item-desc" style="margin-top: 15px; height: ${self.height}px;">
									<div class="layui-btn-group">`;

								if (vo.enable) {
									html += `<button type="button" class="layui-btn layui-btn-sm layui-btn-primary">使用中</button>`;
								} else {
									if (!vo.installed) {
										html += `<button type="button" lay-on="install" data-name="${vo.name}" data-version="${vo.version}" class="layui-btn layui-bg-blue layui-btn-sm">安装</button>`;
									} else {
										html += `<button type="button" lay-on="enable" data-name="${vo.name}" class="layui-btn layui-btn-sm">启用</button>`;
										html += `<button type="button" lay-on="delete" data-name="${vo.name}" class="layui-btn layui-btn-sm layui-btn-danger">删除</button>`;
									}
								}
							html += `</div>`;

								if (vo.update) {
									html += `
									<div class="ew-head-list">
										<button type="button" lay-on="upgrade" data-name="${vo.name}" data-version="${vo.version}" class="layui-btn layui-btn-sm layui-bg-orange">更新</button>
									</div>`;
								}

						html += `</div>
							</div>
						</div>
					</div>`;
					htmlStr += html;
				});
					
				let ALLHTML = `
				<div class="layui-card">
					<div class="layui-card-body">
						<div class="layui-row layui-col-space30" >`
							+ htmlStr +
						`</div>
						<div class="layui-row">
							<div id="laypageId"></div>
						</div>
					</div>
				</div>`;

				container.append(ALLHTML);
				
				if (self.page) {
					layui.laypage.render({
						elem: 'laypageId',
						count: count,
						curr: self.currentPage,
						limit: self.limit,
						hash: 'page',
						layout: ['prev', 'page', 'next', 'count'],
						jump: function (obj, first) {
							self.currentPage = obj.curr;
							if (!first) {
								self.reload({
									where: {
										page: obj.curr
									}
								});
							}
						}
					})
				}
			};

			self.fetchData(obj);
		},

		reload: function (p1, p2) {
			const self = this;
			if (!self.cacheOpt) {
				console.warn('请先执行tpl.render初始化列表');
				return;
			}

			let targetElem = null;
			let newQuery = null;

			if (typeof p1 === 'string') {
				targetElem = '#' + p1;
				newQuery = p2 && typeof p2 === 'object' ? p2 : null;
			} else if (p1 && typeof p1 === 'object') {
				newQuery = p1;
				targetElem = null;
			}

			if (targetElem) {
				self.elem = targetElem;
				self.cacheOpt.elem = targetElem;
			}

			if (newQuery) {
				if (newQuery.where) {
					self.cacheOpt.data = { ...self.cacheOpt.data, ...newQuery.where };
					self.data = self.cacheOpt.data;
					delete newQuery.where;
				}
				Object.keys(newQuery).forEach(key => {
					self[key] = newQuery[key];
					self.cacheOpt[key] = newQuery[key];
				});
			}

			self.fetchData(self.cacheOpt);
		},

		fetchData: function (obj) {
			const self = this;
			if (self.page) {
				self.data.page = self.data.page || 1;
				self.currentPage = self.data.page;
				self.data.limit = self.limit;
			}

			$.get(self.url, self.data, function (res) {
				// 核心新增：使用 parseData 转换原始返回
				let result;
				if (typeof self.parseData === 'function') {
					result = self.parseData(res);
				} else {
					// 无自定义解析，默认原始格式
					result = res;
				}

				// 统一使用标准结构 result.code / result.data / result.count
				if (result.code === 0 && result.data && result.data.length > 0) {
					self.renderList(result.data, result.count);
				} else {
					$(self.elem).html('<div style="padding:20px;text-align:center;color:#999;">暂无数据</div>')
				}
			});
		}
	};

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
							$.post(IS_PAY_URL, {name: data.name, token: data.token}, function (result){
								if(result.code === 0) {
									layer.close(openIndex);
									clearInterval(intervalPay);
									// 安装插件
									goInstall(URL, data);

								} else if(result.code === 1) { // 待付款、交易关闭、交易结束等状态
									console.log(result.msg);
								} else if(result.code === -1 || result.code === 2) {
									clearInterval(intervalPay);
									layer.close(openIndex);
									layer.alert(result.msg);
									return false;
								}
							});
						}, 3000);

					},
					end: function () {
						clearInterval(intervalPay);
					}
				});
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

	// 渲染
	tpl.render({
		elem: '#tplId',
		url: LIST_URL,
		page: true,
		height: 30,
		data: {}
	});

	// 事件
	util.on({
		install: function() {
			var name = $(this).attr('data-name');
			var version = $(this).attr('data-version');

			var data = {
				name: name,
				version: version
			}

			doEvent(data, 'install');

			return false;
		},

		upgrade: function(){
			var name = $(this).attr('data-name');
			var version = $(this).attr('data-version');

			var data = {
				name: name,
				version: version
			}
			loading.Load(3,message);
			doEvent(data, 'upgrade');

			return false;
		},

		enable: function() {
			var name = $(this).attr('data-name');

			loading.Load(5, "启用模板");

			$.post(ENABLE_URL, {name}, function(res) {
				loading.loadRemove(1000);
				if(res.code === 0) {
					toast.success({title:"成功消息", message:res.msg})
					tpl.reload("tplId");
					// window.location.reload();
				} else {
					toast.error({title:"危险消息", message:res.msg})
				}
			})
			
			return false;
		},

		delete: function() {
			var name = $(this).attr('data-name');

			layer.confirm("是否删除？", "vcenter",function(index) {
				$.post(UNINSTALL_URL, { name: data.name }, function (res) {
					if (res.code === 0) {
						tpl.reload("tplId");
						toast.success({title:"删除成功",message:res.msg,position: 'topRight'});
					} else {
						toast.error({title:"删除失败",message:res.msg,position: 'topRight'});
					}
				});
				layer.close(index);
			});

			return false;
		},

		all: function(){
			tpl.reload({
				where:{
					page: 1,
					type: 'all'
				}
			})
			layui.cache.tpl.type = 'all';

			$('button').removeClass('layui-bg-blue');
			$(this).addClass('layui-bg-blue');
		},
		free: function(){
			tpl.reload({
				where:{
					type: 'free'
				}
			})
			layui.cache.tpl.type = 'free';
			$('button').removeClass('layui-bg-blue');
			$(this).addClass('layui-bg-blue');
		},
		installed: function(){
			tpl.reload({
				where:{
					type: 'installed'
				}
			})
			layui.cache.tpl.type = 'installed';
			$('button').removeClass('layui-bg-blue');
			$(this).addClass('layui-bg-blue');
		},
		import: function(){
			tpl.reload({
				where:{
					type: 'import'
				}
			})
			layui.cache.tpl.type = 'import';
			$('button').removeClass('layui-bg-blue');
			$(this).addClass('layui-bg-blue');
		},
	});
	
	// 监听搜索操作
	form.on('submit(search-btn)', function(data) {
		field = data.field;
		field.type = layui.cache.tpl.type;

		tpl.reload("tplId", {
			where: field,
		});
		return false;
	});

  exports("template", {});
});
