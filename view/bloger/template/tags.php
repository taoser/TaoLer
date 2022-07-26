{* Template Name:标签页模块 *}
{template:header}
    <main class="py-2 py-md-2 pb-3">
      <div class="container">
        <div class="row">
          <div class="col-lg-8">
            <div id="main" class="list-home list-grid list-grid-padding">
			{foreach $articles as $article}
              <div class="list-item block card-plain">
                {if rongkeji_Thumb($article) != ''}
                <div class="media media-3x2 col-4 col-md-4 d-none d-md-block">
                  <a class="media-content" href="{$article.Url}" title="{$article.Title}" style="background-image: url('{rongkeji_Thumb($article)}');"></a>
                </div>
                {/if}
                <div class="{if rongkeji_Thumb($article) != ''}list-content{/if}">
                  <div class="list-body">
                    <a href="{$article.Url}" title="{$article.Title}" class="list-title text-lg h-1x">
                      {$article.Title}
                    </a>
                    <div class="list-desc d-block d-md-block text-sm text-secondary my-3">
                      <div class="h-3x">
                       {$article.Intro}
                      </div>
                    </div>
                  </div>
                  <div class="list-footer">
                    <div class="d-flex flex-fill align-items-center text-muted text-xs">
                      <div class="d-inline-block">
                        <a href="{$article.Category.Url}">{$article.Category.Name}</a>
                      </div>
                      <div class="flex-fill"></div>
                      <div class="mx-1">{rongkeji_TimeAgo($article.Time())}</div>
                    </div>
                  </div>
                </div>
              </div>
			{/foreach}
			</div>
			<nav class="navigation pagination">
			{template:pagebar}
			</nav>
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