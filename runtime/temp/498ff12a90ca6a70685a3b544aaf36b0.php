<?php /*a:8:{s:55:"E:\github\TaoLer\app\index\view\taoler\index\index.html";i:1779627432;s:55:"E:\github\TaoLer\app\index\view\taoler\public\base.html";i:1760276314;s:57:"E:\github\TaoLer\app\index\view\taoler\public\header.html";i:1760276314;s:57:"E:\github\TaoLer\app\index\view\taoler\public\column.html";i:1745242959;s:60:"E:\github\TaoLer\app\index\view\taoler\public\index-top.html";i:1760276314;s:55:"E:\github\TaoLer\app\index\view\taoler\public\menu.html";i:1744610627;s:57:"E:\github\TaoLer\app\index\view\taoler\public\footer.html";i:1737273662;s:53:"E:\github\TaoLer\app\index\view\taoler\public\js.html";i:1737273662;}*/ ?>
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
	<meta property="og:title" content="<?php echo htmlentities((string) $sysInfo['webtitle']); ?>" >
	<meta property="og:description" content="<?php echo htmlentities((string) $sysInfo['descript']); ?>" >
	<meta property="og:url" content="<?php echo htmlentities((string) app('request')->url()); ?>" />
	<meta property="og:site_name" content="<?php echo htmlentities((string) $sysInfo['webname']); ?>" />
	<meta property="og:image" content="<?php echo htmlentities((string) $sysInfo['logo']); ?>" >
	

	<script src="/static/layui/layui.js" charset="utf-8"></script>
	<script src="/static/tpl/taoler/mods/toast.js"></script>
	<script src="/static/common/notify.js"></script>
	<!-- 样式 -->
	<link rel="canonical" href="<?php echo htmlentities((string) app('request')->url()); ?>">
	<link rel="stylesheet" href="/static/tpl/taoler/css/font_24081_qs69ykjbea.css" />
	<link rel="stylesheet" href="/static/layui/css/layui.css">
	<link rel="stylesheet" href="/static/tpl/taoler/css/global.css">
	<link rel="stylesheet" href="/static/component/pear/css/module/toast.css">
	
<!-- 特效丶样式 -->


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
<div class="fly-panel fly-column layui-hide-xs">
    <div class="layui-container fly-nav-sub">
        <ul class="layui-nav layui-bg-white layui-hide-xs">
            <li class="layui-nav-item  layui-hide-xs <?php if((app('request')->param('ename') =='' && app('request')->param('id') =='')): ?> layui-this <?php endif; ?>" >
                <a href="<?php echo htmlentities((string) app('request')->domain()); ?>"><?php echo lang('home page'); ?></a>
            </li>
            
            <?php if(config('taoler.config.nav_top')  == 0): $__CATE__ = \app\facade\Category::getNav(); if(!(empty($__CATE__) || (($__CATE__ instanceof \think\Collection || $__CATE__ instanceof \think\Paginator ) && $__CATE__->isEmpty()))): if(is_array($__CATE__) || $__CATE__ instanceof \think\Collection || $__CATE__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__CATE__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nav): $mod = ($i % 2 );++$i;?>
                <li class="layui-nav-item <?php if($nav['ename'] == app('request')->param('ename')): ?> layui-this <?php endif; ?>" >
                    <a href="<?php echo htmlentities((string) $nav['url']); ?>"><?php echo htmlentities((string) $nav['catename']); if($nav['is_hot'] == 1): ?> <span class="layui-badge-dot"></span> <?php endif; ?></a>
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
        </ul>

        <div class="fly-column-right layui-hide-xs">
            <a href="<?php echo url('add_article',['cate'=>app('request')->param('ename')]); ?>" class="layui-btn" id="add_post"><?php echo lang('add post'); ?></a>
        </div>
         
    </div>
</div>

