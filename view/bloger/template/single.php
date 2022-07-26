{* Template Name:文章单页判断模块 *}
{template:header}
<main class="py-2 py-md-2 pb-3" style="transform: none">
  <div class="container" style="transform: none">
    <div class="row" style="transform: none">
      <div class="col-lg-8" itemscope itemType="http://schema.org/BlogPosting">
{if $type=='article'}
        <div class="post card">
          <section class="card-body">
            <div class="post-header border-bottom mb-4 pb-4">
              <h1 class="h3 mb-3" itemprop="headline">{$article.Title}</h1>
              <div class="meta d-flex align-items-center text-xs text-muted">
                <div>
					<div class="d-inline-block" itemprop="author" itemscope="" itemtype="https://schema.org/Person">
						Posted by <a href="{$host}" target="_blank" class="text-muted" itemprop="url"><span itemprop="name">{$article.Author.Name}</span></a>，<a href="{$article.Category.Url}" target="_blank" class="text-muted" rel="category">{$article.Category.Name}</a>，<time class="d-inline-block" datetime="{$article.Time("PostTime","Y-m-d H:i:s")}" itemprop="datePublished">{$article.Time('Y-m-d H:i:s')}</time> 
					</div>
				</div>
				<div class="ml-auto text-sm yincang">
					<span class="mx-1">
						<small>已阅读 {$article.ViewNums} 次</small>
					</span>
				</div>
              </div>
              <div class="border-theme bg-primary"></div>
            </div>
{if $zbp->Config('rongkeji')->dao5=='1'}
			<div class="article_ad">
				{$zbp->Config('rongkeji')->wenzhangad1}
			</div>
{/if}
            <article class="post-content" itemprop="articleBody">
				{$article.Content}
			</article>
          </section>
		
          <div class="card-footer text-center mt-4">
{if rongkeji_is_mobile()}
{else}
{if $zbp->Config('rongkeji')->PostQRON=='1'}
              <a href="javascript:" style="color: #FFF !important;" data-img="{php}echo rongkeji_QR($article->Url);{/php}" data-title="手机扫一扫继续阅读" data-desc="微信或浏览器均可" class="single-popup shangzan {if $zbp->Config('rongkeji')->dashuangkg=='1'}mr-3 {/if}text-muted text-xs">
				手机浏览
              </a>
{/if}
{/if}
{if $zbp->Config('rongkeji')->dashuangkg=='1'}
              <a href="javascript:" style="color: #FFF !important;" data-img="{$zbp->Config('rongkeji')->wxpay}" data-title="赞赏支持一下站长" data-desc="微信赞赏扫码" class="single-popup shangzan text-muted text-xs">
				赞赏支持
              </a>
{if $zbp->Config('rongkeji')->dashangjilukg=='1'}
			<div class="text-center text-muted text-xs" style="margin-top: 30px;">
				"作者已被打赏 {$zbp->Config('rongkeji')->dashangkey} 次"
			</div>
{/if}
{/if}
          </div>
        </div>
		
		<div class="post card block d-flex p-4">
			<span class="d-inline-block text-muted mr-2 fanpian pb-2 mb-2">
				# 上一篇： {if $article.Prev}<a href="{$article.Prev.Url}" rel="prev">{$article.Prev.Title}</a>{else}没有更早的内容了！{/if}
			</span>
			<span class="d-inline-block text-muted mr-2 fanpian">
				# 下一篇： {if $article.Next}<a href="{$article.Next.Url}" rel="next">{$article.Next.Title}</a>{else}已经是最新的内容了！{/if}
			</span>
		</div>
		
{elseif $type=='page'}
		<div class="list-cover">
			<div class="list-item list-overlay-content">
				<div class="media media-21x9">
					<div class="media-content" style="background-image: url('{rongkeji_Thumb($article)}')">
					<div class="overlay"></div> 
					</div> 
				</div> 
				<div class="list-content"> 
					<div class="list-body"> 
						<div class="mt-auto p-md-3"> 
							<h1 class="text-xl" itemprop="headline"> {$article.Title} </h1> 
							<div class="border-top border-white mt-2 mt-md-3 pt-2 pt-md-3">
								<div class="text-sm h-2x" itemprop="description">{$description}</div> 
								<div class="border-theme bg-primary"></div>
							</div>
						</div> 	
					</div> 
				</div> 
			</div> 
		</div>
		<div style="display: none;" itemprop="author" itemscope="" itemtype="https://schema.org/Person" >
			<a href="{$host}" target="_blank" title="{$article.Author.Name}" class="text-muted" itemprop="url">
				<span itemprop="name">{$article.Author.Name}</span>
			</a>
		</div>
		<div class="card"> 
			<section class="card-body">
				<article class="post-content" itemprop="articleBody">
					{$article.Content}
				</article>
			</section> 
		</div>
{/if}

{if rongkeji_is_mobile()}{else}
{if $zbp->Config('rongkeji')->dao4=='1'}
			<section class="paid-share post-footer-share my-lg-4 d-none d-lg-block">
			{$zbp->Config('rongkeji')->adtu4}
			</section>
{/if}
{/if}

{if $type=='article'}
        <section class="list-related">
          <div class="content-related card">
            <div class="card-body">
			  <div class="mb-3">相关文章</div>
              <div class="list list-dots my-n2">
				{php} $order = array('rand()'=>''); $where = array(array('=','log_Status','0')); $array = $zbp->GetArticleList(array('*'),$where,$order,array(5),'');{/php}
				{foreach $array as $related}
                <div class="list-item py-2">
                  <a href="{$related.Url}" target="_blank" class="list-title fanpian" title="{$related.Title}" rel="bookmark">
                    {$related.Title}
                  </a>
                </div>
				{/foreach}
              </div>
            </div>
          </div>
        </section>
{elseif $type=='page'}{/if}

{if $type=='article'}
        <section class="comments">
			<div class="card">
				<div class="card-body pt-4">
					<div class="mb-3">
						文章评论 <small class="font-theme text-muted">({$article.CommNums})</small>
					</div>
					{if !$article.IsLock}
						{template:comments}
					{else}
						<div id="commentszbpmy" style="text-align: center;">
							<h5>评论已关闭！</h5>
						</div>
					{/if}
				</div>
			</div>
        </section>
{elseif $type=='page'}
{if $zbp->Config('rongkeji')->dulipinglunkg=='1'}
        <section class="comments">
			<div class="card">
				<div class="card-body pt-4">
					<div class="mb-3">
						文章评论 <small class="font-theme text-muted">({$article.CommNums})</small>
					</div>
					{if !$article.IsLock}
						{template:comments}
					{else}
						<div id="commentszbpmy" style="text-align: center;">
							<h5>评论已关闭！</h5>
						</div>
					{/if}
				</div>
			</div>
        </section>
{/if}
{/if}
        </div>
			<div class="sidebar col-lg-4 d-none d-lg-block">
				<div class="theiaStickySidebar">
					{template:sidebar}
				</div>
			</div>
		</div>
  </div>
</main>
{template:footer}
