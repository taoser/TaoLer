<?php /*a:7:{s:51:"E:\github\TaoLer\view\index\article\posts\cate.html";i:1730713592;s:38:"E:\github\TaoLer\view\public\base.html";i:1730713592;s:40:"E:\github\TaoLer\view\public\header.html";i:1730854752;s:40:"E:\github\TaoLer\view\public\column.html";i:1730854784;s:38:"E:\github\TaoLer\view\public\menu.html";i:1730713592;s:40:"E:\github\TaoLer\view\public\footer.html";i:1730713592;s:36:"E:\github\TaoLer\view\public\js.html";i:1730713592;}*/ ?>
<!--
 * @Author: TaoLer <alipay_tao@qq.com>
 * @Date: 2021-12-06 16:04:51
 * @LastEditTime: 2022-08-10 16:50:38
 * @LastEditors: TaoLer
 * @Description: 搜索引擎SEO优化设置
 * @FilePath: \github\TaoLer\view\taoler\index\public\base.html
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
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
  <title><?php echo htmlentities((string) $cateinfo['catename']); ?> - <?php echo htmlentities((string) $sysInfo['webname']); ?></title>
  <meta name="keywords" content="<?php echo htmlentities((string) $cateinfo['catename']); ?>,<?php echo htmlentities((string) $sysInfo['webname']); ?>" />
  <meta name="description" content="<?php echo htmlentities((string) $cateinfo['desc']); ?>" />
  <meta name="Copyright" content="<?php echo htmlentities((string) $sysInfo['webname']); ?>" />
  <meta property="og:title" content="<?php echo htmlentities((string) $cateinfo['catename']); ?> - <?php echo htmlentities((string) $sysInfo['webname']); ?>">
  <meta property="og:description" content="<?php echo htmlentities((string) $cateinfo['desc']); ?>" />
  <meta property="og:url" content="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo htmlentities((string) app('request')->url()); ?>" />
  <meta property="og:site_name" content="<?php echo htmlentities((string) $sysInfo['webname']); ?>" />
   
  
	<!-- SEO优化 -->
	<meta name="Copyright" content="<?php echo htmlentities((string) $sysInfo['webname']); ?>">
	<meta property="og:type" content="website"/>
	<meta property="og:image" content=""/>


  <script src="/static/component/layui/layui.js" charset="utf-8"></script>
  <script src="/static/res/mods/toast.js"></script>
  <script src="/static/notify.js"></script>
  <!-- 样式 -->
  <link rel="canonical" href="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo htmlentities((string) app('request')->url()); ?>">
  <link rel="stylesheet" href="/static/res/css/font_24081_qs69ykjbea.css" />
  <link rel="stylesheet" href="/static/component/layui/css/layui.css">
  <link rel="stylesheet" href="/static/res/css/global.css">
  <link rel="stylesheet" href="/static/component/pear/css/module/toast.css">
  
  <script src="/static/share/plusShare.js" type="text/javascript" charset="utf-8"></script>
  <?php echo $sysInfo['showlist']; ?>