<div class="layui-container article-content">
	<div class="layui-row layui-col-space15">
		<!--左栏-->
		<div class="layui-col-md8">

			<!--首页幻灯-->
			<div class="fly-panel">
				<div class="layui-row">
				  <div class="layui-carousel fly-topline" id="TAOLER-SLIDER">
					<div carousel-item="">
					  <?php $__SLIDE__ = \app\facade\AdSlide::getSlide(1,5); if(is_array($__SLIDE__) || $__SLIDE__ instanceof \think\Collection || $__SLIDE__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__SLIDE__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$slide): $mod = ($i % 2 );++$i;?>
						<div time-limit="">
						  <a href="<?php echo htmlentities((string) $slide['url']); ?>" target="_blank" rel="nofollow">
							<img src="<?php echo htmlentities((string) $slide['image']); ?>" alt="<?php echo htmlentities((string) $slide['title']); ?>" />
						  </a>
						</div>
					  <?php endforeach; endif; else: echo "" ;endif; ?>
					</div>
				  </div>
				</div>
			</div>

			<?php $section = \app\facade\Section::getSection('service'); if(!(empty($section) || (($section instanceof \think\Collection || $section instanceof \think\Paginator ) && $section->isEmpty()))): $__SECTIONSUB__ = \app\facade\SectionAccess::getSectionAccess("service",10); ?>
				<?php echo htmlentities((string) $section['title']); ?> - <?php echo htmlentities((string) $section['subtitle']); ?>
				<p></p>
				<?php if(is_array($__SECTIONSUB__) || $__SECTIONSUB__ instanceof \think\Collection || $__SECTIONSUB__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__SECTIONSUB__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$section): $mod = ($i % 2 );++$i;?>
					<?php echo htmlentities((string) $section['name']); ?>- -
				<?php endforeach; endif; else: echo "" ;endif; ?>
			<?php endif; $section = \app\facade\Section::getSection('cc'); if(!(empty($section) || (($section instanceof \think\Collection || $section instanceof \think\Paginator ) && $section->isEmpty()))): $__SECTIONSUB__ = \app\facade\SectionAccess::getSectionAccess("cc",10); ?>
				<?php echo htmlentities((string) $section['title']); ?>
				<?php echo htmlentities((string) $section['subtitle']); if(is_array($__SECTIONSUB__) || $__SECTIONSUB__ instanceof \think\Collection || $__SECTIONSUB__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__SECTIONSUB__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$section): $mod = ($i % 2 );++$i;?>
					<?php echo htmlentities((string) $section['name']); ?>
				<?php endforeach; endif; else: echo "" ;endif; ?>
			<?php endif; ?>

			<!--置顶文章-->
			<div class="fly-panel">
				<div class="fly-panel-title fly-filter">
					<span><?php echo lang('top'); ?></span>
					<?php if(hook('signstatushook') == 1): ?>
					<a href="#signin" class="layui-hide-sm layui-show-xs-block fly-right" id="LAY_goSignin"><?php echo lang('go sign'); ?></a>
					<?php endif; ?>
				</div>
				<ul class="fly-list">
					<?php if(config('taoler.config.top_show') == 1): $__TOPS__ = \app\facade\Article::getTops(5); if(is_array($__TOPS__) || $__TOPS__ instanceof \think\Collection || $__TOPS__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__TOPS__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$article): $mod = ($i % 2 );++$i;?>
<li>
	<a href="<?php echo url("user_home",["id"=>$article['user']['id']])->domain(true); ?>" class="fly-avatar">
		<img src="<?php echo htmlentities((string) $article['user']['user_img']); ?>" alt="<?php echo !empty($article['user']['nickname']) ? htmlentities((string) $article['user']['nickname']) : htmlentities((string) $article['user']['name']); ?>">
	</a>
	<h2><a href="<?php echo htmlentities((string) $article['url']); ?>"><?php echo htmlentities((string) $article['title']); ?></a></h2>
	<div class="fly-list-info">
		<?php if(config('taoler.config.cate_show') == 1): ?>
		<a class="layui-badge"><?php if(cookie('think_lang') == 'en-us'): ?> <?php echo htmlentities((string) $article['cate']['ename']); else: ?> <?php echo htmlentities((string) $article['cate']['catename']); ?> <?php endif; ?></a>
		<?php endif; ?>
		<a href="<?php echo url("user_home",["id"=>$article['user']['id']])->domain(true); ?>" link>
		<cite><?php echo !empty($article['user']['nickname']) ? htmlentities((string) $article['user']['nickname']) : htmlentities((string) $article['user']['name']); ?></cite>
		</a>
		<i><?php echo htmlentities((string) $article['create_time']); ?></i>
		<?php if($article['has_image'] == '1'): ?> <span><i class="layui-icon layui-icon-picture" style="color: #5FB878;"></i></span><?php endif; if($article['has_video'] == '1'): ?> <span><i class="layui-icon layui-icon-play" style="color: #FF5722;"></i></span><?php endif; if($article['has_audio'] == '1'): ?> <span><i class="layui-icon layui-icon-speaker" style="color: #000000;"></i></span><?php endif; ?>
		<span class=" layui-hide-xs" title="浏览"> <i class="iconfont" title="浏览">&#xe60b;</i> <?php echo htmlentities((string) $article['pv']); ?> </span>
		<span class="fly-list-nums"><i class="iconfont icon-pinglun1" title="回答"></i> <?php echo htmlentities((string) $article['comments_num']); ?></span>
	</div>
	<div class="fly-list-badge">
	<?php if($article['flags']['is_top'] == '1'): ?>
	<span class="layui-badge layui-bg-black layui-hide-xs" ><?php echo lang('top'); ?></span>
	<?php endif; ?>
	</div>
