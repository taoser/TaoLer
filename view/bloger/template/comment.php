{* Template Name:评论列表模块 *}
<ul class="comment-list" id="cmt-{$comment.ID}">
	<li class="comment-body comment-parent comment-odd">
		<div class="comment-author text-muted text-xs">
			<span>
				{if $comment.Author.Email==''}
				<img class="avatar" src="https://q4.qlogo.cn/headimg_dl?dst_uin=10000&spec=100" alt="{$comment.Author.StaticName}" width="48" height="48">
				{else}
				<img class="avatar" src="https://q4.qlogo.cn/headimg_dl?dst_uin={$comment.Author.Email}&spec=100" alt="{$comment.Author.StaticName}" width="48" height="48">
				{/if}
			</span>
				<span class="badge badge-primary" style="color: #fff;">
					{$comment.Author.StaticName}
				</span>
				{if $comment.Author.Level < 2}<i class="comment-vip text-lg iconfont icon-vip-line"></i>{/if}{rongkeji_TimeAgo($comment.Time())}{if $comment.Author.Level > 2}{if ((int)$zbp->Config('iparealee')->Getipon) && ($zbp->CheckPlugin('iparealee'))}，来自 {get_ipaddress($comment.IP)}{/if}{/if}
		</div>
	</li>
<li class="comment-content">
{$comment.Content} <a href="#comment" onclick="zbp.comment.reply('{$comment.ID}')" class="text-muted text-xs" style="float: right;">@回复</a>
</li>
</ul>
{foreach $comment.Comments as $comment}
{template:comment}
{/foreach}