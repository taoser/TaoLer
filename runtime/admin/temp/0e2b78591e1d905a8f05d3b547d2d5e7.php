<?php /*a:2:{s:55:"E:\github\TaoLer\app\admin\view\addon\addons\index.html";i:1730964158;s:54:"E:\github\TaoLer\app\admin\view\public\user_login.html";i:1730964158;}*/ ?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>插件管理</title><link rel="stylesheet" href="/static/component/pear/css/pear.css" /></head><body class="pear-container"><div class="layui-card"><div class="layui-card-body"><table id="addons-list" lay-filter="addons-list"></table></div></div><script type="text/html" id="toolbar"><div class="layui-btn-group"><button class="layui-btn layui-btn-sm" lay-event="allAddons">全部</button><button class="layui-btn layui-btn-sm" lay-event="freeAddons">免费</button><button class="layui-btn layui-btn-sm" lay-event="payAddons">付费</button><button class="layui-btn layui-btn-normal layui-btn-sm" lay-event="installed">已安装</button><button class="layui-btn layui-btn-danger layui-btn-sm" lay-event="add">离线安装</button></div></script><script type="text/html" id="addons-bar">			{{#  if(d.have_newversion === 1){ }}
				<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="install" data-url="<?php echo url('addon.addons/upgrade'); ?>" data-userlogin="<?php echo url('addon.addons/userLogin'); ?>" data-ispay="<?php echo url('addon.addons/isPay'); ?>"><i class="layui-icon layui-icon-upload-circle"></i>升级</a>			{{# } else { }}
				{{# if(d.isInstall === 1) { }}
					<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="config" data-url="<?php echo url('addon.addons/config'); ?>"><i class="layui-icon layui-icon-set"></i>设置</a>				{{# } else { }}
					<a class="layui-btn layui-btn-xs" lay-event="install" data-url="<?php echo url('addon.addons/install'); ?>" data-userlogin="<?php echo url('addon.addons/userLogin'); ?>" data-ispay="<?php echo url('addon.addons/isPay'); ?>"><i class="layui-icon layui-icon-edit"></i>安装</a><select id="vers{{d.name}}" name="sss" class="layui-border" lay-ignore lay-filter="versSelect">						{{# d.vers.forEach(function(item, index){ }}
						<option value="{{ item }}">{{ item }}</option>						{{# }); }}
					</select>				{{# } }}
			{{# } }}
		</script><script type="text/html" id="ver-tpl"><div>{{d.version}} {{#  if(d.have_newversion == 1) { }} <span class="layui-badge-dot"></span> {{#  } }}</div></script><script type="text/html" id="buttonStatus"><input type="checkbox" name="{{d.name}}" lay-skin="switch" lay-filter="addonsStatus" lay-text="启动|禁用" {{#  if(d.status == 1){ }} checked {{#  } }} data-url="<?php echo url('addon.addons/check'); ?>"></script><script type="text/html" id="addons-installed-tool"><a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="config" data-url="<?php echo url('addon.addons/config'); ?>"><i class="layui-icon layui-icon-set"></i>设置</a><a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="uninstall" data-url="<?php echo url('addon.addons/uninstall'); ?>"><i class="layui-icon layui-icon-delete"></i>卸载</a></script><script type="text/html" id="user-info"><div><div class="layui-card"><div class="layui-card-header"><blockquote class="layui-elem-quote">温馨提示 此处账号为: <a class="font" target="_blank" href="https://www.aieok.com">TaoLer社区账号</a></blockquote></div><br><div class="layui-card-body" style="padding: 0 40px 10px 0"><form class="layui-form" action=""><div class="layui-form-item"><label class="layui-form-label required">账号 *</label><div class="layui-input-block"><input type="text" class="layui-input"  lay-verify="required" id="username" value="" placeholder="<?php echo lang('username or email'); ?>"></div></div><div class="layui-form-item"><label class="layui-form-label required">密码 *</label><div class="layui-input-block"><input type="password" class="layui-input"  lay-verify="required" id="password" value="" placeholder="<?php echo lang('password'); ?>"></div></div></form></div></div></div></script><script src="/static/component/layui/layui.js"></script><script src="/static/component/pear/pear.js"></script><script>			var addonList = "<?php echo url('addon.addons/getList'); ?>";


			layui.use(['addons','table','form','upload','toast'], function(){
			  var $ = layui.jquery
			  ,table = layui.table
			  ,form = layui.form
			  ,upload = layui.upload;
			  var toast = layui.toast;

			  let LIST_URL = "<?php echo url('addon.addons/list'); ?>";
				
				let cols = [[
					{type: 'checkbox'},
					{title: '序号', type: 'numbers'},
					{field: 'title', title: '插件', width: 200},
					{field: 'description', title: '简介', minWidth: 200},
					{field: 'author', title: '作者', width: 100},
					{field: 'price', title: '价格(元)', width: 85},
					{field: 'downloads', title: '下载', width: 70},
					{field: 'version', title: '版本', templet: '#ver-tpl', width: 75},
					{field: 'status', title: '在线', width: 70},
					{title: '操作', width: 165, align: 'center', toolbar: '#addons-bar'}
				]];
				
				let col  = [[
				{type: 'numbers'},
				{field: 'name', title: '插件', width:  120},
				{field: 'title',title: '标题', width:  100},
				{field: 'version', title: '版本'},
				{field: 'author', title: '作者', width:  80},
				{field: 'description', title: '简介', minWidth:  200},
				{field: 'install', title: '安装', width: 100},
				{field: 'ctime', title: '到期时间', width: 100},
				{field: 'status', title: '状态', width:  95, templet: '#buttonStatus'},
				{title: '操作', width: 160, 'align': 'center', toolbar:  '#addons-installed-tool'}
			]];

				//渲染表格
				var addonTable = table.render({
					elem: "#addons-list",
					toolbar: "#toolbar",
					defaultToolbar: [],
					url: LIST_URL,
					cols: cols,
					page: true,
					limit: 15,
					text: "对不起，加载出现异常！",
				});

				// table.on('tool(addons-list)',function(obj){
				// 	console.log(obj)
				// 	if (obj.event === 'remove') {
				// 		window.remove(obj);
				// 	} else if (obj.event === 'edit') {
				// 		window.edit(obj);
				// 	}
				// })


				table.on('toolbar(addons-list)', function(obj){
					if(obj.event === 'installed'){
						window.installed(obj.event);
					} else if(obj.event === 'add'){
						//window.add();
					} else {
						// all,free,pay
						window.all(obj.event);
					}
				});

				window.all = function (type) {
					$.post(LIST_URL,{type: type}, function (res){
						if(res.code === 0) {
							// 重新渲染
							table.reload('addons-list',{
								url: LIST_URL,
								where: {
									type : type
								},
								cols: cols,
								page: {
									curr: 1 //重新从第 1 页开始
								}
							});

						}
					})
				}

				window.installed = function (type) {
					$.get(LIST_URL,{type: type}, function (res){
						if(res.code === 0) {
							// 重新渲染
							table.reload('addons-list',{
								url: LIST_URL,
								where: {
									type : type
								},
								cols: col,
								page: {
									curr: 1 //重新从第 1 页开始
								}
							});
						}
					})
				}

				window.add = function(){
					layer.open({
						type: 2
						,title: '添加插件'
						,content: 'add.html'
						,area: ['400px', '300px']
					});
				}



			  form.render('select'); // 渲染所在容器内的 select 元素
			  //监听版本选择
			  form.on('select(versSelect)', function(obj){
				layer.tips(this.value + ' ' + this.name + '：'+ obj.elem.checked, obj.othis);
			  });


			  // 启动禁用
			  form.on('switch(addonsStatus)', function(data){
				  var data = data.elem;
				  var url = $(this).data('url');
				  //执行帖子审核
				  $.post(url,{ name: data.name },function(res){
						  if(res.code === 0){
							  toast.success({title:"成功消息",message:res.msg,position: 'topRight'});
						  } else {
							  toast.error({title:"失败消息",message:res.msg,position: 'topRight'});
						  }
						  table.reloadData("addons-list",{},'deep');
					  });
				  return false;
			  });


		  });
		</script></body></html>