</li>
<?php endforeach; endif; else: echo "" ;endif; else: ?>
						
						<div class="layui-carousel" id="ID-carousel">
							<div carousel-item>
								<?php $__TOPS__ = \app\facade\Article::getTops(5); if(is_array($__TOPS__) || $__TOPS__ instanceof \think\Collection || $__TOPS__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__TOPS__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$article): $mod = ($i % 2 );++$i;?>
<li>
	<a href="<?php echo url("user_home",["id"=>$article['user']['id']])->domain(true); ?>" class="fly-avatar">
		<img src="<?php echo htmlentities((string) $article['user']['user_img']); ?>" alt="<?php echo !empty($article['user']['nickname']) ? htmlentities((string) $article['user']['nickname']) : htmlentities((string) $article['user']['name']); ?>">
	</a>
	<h2><a href="<?php echo htmlentities((string) $article['url']); ?>"><?php echo htmlentities((string) $article['title']); ?></a></h2>
	<div class="fly-list-info">
		<?php if(config('taoler.config.cate_show') == 1): ?>
		<a class="layui-badge"><?php if(cookie('think_lang') == 'en-us'): ?> <?php echo htmlentities((string) $article['cate']['ename']); else: ?> <?php echo htmlentities((string) $article['cate']['catename']); ?> <?php endif; ?></a>
		<?php endif; ?>
		<a href="<?php echo url("user_home",["id"=>$article['user']['id']])->domain(true); ?>" link>
		<cite><?php echo !empty($article['user']['nickname']) ? htmlentities((string) $article['user']['nickname']) : htmlentities((string) $article['user']['name']); ?></cite>
		</a>
		<i><?php echo htmlentities((string) $article['create_time']); ?></i>
		<?php if($article['has_image'] == '1'): ?> <span><i class="layui-icon layui-icon-picture" style="color: #5FB878;"></i></span><?php endif; if($article['has_video'] == '1'): ?> <span><i class="layui-icon layui-icon-play" style="color: #FF5722;"></i></span><?php endif; if($article['has_audio'] == '1'): ?> <span><i class="layui-icon layui-icon-speaker" style="color: #000000;"></i></span><?php endif; ?>
		<span class=" layui-hide-xs" title="浏览"> <i class="iconfont" title="浏览">&#xe60b;</i> <?php echo htmlentities((string) $article['pv']); ?> </span>
		<span class="fly-list-nums"><i class="iconfont icon-pinglun1" title="回答"></i> <?php echo htmlentities((string) $article['comments_num']); ?></span>
	</div>
	<div class="fly-list-badge">
	<?php if($article['flags']['is_top'] == '1'): ?>
	<span class="layui-badge layui-bg-black layui-hide-xs" ><?php echo lang('top'); ?></span>
	<?php endif; ?>
	</div>
