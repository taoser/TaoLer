<?php /*a:9:{s:53:"E:\github\TaoLer\view\index\article\posts\detail.html";i:1730713592;s:38:"E:\github\TaoLer\view\public\base.html";i:1730713592;s:40:"E:\github\TaoLer\view\public\header.html";i:1730854752;s:40:"E:\github\TaoLer\view\public\column.html";i:1730854784;s:38:"E:\github\TaoLer\view\public\crud.html";i:1730713592;s:38:"E:\github\TaoLer\view\public\menu.html";i:1730713592;s:40:"E:\github\TaoLer\view\public\footer.html";i:1730713592;s:36:"E:\github\TaoLer\view\public\js.html";i:1730713592;s:46:"E:\github\TaoLer\view\public\images-click.html";i:1730713592;}*/ ?>
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
  <title><?php echo htmlentities((string) $article['title']); ?> - <?php echo htmlentities((string) $sysInfo['webname']); ?></title>
  <meta name="keywords" content="<?php echo !empty($article['keywords']) ? htmlentities((string) $article['keywords']) : htmlentities((string) $article['title']); ?>" />
  <meta name="description" content="<?php echo htmlentities((string) $article['title']); ?>,<?php echo htmlentities((string) $article['description']); ?>" />
  <meta name="Copyright" content="<?php echo htmlentities((string) $sysInfo['webname']); ?>" />
  <meta property="og:title" content="<?php echo htmlentities((string) $article['title']); ?> - <?php echo htmlentities((string) $sysInfo['webname']); ?>">
  <meta property="og:description" content="<?php echo htmlentities((string) $article['title']); ?>,<?php echo !empty($article['description']) ? htmlentities((string) $article['description']) : ''; ?>" />
  <meta property="og:url" content="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo htmlentities((string) app('request')->url()); ?>" />
  <meta property="og:site_name" content="<?php echo htmlentities((string) $sysInfo['webname']); ?>" />
   
  
<meta property="og:type" content="article"/>
<meta property="article:published_time" content="<?php echo htmlentities((string) date('c',!is_numeric($article['create_time'])? strtotime($article['create_time']) : $article['create_time'])); ?>"/>
<meta property="bytedance:published_time" content="<?php echo htmlentities((string) date('c',!is_numeric($article['create_time'])? strtotime($article['create_time']) : $article['create_time'])); ?>" />
<meta property="bytedance:lrDate_time" content="<?php echo htmlentities((string) date('c',!is_numeric($lrDate_time)? strtotime($lrDate_time) : $lrDate_time)); ?>" />
<meta property="bytedance:updated_time" content="<?php echo htmlentities((string) date('c',!is_numeric($article['update_time'])? strtotime($article['update_time']) : $article['update_time'])); ?>" />


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
<div class="layui-hide-xs"><div class="fly-panel fly-column layui-hide-xs">
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
</div></div>

