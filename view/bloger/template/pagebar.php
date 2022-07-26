{* Template Name:分页 *}
{if $zbp->Config('rongkeji')->fanyeanniu <='1'}
<div class="nav-links" style="padding-bottom: 10px;">
{else}
<div class="nav-links" style="display:none;">
{/if}
					<ul class="page-navigator">
{if $pagebar}
{foreach $pagebar.buttons as $k=>$v}
{if $pagebar.PageAll>1}
{if $pagebar.PageNow==$k}
						<li class='current'><span aria-current="page">{$k}</span></li>
{elseif $k=='‹'}
						<li class="prev"><a aria-label="prev" href="{$v}" title="上一页">上一页</a></li>
{elseif $k=='›'}
						<li class="next"><a aria-label="next" href="{$v}" title="下一页">下一页</a></li>
{elseif $k=='‹‹'}
{if $pagebar.PageNow!=1}
						<a href="{$v}" title="第1页"></a>
{/if}
{elseif $k=='››'}
{if $pagebar.PageNow!=$pagebar.PageLast}
						<a href="{$v}" title="最后一页"></a>
{/if}
{else}
{if rongkeji_is_mobile()}
{else}
						<li class='current'><a href="{$v}" title="第{$k}页">{$k}</a></li>
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