</li>
<?php endforeach; endif; else: echo "" ;endif; ?>
							</div>
						</div>
					<?php endif; ?>
				</ul>
			</div>

			<!--文章列表-->
			<section id="main" class="list-home list-grid list-grid-padding">
				<?php $__INDEXS__ = \app\facade\Article::getIndexs(10); if(is_array($__INDEXS__) || $__INDEXS__ instanceof \think\Collection || $__INDEXS__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__INDEXS__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$article): $mod = ($i % 2 );++$i;?>
				<article class="list-item block card-plain">
					<?php if($article['has_image'] > '0'): ?>
					<figure class="media media-3x2  d-none d-md-block">
						<a class="media-content" href="<?php echo htmlentities((string) $article['url']); ?>" title="<?php echo htmlentities((string) $article['title']); ?>">
							<img src="<?php if(!(empty($article['media']['images']) || (($article['media']['images'] instanceof \think\Collection || $article['media']['images'] instanceof \think\Paginator ) && $article['media']['images']->isEmpty()))): ?><?php echo htmlentities((string) $article['media']['images'][0]); ?><?php endif; ?>" alt="<?php echo htmlentities((string) $article['title']); ?>">
						</a>
					</figure>
					<?php endif; ?>
					<div class="list-width list-content">
						<div class="list-body">
							<a href="<?php echo htmlentities((string) $article['url']); ?>" title="<?php echo htmlentities((string) $article['title']); ?>" >
								<h3 class="list-title fanpian"><?php echo htmlentities((string) $article['title']); ?></h3>
								<div class="list-desc d-block d-md-block text-sm text-secondary my-3">
									<p class="h-3x"><?php echo htmlentities((string) $article['description']); ?></p>
								</div>
							</a>
						</div>
						<div class="list-footer">
							<div class="d-flex flex-fill align-items-center text-muted text-xs">
								<time class="d-inline-block" datetime="<?php echo htmlentities((string) $article['create_time']); ?>"><?php echo htmlentities((string) $article['create_time']); ?></time>
								<div class="d-inline-block mx-1 mx-md-2">
									<a href="<?php echo url('cate',['ename'=>$article['cate']['ename']])->domain(true); ?>" class="text-muted"><?php echo htmlentities((string) $article['cate']['catename']); ?></a>
								</div>
								<div class="d-inline-block">
									<a href="<?php echo url("user_home",["id"=>$article['user']['id']])->domain(true); ?>" class="text-muted" title="发布于{article:auther /}" rel="category"><?php echo !empty($article['user']['nickname']) ? htmlentities((string) $article['user']['nickname']) : htmlentities((string) $article['user']['name']); ?></a>
									<?php if($article['has_image'] == '1'): ?> <span><i class="layui-icon layui-icon-picture" style="color: #5FB878;"></i></span><?php endif; if($article['has_video'] == '1'): ?> <span><i class="layui-icon layui-icon-play" style="color: #FF5722;"></i></span><?php endif; if($article['has_audio'] == '1'): ?> <span><i class="layui-icon layui-icon-speaker" style="color: #000000;"></i></span><?php endif; ?>
				
								</div>
								<div class="flex-fill"></div>
								<div class="mx-1">
									<span class="text-muted"><i class="iconfont icon-pinglun1" title="回答"></i> <?php echo htmlentities((string) $article['comments_num']); ?></span>
								</div>
							</div>
						</div>
					</div>
				</article>
				<?php endforeach; endif; else: echo "" ;endif; ?>
			</section>

			<!--更多帖子-->
			<div class="fly-panel" style="margin-bottom: 0;">
				<div style="text-align: center">
					<div class="laypage-main">
					<a href="<?php echo url('cate',['ename'=>'all']); ?>" class="laypage-next"><?php echo lang('more post'); ?></a>
					</div>
				</div>
			</div>

		</div>
		
		
		<div class="layui-col-md4">
			<!-- 插件hook位 -->
			<?php echo hook('signhook', ['id'=>1]); ?>
		</div>
	</div>
</div>
<!--移动端菜单-->
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
	layui.extend({
  ads: '{/}/static/addons/ads/js/ads'
}).use(['util','ads'], function(){
		let util = layui.util;
		let carousel = layui.carousel;

		// 置顶文章
		//tpl模板给发布时间赋值
		$("time").each(function () {
			var othis = $(this);
			var datetime = othis.attr("datetime");
			var posttime = util.timeAgo(datetime, 30);
			othis.text(posttime);
		});

		// 渲染 - 设置时间间隔、动画类型、宽高度等属性
		carousel.render({
			elem: '#ID-carousel',
			interval: 2000,
			anim: 'updown',
			arrow: 'none',
			indicator: 'none',
			width: 'auto',
			height: '80px'
		});

	});
</script>

<?php echo hook('addon_hooks'); ?>
</body>
</html>

