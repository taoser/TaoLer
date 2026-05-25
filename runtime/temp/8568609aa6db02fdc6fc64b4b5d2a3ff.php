<?php /*a:5:{s:64:"E:\github\TaoLer\app\index\view\taoler\article\about\single.html";i:1779271189;s:55:"E:\github\TaoLer\app\index\view\taoler\public\base.html";i:1760276314;s:57:"E:\github\TaoLer\app\index\view\taoler\public\header.html";i:1760276314;s:57:"E:\github\TaoLer\app\index\view\taoler\public\footer.html";i:1737273662;s:53:"E:\github\TaoLer\app\index\view\taoler\public\js.html";i:1737273662;}*/ ?>
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
	<title><?php echo htmlentities((string) $sysInfo['webtitle']); ?></title>
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
导航

    <main class="main">
        <!-- banner -->
        <div class="banner">
            <div class="layui-container">
                <span class="banner-title"><?php echo htmlentities((string) $cateinfo['catename']); ?></span>
            </div>
        </div>
        <div class="layui-container">
            <div class="company-desc info">
                <div class="layui-row layui-col-space30">
                    <div class="layui-col-md6">
                        <h3><?php echo htmlentities((string) $article['title']); ?></h3>
                        <?php echo $article['content']; ?>
                    </div>
                    <div class="layui-col-md6">
                        <div class="right" style="height: 550px;">
                            <img src="/static/tpl/food/image/pic/company_1.png" alt="" style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                        </div>
                    </div> 
                </div>
            </div>
        </div>
        
        
    </main>

    内容

    
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

<?php echo hook('addon_hooks'); ?>
</body>
</html>

