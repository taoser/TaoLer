{* Template Name:文章归档模板 *}
{template:header}
<main class="py-2 py-md-2 pb-3" style="transform: none">
  <div class="container" style="transform: none">
    <div class="row" style="transform: none">
      <div class="col-lg-8">
		<div class="list-cover card">
			<div class="list-item list-overlay-content">
				<div class="media media-21x9">
					<div class="media-content" style="background-image: url('{rongkeji_Thumb($article)}')">
					<div class="overlay"></div> 
					</div> 
				</div> 
				<div class="list-content"> 
					<div class="list-body"> 
						<div class="mt-auto p-md-3"> 
							<h1 class="text-xl"> {$article.Title} </h1> 
							<div class="border-top border-white mt-2 mt-md-3 pt-2 pt-md-3">
								<div class="text-sm h-2x">博客统计：分类 {$zbp->cache->all_category_nums} 个<div class="d-inline-block mx-1 mx-md-2"><i class="text-primary">·</i></div>标签 {$zbp->cache->all_tag_nums} 个<div class="d-inline-block mx-1 mx-md-2"><i class="text-primary">·</i></div>文章 {$zbp->cache->all_article_nums} 篇<div class="d-inline-block mx-1 mx-md-2"><i class="text-primary">·</i></div>留言 {$zbp->cache->all_comment_nums} 条<div class="d-inline-block mx-1 mx-md-2"><i class="text-primary">·</i></div>浏览量：{$zbp->cache->all_view_nums}</div> 
								<div class="border-theme bg-primary"></div>
							</div>
						</div> 
					</div> 
				</div> 
			</div> 
		</div>
		  <!-- 文章归档开始 -->
                {rongkeji_GetArchives()}
		  <!-- 文章归档结束 -->
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










