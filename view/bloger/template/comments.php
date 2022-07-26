{* Template Name:评论模块 *}
{if $socialcomment}
{$socialcomment}
{else}
<label id="AjaxCommentBegin"></label>
<!--评论框--> 
{template:commentpost} 
<!--评论输出-->
{if $article.CommNums==0}
<div class="text-center py-5">
	<div class="text-center">
		<svg t="1645065690246" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6326" width="100" height="100"><path d="M324.83 800.07c-3.48 0-7.02-0.56-10.5-1.74C162.39 746.74 68 638.78 68 516.59c0-18.21 14.68-32.97 32.79-32.97s32.79 14.76 32.79 32.97c0 93.01 77.3 177.03 201.73 219.28 17.16 5.82 26.37 24.53 20.57 41.78-4.6 13.75-17.37 22.42-31.05 22.42zM390.78 331.07c-9.75 0-19.39-4.35-25.86-12.67L261.69 185.65l-80.9 110.47c-10.74 14.66-31.26 17.8-45.85 7.01-14.58-10.79-17.71-31.43-6.97-46.09l106.52-145.47c6.08-8.3 15.68-13.27 25.94-13.42 10.27-0.1 19.99 4.53 26.31 12.66L416.59 277.8c11.15 14.34 8.63 35.06-5.64 46.27a32.48 32.48 0 0 1-20.17 7z" p-id="6327" fill="#b1b1c1"></path><path d="M100.76 553.95c-16.8 0-31.11-12.9-32.63-30.05-8.79-99.38 11.62-189.12 60.67-266.74 9.71-15.36 29.98-19.91 45.27-10.15 15.29 9.76 19.81 30.13 10.1 45.5-41.71 66-58.29 139.77-50.7 225.55 1.6 18.14-11.72 34.14-29.76 35.76-0.99 0.09-1.98 0.13-2.95 0.13zM612.29 821.41c-15.85 0-29.79-11.57-32.35-27.8-2.84-17.98 9.36-34.87 27.25-37.73 166.75-26.61 283.22-125.02 283.22-239.29 0-18.21 14.68-32.97 32.79-32.97s32.79 14.76 32.79 32.97c0 147.42-139.21 272.6-338.52 304.41-1.74 0.28-3.47 0.41-5.18 0.41zM633.22 331.07c-7.06 0-14.18-2.28-20.18-6.99-14.27-11.21-16.79-31.93-5.64-46.27l129.85-166.99c6.32-8.12 16.14-12.68 26.31-12.66 10.26 0.15 19.86 5.11 25.94 13.42l106.52 145.47c10.74 14.66 7.62 35.3-6.97 46.09-14.59 10.79-35.12 7.66-45.85-7.01l-80.89-110.47-103.23 132.75c-6.47 8.31-16.11 12.66-25.86 12.66z" p-id="6328" fill="#b1b1c1"></path><path d="M923.24 553.95c-0.97 0-1.96-0.04-2.95-0.13-18.04-1.61-31.36-17.62-29.76-35.76 7.59-85.77-8.99-159.55-50.7-225.55-9.71-15.37-5.19-35.74 10.1-45.5 15.29-9.75 35.55-5.22 45.27 10.15 49.05 77.62 69.46 167.36 60.67 266.74-1.52 17.14-15.83 30.05-32.63 30.05zM630.05 330.15H393.93c-18.11 0-32.79-14.76-32.79-32.97s14.68-32.97 32.79-32.97h236.12c18.11 0 32.79 14.76 32.79 32.97 0.01 18.21-14.67 32.97-32.79 32.97zM524.98 927.56a32.63 32.63 0 0 1-23.19-9.65l-96.2-96.7c-12.81-12.88-12.81-33.75 0-46.62 12.81-12.87 33.57-12.87 46.38 0l96.2 96.7c12.81 12.88 12.81 33.75 0 46.62a32.612 32.612 0 0 1-23.19 9.65z" p-id="6329" fill="#b1b1c1"></path><path d="M524.97 927.56c-7.4 0-14.85-2.51-20.98-7.64-13.91-11.66-15.8-32.44-4.2-46.43L587.24 768c11.6-13.98 32.28-15.86 46.19-4.22 13.91 11.66 15.79 32.44 4.2 46.43L550.17 915.7c-6.48 7.82-15.81 11.86-25.2 11.86z" p-id="6330" fill="#b1b1c1"></path><path d="M321.78 503.77a45.91 46.15 0 1 0 91.82 0 45.91 46.15 0 1 0-91.82 0Z" p-id="6331" fill="#b1b1c1"></path><path d="M601.63 499.37a45.91 46.15 0 1 0 91.82 0 45.91 46.15 0 1 0-91.82 0Z" p-id="6332" fill="#b1b1c1"></path></svg>
	</div>
	<div class="text-muted text-sm mt-3">暂无评论</div>
</div>
{else}
{foreach $comments as $key => $comment}
<div class="underline">
	{template:comment}
</div>
{/foreach}
{if $article.CommNums>0 && $pagebar}
<nav class="navigation pagination">
	<div class="nav-links" style="padding-bottom: 10px;padding-top: 20px;">
		<ul class="page-navigator">
		{if $pagebar}
		{foreach $pagebar.buttons as $k=>$v}
		{if $pagebar.PageAll>1}
		{if $pagebar.PageNow==$k}
		<li class='current'><span>{$k}</span></li>
		{elseif $k=='‹'}
		<li class="prev"><a href="{$v}document.getElementById('divCommentPost').scrollIntoView()" title="上一页">上一页</a></li>
		{elseif $k=='›'}
		<li class="next"><a href="{$v}document.getElementById('divCommentPost').scrollIntoView()" title="下一页">下一页</a></li>
		{elseif $k=='‹‹'}
		{if $pagebar.PageNow!=1}
		<a href="{$v}document.getElementById('divCommentPost').scrollIntoView()" title="第1页"></a>
		{/if}
		{elseif $k=='››'}
		{if $pagebar.PageNow!=$pagebar.PageLast}
		<a href="{$v}document.getElementById('divCommentPost').scrollIntoView()" title="最后一页"></a>
		{/if}
		{else}
		{if rongkeji_is_mobile()}
		{else}
		<li class='current'><a href="{$v}document.getElementById('divCommentPost').scrollIntoView()" title="第{$k}页">{$k}</a></li>
		{/if}
		{/if}
		{/foreach}
		{/if}
		{/if}
		{if rongkeji_is_mobile()}
		<li id="loadmore"><a href="{$host}" style="margin-right: 0px;">点击返回首页</a></li>
		{else}
		<li id="loadmore"><a style="margin-right: 0px;">当前第 {$pagebar.PageNow} 页</a></li>
		{/if}
		</ul>
	</div>
</nav>
{/if}
{/if} 
<!--评论翻页条输出-->
<label id="AjaxCommentEnd"></label>
{/if} 