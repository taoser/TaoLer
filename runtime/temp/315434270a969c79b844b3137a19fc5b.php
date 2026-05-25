<?php /*a:6:{s:53:"E:\github\TaoLer\app\index\view\taoler\login\reg.html";i:1737273662;s:55:"E:\github\TaoLer\app\index\view\taoler\public\base.html";i:1760276314;s:57:"E:\github\TaoLer\app\index\view\taoler\public\header.html";i:1760276314;s:55:"E:\github\TaoLer\app\index\view\taoler\public\menu.html";i:1744610627;s:57:"E:\github\TaoLer\app\index\view\taoler\public\footer.html";i:1737273662;s:53:"E:\github\TaoLer\app\index\view\taoler\public\js.html";i:1737273662;}*/ ?>
<!--
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2021-12-06 16:04:51
 * @LastEditTime: 2022-08-10 16:50:38
 * @LastEditors: TaoLer
 * @Description: 搜索引擎SEO优化设置
 * @FilePath: \github\TaoLer\view\taoler\index\public\base.html
 * Copyright (c) 2020~2025 https://www.aieok.com All rights reserved.
-->
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="applicable-device" content="pc,mobile" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="renderer" content="webkit" />
	<meta name="force-rendering" content="webkit" />
	<!-- seo -->
	<title>注册账号</title>
	<meta name="keywords" content="<?php echo htmlentities((string) $sysInfo['keywords']); ?>" />
	<meta name="description" content="<?php echo htmlentities((string) $sysInfo['descript']); ?>" />
	<meta name="copyright" content="<?php echo htmlentities((string) $sysInfo['copyright']); ?>" />
	
	 
	<meta property="og:url" content="<?php echo htmlentities((string) app('request')->url()); ?>" />
	<meta property="og:site_name" content="<?php echo htmlentities((string) $sysInfo['webname']); ?>" />
	 
	

	<script src="/static/layui/layui.js" charset="utf-8"></script>
	<script src="/static/tpl/taoler/mods/toast.js"></script>
	<script src="/static/common/notify.js"></script>
	<!-- 样式 -->
	<link rel="canonical" href="<?php echo htmlentities((string) app('request')->url()); ?>">
	<link rel="stylesheet" href="/static/tpl/taoler/css/font_24081_qs69ykjbea.css" />
	<link rel="stylesheet" href="/static/layui/css/layui.css">
	<link rel="stylesheet" href="/static/tpl/taoler/css/global.css">
	<link rel="stylesheet" href="/static/component/pear/css/module/toast.css">
	
	<script src="/static/share/plusShare.js" type="text/javascript" charset="utf-8"></script>
	<?php echo $sysInfo['showlist']; ?>
</head>
<body>
<script>
	const scriptUrl = '/static/layui/layui.js';
	const existingScripts = document.querySelectorAll(`script[src="${scriptUrl}"]`);
	if (existingScripts.length === 0) {
		const script = document.createElement('script');
		script.src = scriptUrl;
		document.body.appendChild(script);
	}
</script>

