{* Template Name:评论提交模块 *}
<div id="divCommentPost" class="comment-respond">
  <form id="commentform" target="_self" method="post" action="{$article.CommentPostUrl}" class="comment-form">
    <input type="hidden" name="inpId" id="inpId" value="{$article.ID}">
    <input type="hidden" name="inpRevID" id="inpRevID" value="0">
    {if $user.ID>0}
    <input type="hidden" name="inpName" id="inpName" value="{$user.Name}">
    <input type="hidden" name="inpEmail" id="inpEmail" value="{$user.Email}">
    <input type="hidden" name="inpHomePage" id="inpHomePage" value="{$user.HomePage}">
    {else}
	
    {if $option['ZC_COMMENT_VERIFY_ENABLE']}
	<div id="comment-author-info" class="comment-form-info row row-sm">
		<div class="col">
			<div class="form-group comment-form-author">
				<input type="text" name="inpName" id="inpName" class="form-control text-sm" value="{$user.Name}" placeholder="昵称" tabindex="1" />
				<?php if ($req) echo "（必填）"; ?>
			</div>
		</div>
					
		<div class="col-12 col-md-4">
			<div class="form-group comment-form-email">
				<input type="text" name="inpEmail" id="inpEmail" class="form-control text-sm" value="{$user.Email}" placeholder="邮箱" tabindex="2" />
				<?php if ($req) echo "（必填）"; ?>
			</div>
		</div>
					
		<div class="col-12 col-md-4">
			<div class="form-group comment-form-url">
				<input type="text" name="inpVerify" id="inpVerify" value="" size="30" class="form-control text-sm" placeholder="验证码" />
				<img class="Coduimg" style="position: absolute;top: 3px;right: 12px;" src="{$article.ValidCodeUrl}" alt="验证码"onclick="javascript:this.src='{$article.ValidCodeUrl}&amp;tm='+Math.random();"> </p>
			</div>
		</div>
	</div>
    {else}
	<div id="comment-author-info" class="comment-form-info row row-sm">
		<div class="col">
			<div class="form-group comment-form-author">
				<input type="text" name="inpName" id="inpName" class="form-control text-sm" value="{$user.Name}" placeholder="昵称" tabindex="1" />
				<?php if ($req) echo "（必填）"; ?>
			</div>
		</div>
					
		<div class="col-12 col-md-4">
			<div class="form-group comment-form-email">
				<input type="text" name="inpEmail" id="inpEmail" class="form-control text-sm" value="{$user.Email}" placeholder="邮箱" tabindex="2" />
				<?php if ($req) echo "（必填）"; ?>
			</div>
		</div>
					
		<div class="col-12 col-md-4">
			<div class="form-group comment-form-url">
				<input type="text" name="inpHomePage" id="inpHomePage" class="form-control text-sm" value="{$user.HomePage}" placeholder="网址" tabindex="3" />
			</div>
		</div>
	</div>
    {/if}
	
    {/if}
		<div class="comment-textarea mb-3">
			<textarea id="txaArticle" name="txaArticle" class="form-control form-control-sm" rows="4" tabindex="4"></textarea>
		</div>
		<div class="form-submit text-right">
			<input id="submit" name="sumbit" type="submit" tabindex="5" value="提交评论" class="btn btn-dark" onclick="return zbp.comment.post()"/>
		</div>
		<h5 class="comment-reply-title underline">{if $user.ID>0}{$user.StaticName}{/if} 评论列表 <small><a rel="nofollow" id="cancel-reply" style="display:none;">#取消回复#</a></small></h5>
  </form>
</div>