<div class="layui-container">
	<div class="layui-row layui-col-space15">
		<div class="layui-col-md8 content detail">
			<section class="fly-panel detail-box">

				
				<h1><?php echo htmlentities((string) $article['title']); ?></h1>

				
				<div class="fly-detail-info">
					<span class="layui-badge layui-bg-green fly-detail-column">
					<?php if((cookie('think_lang') == 'en-us')): ?>
						<?php echo htmlentities((string) $article['cate']['ename']); else: ?>
						<?php echo htmlentities((string) $article['cate']['catename']); ?>
					<?php endif; ?>
					</span>
					<?php if(($article['is_top'] == 1)): ?>
					<span class="layui-badge layui-bg-black"><?php echo lang('top'); ?></span>
					<?php endif; if(($article['is_hot'] == 1)): ?>
					<span class="layui-badge layui-bg-red"><?php echo lang('hot'); ?></span>
					<?php endif; ?>
					<span id="LAY_jieAdmin" data-id="<?php echo htmlentities((string) $article['id']); ?>" data-colurl="<?php echo url('collection/find'); ?>"></span>
					<span class="fly-list-nums"> 
						<a href="#comment">
						<i class="iconfont" title="<?php echo lang('reply'); ?>">&#xe60c;</i><?php echo htmlentities((string) $article['comments_count']); ?>
						</a>
						<i class="iconfont" title="浏览">&#xe60b;</i><?php echo htmlentities((string) $article['pv']); ?>
					</span>
				</div>

				
				<div class="detail-about">
					<a class="fly-avatar" href="<?php echo url("user/home",["id"=>$article['user']['id']])->domain(true); ?>">
					<img src="<?php echo htmlentities((string) $article['user']['user_img']); ?>" alt="<?php echo htmlentities((string) $article['user']['name']); ?>">
					<?php if(($article['user']['vip'] > 0)): ?>
					<i class="iconfont icon-renzheng" title="认证信息"></i>
					<?php endif; ?>
					</a>
					<div class="fly-detail-user">
					<a href="<?php echo url("user/home",["id"=>$article['user']['id']])->domain(true); ?>" class="fly-link"><cite><?php echo !empty($article['user']['nickname']) ? htmlentities((string) $article['user']['nickname']) : htmlentities((string) $article['user']['name']); ?></cite></a>
					</div>
					<div class="detail-hits">
					<span class="post-time" data="<?php echo htmlentities((string) $article['update_time']); ?>" style="padding-top: 5px;"><?php echo htmlentities((string) $article['update_time']); ?></span>
					<?php echo hook('ipShow',$article['user']['city']); ?>
					</div>
				</div>
				<hr>

				
				<?php echo hook('taoplayerdiv'); ?>
				<article class="detail-body photos" id="content"><?php echo $article['content']; ?></article>
				
				<?php if(!(empty($article['upzip']) || (($article['upzip'] instanceof \think\Collection || $article['upzip'] instanceof \think\Paginator ) && $article['upzip']->isEmpty()))): if((session('?user_name'))): ?>
					<button type="button" class="layui-btn layui-btn-xs" id="zip-download"><i class="layui-icon layui-icon-download-circle"></i><?php echo lang('download files'); ?>: <?php echo htmlentities((string) $article['downloads']); ?>次</button>
				<?php endif; ?>
				<?php endif; if(empty($passJieMi) || (($passJieMi instanceof \think\Collection || $passJieMi instanceof \think\Paginator ) && $passJieMi->isEmpty())): if(($article['read_type'] == 1)): ?>
				<div id="jiemi" style="text-align:center">
					<button type="button" class="layui-btn layui-btn-primary"><i class="layui-icon layui-icon-password" style="font-size: 30px; color: #FF5722;"></i> 阅读请解密 </button>
				</div>
				<?php endif; ?>
				<?php endif; if(!(empty($tags) || (($tags instanceof \think\Collection || $tags instanceof \think\Paginator ) && $tags->isEmpty()))): ?>
				<div style="margin-top: 15px">标签
					<?php if(is_array($tags) || $tags instanceof \think\Collection || $tags instanceof \think\Paginator): $i = 0; $__LIST__ = $tags;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
					<a href="<?php echo htmlentities((string) $vo['url']); ?>"><span class="layui-btn layui-btn-xs layui-btn-normal  layui-btn-radius"><?php echo htmlentities((string) $vo['name']); ?></span></a>
					<?php endforeach; endif; else: echo "" ;endif; ?>
				</div>
				<?php endif; ?>
				
				<div style="margin: 20px 0px 15px 0px; color: rgb(130, 125, 125);">
					<p style="line-height:200%; "><?php echo $sysInfo['state']; ?></p>
				</div>
				
				<div style="margin-top: 20px;">本文链接：<a href="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo htmlentities((string) app('request')->url()); ?>"><?php echo htmlentities((string) app('request')->domain()); ?><?php echo htmlentities((string) app('request')->url()); ?></a></div>
				<div class="detail-zan">
					<span class="jieda-zan" type="zan" id="article-zan">
						点赞 <i class="iconfont icon-zan"></i> <em><?php echo count($userZanList); ?></em>
					</span>
					<!-- 点赞列表 -->
					<?php if(is_array($userZanList) || $userZanList instanceof \think\Collection || $userZanList instanceof \think\Paginator): $i = 0; $__LIST__ = $userZanList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
					<span><img src="<?php echo htmlentities((string) $vo['userImg']); ?>"></span>
					<?php endforeach; endif; else: echo "" ;endif; ?>
				</div>
			</section>

			
			<section class="fly-panel">
				<?php if(session('?user_id') AND ( config('taoler.config.is_reply') == 1 ) AND ( $article['is_reply'] == 1 )): ?>
				<div class="layui-form layui-form-pane">
					<div class="layui-form-item layui-form-text">
						<a name="comment"></a>
						<div class="layui-input-block">
							<textarea id="L_content" name="content" required lay-verify="required" placeholder="<?php echo lang('please input the content'); ?>"  class="layui-textarea taonyeditor"></textarea>
						</div>
					</div>
					<div class="layui-form-item">
						<input type="hidden" name="article_id" value="<?php echo htmlentities((string) $article['id']); ?>">
						<button class="layui-btn" lay-filter="user-comment" lay-submit><?php echo lang('submit comments'); ?></button>
					</div>
				</div>
				<?php endif; ?>
			</section>

			
			<div class="fly-panel detail-box" id="flyReply">
			<span style="font-size:18px;">评论 <?php echo htmlentities((string) $article['comments_count']); ?></span>
			<ul class="jieda" id="jieda">
				<?php  if(is_array($comments['data']) || $comments['data'] instanceof \think\Collection || $comments['data'] instanceof \think\Paginator): $i = 0; $__LIST__ = $comments['data'];if( count($__LIST__)==0 ) : echo "还没有内容" ;else: foreach($__LIST__ as $key=>$comment): $mod = ($i % 2 );++$i;?>
					<li data-id="<?php echo htmlentities((string) $comment['id']); ?>" class="jieda-daan">
						<div class="detail-about detail-about-reply">
							<a class="fly-avatar" href="<?php echo url("user/home",["id"=>$comment['user_id']])->domain(true); ?>"><img src="<?php echo htmlentities((string) $comment['user']['user_img']); ?>" alt="<?php echo !empty($comment['user']['nickname']) ? htmlentities((string) $comment['user']['nickname']) : htmlentities((string) $comment['user']['name']); ?>"></a>
							<div class="fly-detail-user">
								<a href="<?php echo url("user/home",["id"=>$comment['user_id']])->domain(true); ?>" class="fly-link"><cite><?php echo !empty($comment['user']['nickname']) ? htmlentities((string) $comment['user']['nickname']) : htmlentities((string) $comment['user']['name']); ?></cite></a>
								<?php if($article['user_id'] == $comment['user_id']): ?>
								<span>(<?php echo lang('poster'); ?>)</span>
								<?php endif; ?>
								<span><?php echo $comment['user']['sign']; ?></span>
							</div>
							<div class="detail-hits">
								<span class="post-time"><?php echo htmlentities((string) $comment['create_time']); ?></span><?php echo hook('ipShow',$comment['user']['city']); ?></span>
							</div>

							
							<?php if(($article['read_type'] == 0 || (($article['read_type'] == 1) && $passJieMi))): ?>
								<div class="detail-body jieda-body photos"><?php echo $comment['content']; ?></div>
									<div class="jieda-reply">
									<?php if($comment['delete_time'] == '0'): ?>
									<span class="jieda-zan <?php if(($comment['zan'] != 0)): ?>zanok<?php endif; ?>" type="zan">
										<i class="iconfont icon-zan"></i><em><?php echo htmlentities((string) $comment['zan']); ?></em>
									</span>
									<span type="reply" data-pid="<?php echo htmlentities((string) $comment['id']); ?>" data-tid="<?php echo htmlentities((string) $comment['user_id']); ?>"><i class="iconfont icon-svgmoban53"></i><?php echo lang('reply'); ?></span>
									
									<div class="jieda-admin">
										<?php if(((session('user_id') == $comment['user_id']) && (getLimtTime($comment['create_time']) < 2)) OR ($user['auth']  ?? '')): ?>
										<span type="edit" class="comment-edit" data-id="<?php echo htmlentities((string) $comment['id']); ?>"><?php echo lang('edit'); ?></span>
										<span type="del" class="comment-del" data-id="<?php echo htmlentities((string) $comment['id']); ?>"><?php echo lang('delete'); ?></span>
										<?php endif; ?>
									</div>
									<?php endif; if(!(empty($comment['children']) || (($comment['children'] instanceof \think\Collection || $comment['children'] instanceof \think\Paginator ) && $comment['children']->isEmpty()))): if(is_array($comment['children']) || $comment['children'] instanceof \think\Collection || $comment['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $comment['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
											<div class="layui-clear" style="margin:10px 0; padding: 10px; border: 1px solid #f0f0f0; background: #f6f6f6">
												<a style="display: inline-block; width: 50px;">
													<img src="<?php echo htmlentities((string) $vo['user']['user_img']); ?>" style="width: 30px; height: 30px; border-radius: 15px; object-fit: cover">
												</a>
												<div style="float: left;width: calc(100% - 50px);">
												<div><?php echo htmlentities((string) $vo['user']['name']); ?> <?php echo htmlentities((string) date('Y-m-d H:i',!is_numeric($vo['create_time'])? strtotime($vo['create_time']) : $vo['create_time'])); ?></div>
												<div class="detail-body jieda-body photos"><?php echo $vo['content']; ?></div>
												<div class="jieda-reply">
													<?php if($vo['delete_time'] == '0'): ?>
														<span class="jieda-zan <?php if(($vo['zan'] != 0)): ?>zanok<?php endif; ?>" type="zan">
														<i class="iconfont icon-zan"></i><em><?php echo htmlentities((string) $vo['zan']); ?></em>
														</span>
														<span type="reply" data-pid="<?php echo htmlentities((string) $vo['id']); ?>" data-tid="<?php echo htmlentities((string) $vo['user']['id']); ?>"><i class="iconfont icon-svgmoban53"></i><?php echo lang('reply'); ?></span>
														
														<div class="jieda-admin">
															<?php if(((session('user_id') == $vo['user']['id']) && (getLimtTime($vo['create_time']) < 2)) OR ($user['auth']  ?? '')): ?>
															<span type="edit" class="comment-edit" data-id="<?php echo htmlentities((string) $vo['id']); ?>"><?php echo lang('edit'); ?></span>
															<span type="del" class="comment-del" data-id="<?php echo htmlentities((string) $vo['id']); ?>" ><?php echo lang('delete'); ?></span>
															<?php endif; ?>
														</div>
													<?php endif; ?>
												</div>
												</div>
											</div>

											
											<?php if(!(empty($vo['children']) || (($vo['children'] instanceof \think\Collection || $vo['children'] instanceof \think\Paginator ) && $vo['children']->isEmpty()))): if(is_array($vo['children']) || $vo['children'] instanceof \think\Collection || $vo['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$voo): $mod = ($i % 2 );++$i;?>
												<div class="layui-clear" style="margin:10px 0; padding: 10px; border: 1px solid #f0f0f0;">
													<a style="display: inline-block; width: 50px;">
														<img src="<?php echo htmlentities((string) $voo['user']['user_img']); ?>" style="width: 30px; height: 30px; object-fit: cover; border-radius: 15px;">
													</a>
													<div style="float: left;width: calc(100% - 50px);">
													<div><?php echo htmlentities((string) $voo['user']['name']); ?> 回复 <?php echo htmlentities((string) $voo['touser']); ?> <?php echo htmlentities((string) date('Y-m-d H:i',!is_numeric($voo['create_time'])? strtotime($voo['create_time']) : $voo['create_time'])); ?></div>
													<div class="detail-body jieda-body photos"><?php echo $voo['content']; ?></div>
													<div class="jieda-reply">
														<?php if($voo['delete_time'] == '0'): ?>
															<span class="jieda-zan <?php if(($voo['zan'] != 0)): ?>zanok<?php endif; ?>" type="zan">
															<i class="iconfont icon-zan"></i><em><?php echo htmlentities((string) $voo['zan']); ?></em>
															</span>
															<span type="reply" data-pid="<?php echo htmlentities((string) $vo['id']); ?>" data-tid="<?php echo htmlentities((string) $voo['user']['id']); ?>"><i class="iconfont icon-svgmoban53"></i><?php echo lang('reply'); ?></span>
															
															<div class="jieda-admin">
															<?php if(((session('user_id') == $voo['user']['id']) && (getLimtTime($voo['create_time']) < 2)) OR ($user['auth']  ?? '')): ?>
															<span type="edit" class="comment-edit" data-id="<?php echo htmlentities((string) $voo['id']); ?>"><?php echo lang('edit'); ?></span>
															<span type="del" class="comment-del" data-id="<?php echo htmlentities((string) $voo['id']); ?>"><?php echo lang('delete'); ?></span>
															<?php endif; ?>
															</div>
														<?php endif; ?>
													</div>
													</div>
												</div>
												<?php endforeach; endif; else: echo "" ;endif; ?>
											<?php endif; ?>
										<?php endforeach; endif; else: echo "" ;endif; ?>
									<?php endif; ?>
								</div>
							<?php else: ?>
								<div class="detail-body jieda-body photos">
									<i class="layui-icon layui-icon-password" style="font-size: 24px; color: #FF5722;"></i>
									评论解密后查看
								</div>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; endif; else: echo "还没有内容" ;endif; ?>
			</ul>
			<div style="text-align: center" id="pages"></div>
			</div>
		</div>

	
	<div class="layui-col-md4">
		<div class="fly-panel">
			<div class="fly-panel-main wenda-user">
				<div class="user-img">
					<a href="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo url('user/home',['id'=>$article['user']['id']]); ?>">
					<img class="" src="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo htmlentities((string) $article['user']['user_img']); ?>" alt="<?php echo htmlentities((string) $article['user']['name']); ?>" />
					</a>
				</div>
				<div class="questions">
					<span class="layui-badge layui-bg-green">回答 <?php echo htmlentities((string) $article['user']['comments_count']); ?></span>
					<span class="layui-badge layui-bg-green">发表 <?php echo htmlentities((string) $article['user']['article_count']); ?></span>
				</div>
			</div>
		</div>
		
	</div>

	<!-- crud管理模块 -->
	
<?php if((session('?user_name'))): ?>
  <div class="detail-assist">
    <div class="fly-admin-box" data-id="<?php echo htmlentities((string) $article['id']); ?>">
      <?php if(($user['auth'] ?? '')): if(($article['is_top'] == 0)): ?>
        <span class="layui-btn layui-btn-xs jie-admin" type="set" field="top" rank="1" style="background-color:#ccc;" title="置顶">顶</span> 
        <?php else: ?>
          <span class="layui-btn layui-btn-xs jie-admin" type="set" field="top" rank="0" title="取消置顶">顶</span>
        <?php endif; if(($article['is_hot'] == 0)): ?>
          <span class="layui-btn layui-btn-xs jie-admin" type="set" field="hot" rank="1" style="background-color:#ccc;" title="加精">精</span> 
        <?php else: ?>
          <span class="layui-btn layui-btn-xs jie-admin" type="set" field="hot" rank="0" title="取消加精">精</span>
        <?php endif; if(($article['is_reply'] == 1)): ?>
        <span class="layui-btn layui-btn-xs jie-admin" type="set" field="reply" rank="0" title="禁评">评</span>
        <?php else: ?>
        <span class="layui-btn layui-btn-xs jie-admin" type="set" field="reply" rank="1" style="background-color:#ccc;" title="可评">评</span>
        <?php endif; ?>
        <span class="layui-btn layui-btn-xs jie-admin" type="del" title="删除"><i class="layui-icon layui-icon-delete"></i></span>
        <?php endif; if((session('user_name')==$article['user']['name']  || ($user['auth']  ?? ''))): ?>
        <a  href="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo url('article/edit',['id'=>$article['id']]); ?>" title="编辑"><span class="layui-btn layui-btn-xs"><i class="layui-icon layui-icon-edit"></i></span></a>
        <?php endif; ?>
    </div>
  </div>
<?php endif; ?>
	</div>
  <!--底部栏-->
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
	var collectionFind = "<?php echo url('collection/find'); ?>",
		collection = "<?php echo url('collection/'); ?>",
		articleJieset = "<?php echo url('Article/jieset'); ?>",
		articleDelete = "<?php echo url('Article/delete'); ?>",
		commentJiedaZan = "<?php echo url('Comment/jiedaZan'); ?>",
		commentJiedaCai = "<?php echo url('Comment/jiedaCai'); ?>",
		commentGetDa = "<?php echo url('Comment/getDa'); ?>",
		commentUpdateDa = "<?php echo url('Comment/updateDa'); ?>",
		commentJiedaDelete = "<?php echo url('Comment/jiedaDelete'); ?>",
		langCollection = "<?php echo lang('collection'); ?>",
		langCancelCollection = "<?php echo lang('cancel collection'); ?>";

	layui.use(['laypage'], function(){
		var $ = layui.jquery
		,form = layui.form
		,uid = layui.cache.user.uid
		,laypage = layui.laypage;

		let AID = "<?php echo htmlentities((string) $article['id']); ?>";
		let LOGIN_URL = "<?php echo url('login/index'); ?>";

		//文章点赞
		$("#article-zan").on('click',function(){
			//判断登陆
			if(uid == -1){
				layer.msg('请登录再点赞', {icon: 6}, function(){location.href = LOGIN_URL})
				return false;
			}
			$.post("<?php echo url('article/userZanArticle'); ?>",{article_id:AID, type:1},function(data){
				if(data.code === 0){
					layer.msg(data.msg,{icon:6,time:2000},function () { location.reload(true) });
				} else {
					layer.msg(data.msg,{icon:6,adim:6});
				}
			})
		});

		// 评论
		form.on('submit(user-comment)',function (data){
			comment(data.field);
		});

		// 提交评论
		form.on('submit(submit-user-comment)', function(data){
			comment(data.field);
			return false;
		})

		// 回复评论用户
		$("span[type='reply']").on('click',function (){
			var pid = $(this).attr('data-pid');
			var tid = $(this).data('tid');
			var html =
					'<form class="layui-form user-comment" style="margin-left:50px;">' +
					'<div>' +
					'<input type="hidden" name="article_id" value="<?php echo htmlentities((string) $article['id']); ?>">' +
					'<input name="pid" value="'+ pid +'" class="layui-hide">' +
					'<input name="to_user_id" value="'+ tid +'" class="layui-hide">' +
					'<textarea name="content" required lay-verify="required"  class="layui-textarea taonyeditor" style="height: 100px; right: 5px; margin: 10px 5px;"></textarea>' +
					'<button  type="submit" class="layui-btn" lay-submit lay-filter="submit-user-comment">提交</button>' +
					'</div>' +
					'</form>';
			var forms = $(this).nextAll('form');
			if(forms.length == 0) {
				// 移除其它评论块
				$('.user-comment').remove();
				//动态添加评论块
				$(this).next().after(html);
			}
		})

		// 编辑评论
		$(".comment-edit").on('click', function () {
			var id = $(this).data('id');
			layer.open({
				type: 2,
				title: '修改',
				shade: 0.1,
				area: ['600px', '500px'],
				content: "<?php echo url('comment/edit'); ?>" + '?id=' + id
			});
		});

		// 删除评论
		$(".comment-del").on('click', function () {
			var id = $(this).data('id');
			layer.confirm('需要删除吗？', {icon: 3}, function (){
				$.post("<?php echo url('comment/jiedaDelete'); ?>", {id: id}, function (res) {
					if (res.status === 0) {
						toast.success({title: "成功消息", message: res.msg});
						location.reload(true);
					} else {
						toast.error({title: "失败消息", message: res.msg});
					}
				})
			})
		});

		// 评论接口
		function comment(data){
			if (uid == -1) {
				layer.msg('请先登陆',{icon:5,time:2000},function(){location.href = LOGIN_URL});
				return false;
			}
			var index = layer.load(1);
			$.ajax({
				type: "post",
				url: "<?php echo url('article/comment'); ?>",
				data: data,
				dataType: "json",
				success:function (res) {
				layer.close(index);
					if (res.code === 0) {
						layer.msg(res.msg,{icon:6,time:2000},function () {location.reload(true)});
					} else {
						layer.open({title:'评论失败',content:res.msg,icon:5,anim:6});
					}
				}
			});
		}

		// 解密内容
		$("#jiemi").click(function (){
			//判断登陆
			if(uid == -1){
				layer.msg('请先登录再查看', {icon: 6}, function(){location.href = LOGIN_URL})
				return false;
			}
			layer.prompt(function(value, index, elem){
				$.post("<?php echo url('article/jiemi'); ?>",{id: AID, art_pass:value},function (res){
					layer.close(index);
					if(res.code === 0){
						layer.msg(res.msg,{icon:6,time:2000},function () {
							parent.location.reload(); //刷新父页面，注意一定要在关闭当前iframe层之前执行刷新
						});
					} else {
						layer.msg(res.msg,{icon:5,adim:6});
					}
				});
			});
		});

		// 下载附件
		$('#zip-download').click(function (){
			var id = "<?php echo htmlentities((string) $article['id']); ?>";
			$.ajax({
				type:"post",
				url:"<?php echo url('article/download'); ?>",
				data:{id:id},
				success:function (data) {
				location.href = "<?php echo url('article/download',['id'=>$article['id']]); ?>"; 
				}
			});
		});

		// 评论分页
		laypage.render({
			elem: "pages", //注意，这里的 test1 是 ID，不用加 # 号
			count: "<?php echo htmlentities((string) $comments['total']); ?>", //数据总数，从服务端得到
			limit: 10,
			curr: "<?php echo htmlentities((string) $page); ?>",
			//获取起始页
			jump: function (obj, first) {
				var page = obj.curr;
				var limit = obj.limit;
				var  url = "<?php echo url('article_detail',['id' => $article['id'] ,'ename' =>$article['cate']['ename']]); ?>";
				//首次不执行
				if (!first) {
					$.post("<?php echo url('article/detail'); ?>", { id: AID, page: page }, function () {
						location.href = url + '?page=' + page + '#comment';
					});
				}
			},
		});
		
	});
</script>

<!-- 插件hook位 -->
<?php echo hook('addonhook_detail_js'); ?>
<!-- 插件hook位 -->
<?php echo hook('addonhook_detail'); ?>



<div id="outerdiv" style="position: fixed; top: 0; left: 0; background: rgba(0, 0, 0, 0.7); z-index: 2; width: 100%; height: 100%; display: none">
  <div id="innerdiv" style="position: absolute">
    <img id="bigimg" style="border: 5px solid #fff" src="" />
  </div>
</div>
<script type="text/javascript">
  $(function () {
    $(".photos").on("click", "img", function () {
      var _this = $(this);
      imgShow("#outerdiv", "#innerdiv", "#bigimg", _this);
    });
  });
  function imgShow(outerdiv, innerdiv, bigimg, _this) {
    var src = _this.attr("src");
    $(bigimg).attr("src", src);

    $("<img/>")
      .attr("src", src)
      .on("load", function () {
        var windowW = $(window).width();
        var windowH = $(window).height();
        var realWidth = this.width;
        var realHeight = this.height;
        var imgWidth, imgHeight;
        var scale = 0.8;
        if (realHeight > windowH * scale) {
          //判断图片高度
          imgHeight = windowH * scale;
          imgWidth = (imgHeight / realHeight) * realWidth;
          if (imgWidth > windowW * scale) {
            //如宽度扔大于窗口宽度
            imgWidth = windowW * scale;
          }
        } else if (realWidth > windowW * scale) {
          imgWidth = windowW * scale;
          imgHeight = (imgWidth / realWidth) * realHeight;
        } else {
          //如果图片真实高度和宽度都符合要求，高宽不变
          imgWidth = realWidth;
          imgHeight = realHeight;
        }
        $(bigimg).css("width", imgWidth);
        var w = (windowW - imgWidth) / 2;
        var h = (windowH - imgHeight) / 2;
        $(innerdiv).css({ top: h, left: w });
        $(outerdiv).fadeIn("fast");
      });
    $(outerdiv).click(function () {
      //再次点击淡出消失弹出层
      $(this).fadeOut("fast");
    });
  }
</script>



</body>
</html>

