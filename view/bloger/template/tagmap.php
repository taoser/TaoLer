{* Template Name:独立标签页 *}
{template:header}
<main class="py-2 py-md-2 pb-3 mb-5 h-v-75">
<div class="container">
	<div class="post">
		<div class="post-header mb-4">
			<h1 class="h3 m-0">{$article.Title}</h1>
		</div> 
	</div> 
	<div class="list-links-item">
			<div class="row-sm list-grouped my-n2">
				{rongkeji_getTags('0')}
			</div>
	</div>
</div>
</main>
{template:footer}