</head>
<body >
<div class="fly-header layui-bg-black">
	<div class="layui-container">
		<a class="fly-logo layui-hide-xs" href="<?php echo htmlentities((string) app('request')->domain()); ?>"><img src="<?php echo htmlentities((string) app('request')->domain()); ?>/<?php echo htmlentities((string) $sysInfo['logo']); ?>"  alt="<?php echo htmlentities((string) $sysInfo['webname']); ?>logo"></a>
		<!--头部伸缩侧边栏-->
		<div class="site-tree-mobile-top layui-hide"><i class="layui-icon layui-icon-spread-left"></i></div>
		<div class="site-mobile-shade-top"></div>
		
		<a class="layui-hide-md layui-hide-sm" href="<?php echo htmlentities((string) app('request')->domain()); ?>" ><img class="fly-logo-m" src="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo htmlentities((string) $sysInfo['m_logo']); ?>" alt="logo"></a>
		
		<ul class="layui-nav fly-nav layui-hide-xs">
		<?php if(config('taoler.config.nav_top')  != 0): $__cate__ = \app\facade\Cate::getNav(); if(is_array($__cate__) || $__cate__ instanceof \think\Collection || $__cate__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__cate__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nav): $mod = ($i % 2 );++$i;?>
			<li class="layui-nav-item <?php if('' == 'nav.ename'): ?> layui-this <?php endif; ?>" >
				<a href="<?php echo htmlentities((string) $nav['url']); ?>"><?php echo cookie('think_lang') == 'en-us' ? $nav['ename'] : $nav['catename']; if($nav['is_hot'] == '1'): ?> <span class="layui-badge-dot"></span> <?php endif; ?></a>
				<?php if(!(empty($nav['children']) || (($nav['children'] instanceof \think\Collection || $nav['children'] instanceof \think\Paginator ) && $nav['children']->isEmpty()))): ?>
				<dl class="layui-nav-child"> <!-- 二级菜单 -->
					<?php if(!(empty($nav['children']) || (($nav['children'] instanceof \think\Collection || $nav['children'] instanceof \think\Paginator ) && $nav['children']->isEmpty()))): if(is_array($nav['children']) || $nav['children'] instanceof \think\Collection || $nav['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $nav['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$snav): $mod = ($i % 2 );++$i;?>
					<dd><a href="<?php echo htmlentities((string) $snav['url']); ?>"><?php echo htmlentities((string) $snav['catename']); ?></a></dd>
					<?php endforeach; endif; else: echo "" ;endif; ?><?php endif; ?>
				</dl>
				<?php endif; ?>
			</li>
		<?php endforeach; endif; else: echo "" ;endif; ?>
		<?php endif; ?>
		
		<?php echo hook('ads_header_link'); ?>
		</ul>

		
		<ul class="layui-nav fly-nav-user" msg-url="<?php echo url('message/nums'); ?>" readMsg-url="<?php echo url('Message/read'); ?>" userlogin="<?php echo url('user_login'); ?>">
			<li class="layui-nav-item">
				<span class="fly-search layui-hide-xs" data-url="<?php echo url('user_search'); ?>"><i class="layui-icon layui-icon-search"></i></span>
			</li>
			<!-- 登录 -->
			<?php if(session('?user_id')): if((app('request')->action()=='user')): ?>
				<li class="layui-nav-item"><a href="<?php echo htmlentities((string) app('request')->domain()); ?>"><?php echo lang('home page'); ?></a></li>
				<?php endif; ?>
				<li class="layui-nav-item">
					<a class="fly-nav-avatar" ><cite class="layui-hide-xs"><?php echo htmlentities((string) $user['name']); ?></cite><img src="<?php echo htmlentities((string) $user['user_img']); ?>"></a>
					<dl class="layui-nav-child">
						<dd><a href="<?php echo url('user/index'); ?>"><i class="layui-icon layui-icon-username"></i><?php echo lang('user center'); ?></a></dd>
						<dd><a href="<?php echo url('user/set'); ?>"><i class="layui-icon layui-icon-set"></i><?php echo lang('set info'); ?></a></dd>
						<dd><a href="<?php echo url('user/message'); ?>"><i class="iconfont icon-tongzhi"></i><?php echo lang('my message'); ?></a></dd>
						<dd><a href="<?php echo url('user/home',['id'=>$user['id']]); ?>"><i class="layui-icon layui-icon-home"></i><?php echo lang('my page'); ?></a></dd>
						<dd><a data-url="<?php echo url('user/logout'); ?>" href="javascript:void(0)" class="logi_logout" style="text-align: center;"><?php echo lang('logout'); ?></a></dd>
					</dl>
				</li>
			<!-- 未登入的状态 -->
			<?php else: ?>
				<li class="layui-nav-item"><a class="layui-icon layui-icon-username" style="font-size: 20px;" href="<?php echo url('user_login'); ?>"></a></li>
				<li class="layui-nav-item layui-hide-xs"><a href="<?php echo url('user_login'); ?>"><?php echo lang('login'); ?></a></li>
				<li class="layui-nav-item layui-hide-xs"><a href="<?php echo url('user_reg'); ?>"><?php echo lang('register'); ?></a></li>
				<li class="layui-nav-item layui-hide-xs layui-hide">
					<select name="language" style="width:50px;" lay-filter="language" lay-verify="" id="language">
						<option value="cn" <?php if(cookie('think_lang') == 'zh-cn'): ?> selected <?php endif; ?> ><?php echo lang('chinese'); ?></option>
						<option value="tw" <?php if(cookie('think_lang') == 'zh-tw'): ?> selected <?php endif; ?> ><?php echo lang('tChinese'); ?></option>
						<option value="en" <?php if(cookie('think_lang') == 'en-us'): ?> selected <?php endif; ?> ><?php echo lang('english'); ?></option>
					</select>
				</li>
			<?php endif; ?>
		</ul>
	</div>
</div>
<div class="fly-panel fly-column layui-hide-xs">
  <div class="layui-container fly-nav-sub">
    <ul class="layui-nav layui-bg-white layui-hide-xs">
      <li class="layui-nav-item  layui-hide-xs <?php if(('' == '') AND ('' == '')): ?> layui-this <?php endif; ?>" >
        <a href="<?php echo htmlentities((string) app('request')->domain()); ?>"><?php echo lang('home page'); ?></a>
      </li>
      
      <?php if(config('taoler.config.nav_top')  == 0): $__cate__ = \app\facade\Cate::getNav(); if(is_array($__cate__) || $__cate__ instanceof \think\Collection || $__cate__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__cate__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nav): $mod = ($i % 2 );++$i;?>
        <li class="layui-nav-item <?php if('' == 'nav.ename'): ?> layui-this <?php endif; ?>" >
          <a href="<?php echo htmlentities((string) $nav['url']); ?>"><?php echo cookie('think_lang') == 'en-us' ? $nav['ename'] : $nav['catename']; if($nav['is_hot'] == '1'): ?> <span class="layui-badge-dot"></span> <?php endif; ?></a>
          <?php if(!(empty($nav['children']) || (($nav['children'] instanceof \think\Collection || $nav['children'] instanceof \think\Paginator ) && $nav['children']->isEmpty()))): ?>
          <dl class="layui-nav-child"> <!-- 二级菜单 -->
            <?php if(!(empty($nav['children']) || (($nav['children'] instanceof \think\Collection || $nav['children'] instanceof \think\Paginator ) && $nav['children']->isEmpty()))): if(is_array($nav['children']) || $nav['children'] instanceof \think\Collection || $nav['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $nav['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$snav): $mod = ($i % 2 );++$i;?>
            <dd><a href="<?php echo htmlentities((string) $snav['url']); ?>"><?php echo htmlentities((string) $snav['catename']); ?></a></dd>
            <?php endforeach; endif; else: echo "" ;endif; ?><?php endif; ?>
          </dl>
          <?php endif; ?>
        </li>
        <?php endforeach; endif; else: echo "" ;endif; ?>
      <?php endif; ?>
    </ul>
    <div class="fly-column-right layui-hide-xs">
      <a href="<?php echo url('add_article',['cate'=>app('request')->param('ename')]); ?>" class="layui-btn" id="add_post"><?php echo lang('add post'); ?></a>
    </div> 
  </div>
</div>

<div class="layui-container">
	<div class="layui-row layui-col-space15">
		<div class="layui-col-md8">
			<div class="fly-panel" style="margin-bottom: 0;">
				<ul class="fly-list">
					<?php if(is_array($artList['data']) || $artList['data'] instanceof \think\Collection || $artList['data'] instanceof \think\Paginator): $i = 0; $__LIST__ = $artList['data'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$art): $mod = ($i % 2 );++$i;?>
					<li>
						<a href="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo url('user/home',['id'=>$art['user']['id']]); ?>" class="fly-avatar">
							<img src="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo htmlentities((string) $art['user']['user_img']); ?>" alt="<?php echo htmlentities((string) $art['user']['name']); ?>">
						</a>
						<h2>
							<a href="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo htmlentities((string) $art['url']); ?>"  style="color:<?php echo isset($art['title_color']) ? htmlentities((string) $art['title_color']) : '#333'; ?>;"><?php echo htmlentities((string) $art['title']); ?></a>
						</h2>
						
						<div class="fly-list-info">
							<?php if(config('taoler.config.cate_show') == 1): ?>
							<a class="layui-badge"><?php echo cookie('think_lang') == 'en-us' ? $art['cate']['ename'] : $art['cate']['catename']; ?></a>
							<?php endif; ?>
							<a href="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo url('user/home',['id'=>$art['user']['id']]); ?>" link>
								<cite><?php echo !empty($art['user']['nickname']) ? htmlentities((string) $art['user']['nickname']) : htmlentities((string) $art['user']['name']); ?></cite>
								<i><?php echo htmlentities((string) date('Y-m-d',!is_numeric($art['create_time'])? strtotime($art['create_time']) : $art['create_time'])); ?></i>
							</a>
							
							
							<?php if(!empty($art['has_img'])) echo '<span><i class="layui-icon layui-icon-picture" style="color: #5FB878;"></i></span>'; if(!empty($art['has_video'])) echo '<span><i class="layui-icon layui-icon-play" style="color: #FF5722;"></i></span>'; if(!empty($art['has_audio'])) echo '<span><i class="layui-icon layui-icon-speaker" style="color: #000000;"></i></span>'; if(!empty($art['read_type'])) echo '<span><i class="layui-icon layui-icon-password" style="color: #FF5722;"></i></span>'; if(!empty($art['upzip'])) echo '<span><i class="layui-icon layui-icon-file-b" style="color: #009688;" title="附件"></i></span>'; ?>
							<span class="layui-hide-xs" title="浏览"><i class="iconfont" title="浏览">&#xe60b;</i> <?php echo htmlentities((string) $art['pv']); ?></span>
							<span class="fly-list-nums"><i class="iconfont icon-pinglun1" title="回答"></i> <?php echo htmlentities((string) $art['comments_count']); ?></span>
						</div>
						<div class="fly-list-badge">
							<?php if(($art['is_hot'] == 1)): ?>
							<i class="layui-icon layui-icon-fire layui-hide-md" style="font-size: 20px; color: #FF5722;"></i><span class="layui-badge layui-bg-red layui-hide-xs"><?php echo lang('hot'); ?></span>
							<?php endif; ?>
						</div>
					</li>
					<?php endforeach; endif; else: echo "" ;endif; ?>	
				</ul>
				<div style="text-align: center" id="pages"></div>
			</div>
		</div>
		<div class="layui-col-md4">
			<!-- 插件hook位 -->
			<?php echo hook('addonhook_list'); ?>

			
			<div class="fly-panel layui-hide-xs">
				<div class="fly-panel-title">热门标签</div>
				<div class="fly-panel-main">
					<ul>
						<?php if(is_array($hotTag) || $hotTag instanceof \think\Collection || $hotTag instanceof \think\Paginator): $i = 0; $__LIST__ = $hotTag;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
						<li style="padding:10px;display:inline-block;height: 15px;"><a href="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo htmlentities((string) $vo['url']); ?>"><?php echo htmlentities((string) $vo['name']); ?></a></li>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</ul>
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
			<?php $__cate__ = \app\facade\Cate::getNav(); if(is_array($__cate__) || $__cate__ instanceof \think\Collection || $__cate__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__cate__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nav): $mod = ($i % 2 );++$i;?>
				<li <?php if($nav['ename'] == app('request')->param('ename')): ?> class="layui-this" <?php endif; ?> class="layui-menu-item-group layui-menu-item-down" lay-options="{type: 'group'}">
					<div class="layui-menu-body-title">
						<a href="<?php echo htmlentities((string) $nav['url']); ?>">
							<i class="layui-icon <?php echo htmlentities((string) $nav['icon']); ?>"></i><?php echo cookie('think_lang') == 'en-us' ? $nav['ename'] : $nav['catename']; if($nav['is_hot'] == 1): ?><span class="layui-badge-dot"></span><?php endif; ?>
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
			<?php endforeach; endif; else: echo "" ;endif; ?>
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
			<div class="footer-sns">
				<a class="sns-wx" href="javascript:;" aria-label="icon">
					<i class="layui-icon layui-icon-login-wechat sns-icon"></i>
					<span style="background-image:url(<?php echo hook('qqKefu','wxqr'); ?>);"></span>
				</a>
				<a class="sns-wx" href="javascript:;" aria-label="icon">
					<i class="layui-icon layui-icon-chat sns-icon"></i>
					<span style="background-image:url(<?php echo hook('qqKefu','qqqr'); ?>);"></span>
				</a>
				<a href="<?php echo hook('qqKefu','qq'); ?>" target="_blank" rel="nofollow noopener" aria-label="icon" title="点击跟我聊天">
					<i class="layui-icon layui-icon-login-qq sns-icon"></i>
				</a>
			</div>
		</div>
	</div>
</footer>


<?php echo hook('showLeftLayer'); ?>
<?php echo hook('msgnum'); ?>
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
		,avatar: '/static/res/images/avatar/00.jpg'
		,experience: "<?php echo isset($user['point']) ? htmlentities((string) $user['point']) : 0; ?>"
		,sex: "<?php echo !empty($user['sex']) ? '女' : '男'; ?>"
	};
	
	layui.config({
		version: "3.0.0"
		,base: "/static/res/mods/"
	}).extend({
		fly: 'index'
	}).use('fly');
</script>

<script>
    layui.use(['laypage'], function(){
		var laypage = layui.laypage;
		
		//执行一个laypage实例
		laypage.render({
			elem: 'pages' //注意，这里的 test1 是 ID，不用加 # 号
			,count: "<?php echo htmlentities((string) $artList['total']); ?>" //数据总数，从服务端得到
			,groups: 1
			,limit: "<?php echo htmlentities((string) $artList['per_page']); ?>"
			,curr : "<?php echo htmlentities((string) $artList['current_page']); ?>"
			,first: false
    		,last: false
			,layout: ['prev', 'page','next','count']
			//获取起始页
			,jump: function(obj, first){
				//首次不执行
				if(!first){
					window.location.href = '<?php echo htmlentities((string) $path); ?>/'+obj.curr+'.html'; //跳转
				}
			}
	  	})
	});
</script>


</body>
</html>