<div class="fly-header layui-bg-black">
	<div class="layui-container">
		<a class="fly-logo layui-hide-xs" href="<?php echo htmlentities((string) app('request')->domain()); ?>"><img src="<?php echo htmlentities((string) $sysInfo['logo']); ?>"  alt="logo"></a>
		<!--头部伸缩侧边栏-->
		<div class="site-tree-mobile-top layui-hide"><i class="layui-icon layui-icon-spread-left"></i></div>
		<div class="site-mobile-shade-top"></div>
		
		<a class="layui-hide-md layui-hide-sm" href="<?php echo htmlentities((string) app('request')->domain()); ?>" ><img class="fly-logo-m" src="<?php echo htmlentities((string) $sysInfo['m_logo']); ?>" alt="logo"></a>
		
		<ul class="layui-nav fly-nav layui-hide-xs">
		<?php if(config('taoler.config.nav_top') != '0'): $__CATE__ = \app\facade\Category::getNav(); if(!(empty($__CATE__) || (($__CATE__ instanceof \think\Collection || $__CATE__ instanceof \think\Paginator ) && $__CATE__->isEmpty()))): if(is_array($__CATE__) || $__CATE__ instanceof \think\Collection || $__CATE__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__CATE__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nav): $mod = ($i % 2 );++$i;?>
			<li class="layui-nav-item <?php if($nav['ename'] == app('request')->param('ename')): ?> layui-this <?php endif; ?>" >
				<a href="<?php echo htmlentities((string) $nav['url']); ?>"><?php echo htmlentities((string) $nav['catename']); if($nav['is_hot'] == '1'): ?> <span class="layui-badge-dot"></span> <?php endif; ?></a>
				<?php if(!(empty($nav['children']) || (($nav['children'] instanceof \think\Collection || $nav['children'] instanceof \think\Paginator ) && $nav['children']->isEmpty()))): ?>
				<dl class="layui-nav-child"> <!-- 二级菜单 -->
					<?php if(!(empty($nav['children']) || (($nav['children'] instanceof \think\Collection || $nav['children'] instanceof \think\Paginator ) && $nav['children']->isEmpty()))): if(is_array($nav['children']) || $nav['children'] instanceof \think\Collection || $nav['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $nav['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$snav): $mod = ($i % 2 );++$i;?>
					<dd><a href="<?php echo htmlentities((string) $snav['url']); ?>"><?php echo htmlentities((string) $snav['catename']); ?></a></dd>
					<?php endforeach; endif; else: echo "" ;endif; ?><?php endif; ?>
				</dl>
				<?php endif; ?>
			</li>
		<?php endforeach; endif; else: echo "" ;endif; ?><?php endif; ?>
		<?php endif; ?>
		
		<?php echo hook('ads_header_link'); ?>
		</ul>

		
		<ul class="layui-nav fly-nav-user" msg-url="<?php echo url('message/nums'); ?>" readMsg-url="<?php echo url('Message/read'); ?>" lay-filter="header-filter-nav">
			<li class="layui-nav-item">
				<span class="fly-search layui-hide-xs" data-url="<?php echo url('user_search'); ?>"><i class="layui-icon layui-icon-search"></i></span>
			</li>
			<!-- 登录 -->
		</ul>
	</div>
</div>


<div class="layui-container fly-marginTop">
	<div class="fly-panel fly-panel-user" pad20>
	<div class="layui-tab layui-tab-brief" lay-filter="user">
		<ul class="layui-tab-title">
		<li><a href="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo url('user_login'); ?>"><?php echo lang('login'); ?></a></li>
		<li class="layui-this"><?php echo lang('sign up'); ?></li>
		</ul>
		<div class="layui-form layui-tab-content" id="LAY_ucm" style="padding: 20px 0;" lay-filter="register">
		<div class="layui-tab-item layui-show">
			<div class="layui-form layui-form-pane">
			<form>
			<?php if(config('taoler.config.is_regist') == 1): ?>
				<div class="layui-form-item">
					<label for="L_username" class="layui-form-label"><?php echo lang('username'); ?></label>
					<div class="layui-input-inline">
					<input type="text" id="L_username" name="name" required lay-verify="required" autocomplete="off" class="layui-input" placeholder="建议用手机号注册">
					</div>
				</div>
				<div class="layui-form-item">
					<label for="L_email" class="layui-form-label"><?php echo lang('email'); ?></label>
					<div class="layui-input-inline">
					<input type="text" id="L_email" name="email" required lay-verify="email" autocomplete="off" class="layui-input" placeholder="<?php echo lang('the only way to get back your password'); ?>">
					</div>
					<?php if(config('taoler.config.regist_type') == 2): ?>
					<div class="layui-form-mid layui-word-aux" style="padding-top: 0px !important;"> 
					<button type="button" class="layui-btn layui-btn-normal" id="LAY-component-form-getval">发送邮箱验证码</button>
				</div>
				<?php endif; ?>
				</div>
				<?php if(config('taoler.config.regist_type') == 2): ?>
				<div class="layui-form-item">
					<label for="L_email" class="layui-form-label"><?php echo lang('captcha'); ?></label>
					<div class="layui-input-inline">
					<input type="text" name="email_code" required lay-verify="required" autocomplete="off" class="layui-input" placeholder="<?php echo lang('请去邮箱查验验证码'); ?>">
					</div>
				</div>
				<?php endif; ?>
				<div class="layui-form-item">
					<label for="L_pass" class="layui-form-label"><?php echo lang('password'); ?></label>
					<div class="layui-input-inline">
					<input type="password" id="L_pass" name="password" required lay-verify="required" autocomplete="off" class="layui-input" placeholder="<?php echo lang('6-16 characters'); ?>">
					</div>
				</div>
				<div class="layui-form-item">
					<label for="L_repass" class="layui-form-label"><?php echo lang('confirm password'); ?></label>
					<div class="layui-input-inline">
					<input type="password" id="L_repass" name="repassword" required lay-verify="required" autocomplete="off" class="layui-input" placeholder="<?php echo lang('please confirm the password'); ?>">
					</div>
				</div>
				<?php if(config('taoler.config.regist_type') == 1): ?>
				<div class="layui-form-item">
					<label for="L_vercode" class="layui-form-label"><?php echo lang('captcha'); ?></label>
					<div class="layui-input-inline">
					<input type="text" id="L_vercode" name="captcha" required lay-verify="required" placeholder="<?php echo lang('please input the captcha'); ?>" autocomplete="off" class="layui-input">
					</div>
					<div class="layui-form-mid layui-word-aux" style="padding-top: 0px !important;">
						<img id="captcha" src="<?php echo captcha_src(); ?>" onclick="this.src='<?php echo captcha_src(); ?>?'+Math.random();"  alt="captcha" />
					</div>
				</div>
				<?php endif; ?>			  
				<div class="layui-form-item">
					<button type="submit" class="layui-btn" lay-filter="user-register" lay-submit><?php echo lang('register now'); ?></button>
				</div>
			<?php else: ?>
				<div class="layui-form-item">抱歉，暂未开放注册</div>
			<?php endif; ?>
				<!--div class="layui-form-item fly-form-app">
				<span>或者直接使用社交账号快捷注册</span>
				<a href="" onclick="layer.msg('正在通过QQ登入', {icon:16, shade: 0.1, time:0})" class="iconfont icon-qq" title="QQ登入"></a>
				<a href="" onclick="layer.msg('正在通过微博登入', {icon:16, shade: 0.1, time:0})" class="iconfont icon-weibo" title="微博登入"></a>
				</div-->
			</form>
			</div>
		</div>
		</div>
	</div>
	</div>
</div>
<?php if(app('request')->isMobile()): ?>
<div class="layui-panel site-menu layui-hide-md">
    <ul class="layui-menu layui-menu-lg">
		<li class="search" style="padding-left:5px;padding-top:2px;padding-right:5px;">
			<form action="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo url('index/search',['keywords'=>app('request')->param('keywords')]); ?>">
				<input  type="search" name="keywords" value="" aria-label="Search text" placeholder="搜索" class="layui-input">
			</form>
		</li>
	<?php if(session('?user_id')): ?>
		<li class="layui-nav-item" style="padding-left:25px;">
			<a class="fly-nav-avatar" href="javascript:;">
				<img src="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo htmlentities((string) $user['user_img']); ?>" >
			</a>
		</li>
	<?php else: ?>
		<li class="layui-nav-item" style="padding-left:25px;">
			<a class="iconfont icon-touxiang" href="<?php echo url('login/index'); ?>"></a>
			<a href="<?php echo url('login/index'); ?>"> <?php echo lang('login'); ?> </a>
			<a href="<?php echo url('login/reg'); ?>"> <?php echo lang('register'); ?> </a>
		</li>
		<li class="layui-nav-item" style="padding-left:25px;">
			<select name="language" style="width:100px;" lay-filter="language1" lay-verify="" id="language1">
			<option value="cn" <?php if(cookie('think_lang')=='zh-cn'): ?> selected<?php endif; ?> ><?php echo lang('chinese'); ?></option>
			<option value="tw" <?php if(cookie('think_lang')=='zh-tw'): ?> selected<?php endif; ?> ><?php echo lang('tChinese'); ?></option>
			<option value="en" <?php if(cookie('think_lang')=='en-us'): ?> selected<?php endif; ?> ><?php echo lang('english'); ?></option>
			</select>
		</li>
	<?php endif; ?>
	
		<li class="layui-menu-item-group" lay-options="{type: 'group', isAllowSpread: true}">
			<div class="layui-menu-body-title">
				社区分类
			</div>
			<hr>
			<ul>
			<?php $__CATE__ = \app\facade\Category::getNav(); if(!(empty($__CATE__) || (($__CATE__ instanceof \think\Collection || $__CATE__ instanceof \think\Paginator ) && $__CATE__->isEmpty()))): if(is_array($__CATE__) || $__CATE__ instanceof \think\Collection || $__CATE__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__CATE__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nav): $mod = ($i % 2 );++$i;?>
				<li <?php if($nav['ename'] == app('request')->param('ename')): ?> class="layui-this" <?php endif; ?> class="layui-menu-item-group layui-menu-item-down" lay-options="{type: 'group'}">
					<div class="layui-menu-body-title">
						<a href="<?php echo htmlentities((string) $nav['url']); ?>">
							<i class="layui-icon <?php echo htmlentities((string) $nav['icon']); ?>"></i><?php echo htmlentities((string) $nav['catename']); if($nav['is_hot'] == 1): ?><span class="layui-badge-dot"></span><?php endif; ?>
						</a>
					</div>
					<?php if(!(empty($nav['children']) || (($nav['children'] instanceof \think\Collection || $nav['children'] instanceof \think\Paginator ) && $nav['children']->isEmpty()))): ?>
					<ul>
						<?php if(!(empty($nav['children']) || (($nav['children'] instanceof \think\Collection || $nav['children'] instanceof \think\Paginator ) && $nav['children']->isEmpty()))): if(is_array($nav['children']) || $nav['children'] instanceof \think\Collection || $nav['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $nav['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$snav): $mod = ($i % 2 );++$i;?>
						<li><a href="<?php echo htmlentities((string) $snav['url']); ?>"><?php echo htmlentities((string) $snav['catename']); ?></a></li>
						<?php endforeach; endif; else: echo "" ;endif; ?><?php endif; ?>
					</ul>
					<?php endif; ?>
				</li>
			<?php endforeach; endif; else: echo "" ;endif; ?><?php endif; ?>
			</ul>
		</li>
      
		<li class="layui-menu-item-group" lay-options="{type: 'group', isAllowSpread: true}">
			<div class="layui-menu-body-title">菜单</div>
			<hr>
			<ul>
				<?php echo hook('ads_mobile_link'); ?>
				<li class="">
					<div class="layui-menu-body-title">
						<a href="/">
							<i class="layui-icon layui-icon-home" style="color: #009688;"></i><span> 回首页</span> 
							<span class="layui-font-12 layui-font-gray">index</span>
						</a>
					</div>
				</li> 
			</ul>
		</li>
    </ul>
</div>
<?php endif; ?>




<footer class="footer">
	<div class="layui-container">
		<div class="footer-col footer-col-logo">
			<img src="<?php echo htmlentities((string) $sysInfo['logo']); ?>" alt="<?php echo htmlentities((string) $sysInfo['webname']); ?>">
		</div>
		<div class="footer-col footer-col-copy">
			<?php echo hook('ads_footer_flink'); ?>
			<?php echo hook('ads_footer_link'); ?>
			<div class="copyright">
				<span class="layui-hide"> v<?php echo config('taoler.version'); ?></span>
				Copyright © <?php echo date('Y'); ?> <?php echo $sysInfo['copyright']; ?>
				<a href="https://www.aieok.com" target="blank" title="TaoLerCMS" class="layui-hide">TaoLerCMS</a>
				<a href="https://beian.miit.gov.cn/" target="blank"><?php echo htmlentities((string) $sysInfo['icp']); ?></a>
			</div>
			
			<div style="text-align:center;color:#999;font-size:14px;padding:0 0 10px;" id="online_count"></div>
		</div>
		<div class="footer-col footer-col-sns">
		</div>
	</div>
</footer>

<script>
    var $ = layui.jquery;
    var articleAdd  = "<?php echo url('add_article',['cate'=>app('request')->param('ename')]); ?>",
      uploads     = "<?php echo url('article/uploads'); ?>",
      langUrl     = "<?php echo url('index/language'); ?>",
      jumpUrl     = "<?php echo url('/jump/index/'); ?>";
</script>





<script>
	var element = layui.element;
	var toast = layui.toast;
	var notify = layui.notify;

	layui.cache.user = {
		username: "<?php echo isset($user['name']) ? htmlentities((string) $user['name']) : '游客'; ?>"
		,uid: "<?php echo isset($user['id']) ? htmlentities((string) $user['id']) : -1; ?>"
		,avatar: '/static/tpl/taoler/images/avatar/00.jpg'
		,experience: "<?php echo isset($user['point']) ? htmlentities((string) $user['point']) : 0; ?>"
		,sex: "<?php echo !empty($user['sex']) ? '女' : '男'; ?>"
	};
	
	layui.config({
		version: "3.0.0"
		,base: "/static/tpl/taoler/mods/"
	}).extend({
		fly: 'index'
	}).use('fly');

	$.get("<?php echo url('login_status'); ?>", function(res) {
		if(res.code === 0) {
			var LOGHTML = `<li class="layui-nav-item"><a class="layui-icon layui-icon-username" style="font-size: 20px;" href="<?php echo url('user_login'); ?>"></a></li>
				<li class="layui-nav-item layui-hide-xs"><a href="<?php echo url('user_login'); ?>"><?php echo lang('login'); ?></a></li>
				<li class="layui-nav-item layui-hide-xs"><a href="<?php echo url('user_reg'); ?>"><?php echo lang('register'); ?></a></li>
				<li class="layui-nav-item layui-hide-xs layui-hide">
					<select name="language" style="width:50px;" lay-filter="language" lay-verify="" id="language">
						<option value="cn" <?php if(cookie('think_lang') == 'zh-cn'): ?> selected <?php endif; ?> ><?php echo lang('chinese'); ?></option>
						<option value="tw" <?php if(cookie('think_lang') == 'zh-tw'): ?> selected <?php endif; ?> ><?php echo lang('tChinese'); ?></option>
						<option value="en" <?php if(cookie('think_lang') == 'en-us'): ?> selected <?php endif; ?> ><?php echo lang('english'); ?></option>
					</select>
				</li>`;
		} else {
			var LOGHTML = `<?php if((app('request')->action()=='user')): ?>
			<li class="layui-nav-item"><a href="/"><?php echo lang('home page'); ?></a></li>
			<?php endif; ?>
			<li class="layui-nav-item" lay-unselect>
				<a class="fly-nav-avatar" href="<?php echo url('user/index'); ?>"><cite class="layui-hide-xs">${res.data.name}</cite><img src="${res.data.avatar}"></a>
				<dl class="layui-nav-child">
					<dd><a href="<?php echo url('user/index'); ?>"><i class="layui-icon layui-icon-username"></i><?php echo lang('user center'); ?></a></dd>
					<dd><a href="<?php echo url('user/set'); ?>"><i class="layui-icon layui-icon-set"></i><?php echo lang('set info'); ?></a></dd>
					<dd><a href="<?php echo url('user/message'); ?>"><i class="iconfont icon-tongzhi"></i><?php echo lang('my message'); ?></a></dd>
					<dd><a href="${res.data.user_home}"><i class="layui-icon layui-icon-home"></i><?php echo lang('my page'); ?></a></dd>
					<dd><a data-url="<?php echo url('user/logout'); ?>" href="javascript:void(0)" id="logout" style="text-align: center;"><?php echo lang('logout'); ?></a></dd>
				</dl>
			</li>`;
		}

		$('.fly-nav-user').append(LOGHTML);
		// 渲染导航组件
		element.render('nav', 'header-filter-nav');
	  })

</script>

<script>
layui.use(['form','layer'],function(){
    var $ = layui.jquery;
    var form = layui.form;
    var layer = layui.layer;
	//注册
	form.on('submit(user-register)', function(data){
		var field = data.field;
		var loading = layer.load(2, {
                shade: [0.2, '#000']
            });
		$.ajax({
			type:'post',
			url:"<?php echo url('Login/reg'); ?>",
			data:field,
			dataType:"json",
			success:function(res){
			layer.close(loading);
				if(res.code === 0){
					toast.success({title:"成功消息",message: res.msg});
					// location.href = res.url;
				} else {
					layer.open({title:"注册失败",content:res.msg,icon:5,anim:6});
					$('#captcha').attr('src', '<?php echo captcha_src(); ?>?'+Math.random());
				}}
			});
		return false;
	});

	//表单取值
	$('#LAY-component-form-getval').on('click', function(){
		var loadIndex = layer.load(2);
		var data = form.val('register');
		//alert(JSON.stringify(data));
		$.post("<?php echo url('Login/sentMailCode'); ?>",{"email": data.email},  function(res){
			layer.close(loadIndex);
			if(res.code === 0){
				toast.success({title:"成功消息",message: res.msg});
			} else {
				layer.open({title:"发送失败",content:res.msg,icon:5,anim:6});
			}
		})
	});

});
</script>

<?php echo hook('addon_hooks'); ?>
</body>
</html>

