<?php /*a:1:{s:44:"E:\github\TaoLer\view\admin\index\index.html";i:1730713589;}*/ ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<title>TaoLer Admin管理系统</title>
		<!-- 依 赖 样 式 -->
		<link rel="stylesheet" href="/static/component/pear/css/pear.css" />
		<!-- 加 载 样 式 -->
		<link rel="stylesheet" href="/static/admin/css/loader.css" />
		<!-- 布 局 样 式 -->
		<link rel="stylesheet" href="/static/admin/css/admin.css" />
	</head>
	<!-- 结 构 代 码 -->
	<body class="layui-layout-body pear-admin">
		<!-- 布 局 框 架 -->
		<div class="layui-layout layui-layout-admin">
			<!-- 顶 部 样 式 -->
			<div class="layui-header">
				<!-- 菜 单 顶 部 -->
				<div class="layui-logo">
					<!-- 图 标 -->
					<img class="logo">
					<!-- 标 题 -->
					<span class="title"></span>
				</div>
				<!-- 顶 部 左 侧 功 能 -->
				<ul class="layui-nav layui-layout-left">
					<li class="collapse layui-nav-item"><a href="#" class="layui-icon layui-icon-shrink-right"></a></li>
					<li class="refresh layui-nav-item"><a href="#" class="layui-icon layui-icon-refresh-1" loading = 600></a></li>
				</ul>
				<!-- 多 系 统 菜 单 -->
				<div id="control" class="layui-layout-control"></div>
				<!-- 顶 部 右 侧 菜 单 -->
				<ul class="layui-nav layui-layout-right">
					<li class="layui-nav-item layui-hide-xs"><a href="#" class="menuSearch layui-icon layui-icon-search"></a></li>
					<li class="layui-nav-item layui-hide-xs"><a href="#" class="fullScreen layui-icon layui-icon-screen-full"></a></li>
					<li class="layui-nav-item layui-hide-xs"><a href="<?php echo htmlentities((string) $domain); ?>" class="layui-icon layui-icon-website" target="_blank"></a></li>
					<li class="layui-nav-item layui-hide-xs"><a href="javascript:void(0);" layadmin-event="clearcache" id="clearcache"><i class="layui-icon layui-icon-fonts-clear"></i></a></li>
					<li class="layui-nav-item layui-hide-xs message"></li>
					<li class="layui-nav-item user">
						<!-- 头 像 -->
						<a class="layui-icon layui-icon-username" href="javascript:;"></a>
						<!-- 功 能 菜 单 -->
						<dl class="layui-nav-child">
							<dd><a user-menu-url="<?php echo url('system.admin/info'); ?>" user-menu-id="5555" user-menu-title="基本资料">基本资料</a></dd>
							<dd><a href="javascript:void(0);" id="resetPassword">修改密码</a></dd>
							<dd><a href="javascript:void(0);" class="logout">注销登录</a></dd>
						</dl>
					</li>
					<!-- 主 题 配 置 -->
					<li class="layui-nav-item setting"><a href="#" class="layui-icon layui-icon-more-vertical"></a></li>
				</ul>
			</div>
			<!-- 侧 边 区 域 -->
			<div class="layui-side layui-bg-black">
				<!-- 菜 单 顶 部 -->
				<div class="layui-logo">
					<!-- 图 标 -->
					<img class="logo">
					<!-- 标 题 -->
					<span class="title"></span>
				</div>
				<!-- 菜 单 内 容 -->
				<div class="layui-side-scroll">
					<div id="sideMenu"></div>
				</div>
			</div>
			<!-- 视 图 页 面 -->
			<div class="layui-body">
				<!-- 内 容 页 面 -->
				<div id="content"></div>
			</div>
			<!-- 页脚 -->
			<div class="layui-footer layui-text">
				<span class="left">
					作者qq: 317927823 官方qq群：863422399
				</span>
				<span class="center"></span>
				<span class="right">
					Copyright © 2020-2024 aieok.com
				</span>
			</div>
			<!-- 遮 盖 层 -->
			<div class="pear-cover"></div>
			<!-- 加 载 动 画 -->
			<div class="loader-main">
				<!-- 动 画 对 象 -->
				<div class="loader"></div>
			</div>
		</div>
		<!-- 移 动 端 便 捷 操 作 -->
		<div class="pear-collapsed-pe collapse">
			<a href="#" class="layui-icon layui-icon-shrink-right"></a>
		</div>
		<!-- 依 赖 脚 本 -->
		<script src="/static/layui/layui.js"></script>
		<script src="/static/component/pear/pear.js"></script>
		<!-- 框 架 初 始 化 -->
		<script>
			layui.use(['admin','jquery','popup','drawer'], function() {
				var $ = layui.jquery;
				var admin = layui.admin;
				var popup = layui.popup;
				
				admin.setConfigType("yml");
				admin.setConfigPath("/static/config/pear.config.yml");
				
				admin.render();
				
				// 登出逻辑 
				admin.logout(function(){
					$.get("<?php echo url('system.admin/logout'); ?>",function(res){
						if(res.code === 0) {
							popup.success("注销成功",function(){
								location.href = "<?php echo url('system.login/index'); ?>";
							})
						}
					});
					
					// 注销逻辑 返回 true / false
					return true;
				})
				
				// 消息点击回调
				admin.message(function(id, title, context, form) {});

				$("#clearcache").click(function(){
					$.get("<?php echo url('system.admin/clearCache'); ?>",function(res){
						if(res.code === 0) {
							popup.success("清理成功");
						}
					})
				});

				// 修改密码
				$("#resetPassword").click(function(){
					layer.open({
					type: 2,
					title: '修改密码',
					shade: 0.1,
					area: ["350px", "300px"],
					content: "<?php echo url('system.admin/repass'); ?>",
					})
				});

			})
		</script>		
	</body>
</html>
