{* Template Name:SEO *}
{if $type=='index'}
	<title>{$name} {$zbp->Config('rongkeji')->lianjiefu} {if $page>'1'}第{$pagebar.PageNow}页 {$zbp->Config('rongkeji')->lianjiefu} {/if} {$subname}</title>
	<meta name="Keywords" content="{$zbp->Config('rongkeji')->Keywords}" />
	<meta name="description" content="{$zbp->Config('rongkeji')->Description}" />
	<meta name="Copyright" content="{$name}">
	<meta property="og:type" content="website"/>
	<meta property="og:title" content="{$name} {$zbp->Config('rongkeji')->lianjiefu} {$subname}"/>
	<meta property="og:description" content="{$zbp->Config('rongkeji')->Description}" />
	<meta property="og:url" content="{$zbp->fullcurrenturl}"/>
	<meta property="og:site_name" content="{$name}"/>
	<meta property="og:image" content="{$zbp->Config('rongkeji')->ogimg}"/>
{elseif $type=='category'} 
	<title>{if $category.Metas.rongkeji_fltitle}{$category.Metas.rongkeji_fltitle}{else}{$category.Name}{/if} {$zbp->Config('rongkeji')->lianjiefu}{if $page>'1'} 第{$pagebar.PageNow}页 -{/if} {$name}</title>
	<meta name="Keywords" content="{if $category.Metas.rongkeji_flgjc}{$category.Metas.rongkeji_flgjc}{else}{$category.Name}{/if}" />
	<meta name="description" content="{$category.Intro}" />
	<meta property="og:type" content="website"/>
    <meta property="og:title" content="{if $category.Metas.rongkeji_fltitle}{$category.Metas.rongkeji_fltitle}{else}{$category.Name}{/if}{if $page>'1'} {$zbp->Config('rongkeji')->lianjiefu} 第{$pagebar.PageNow}页{/if}" />
    <meta property="og:site_name" content="{$name}" />
    <meta property="og:url" content="{$zbp->fullcurrenturl}">
	<meta property="og:keywords" content="{if $category.Metas.rongkeji_flgjc}{$category.Metas.rongkeji_flgjc}{else}{$category.Name}{/if}" />
	<meta property="og:description" content="{$category.Intro}" />
{elseif $type=='article'}
	<title>{$title} {$zbp->Config('rongkeji')->lianjiefu} {$name}</title>
{php}$aryTags = array();foreach($article->Tags as $key){$aryTags[] = $key->Name;}if(count($aryTags)>0){$keywords = implode(',',$aryTags);} else {$keywords = $zbp->name;}if (strlen($article->Intro)>0){$description = preg_replace('/[\r\n\s]+/', ' ', trim(SubStrUTF8(TransferHTML($article->Intro,'[nohtml]'),100)).'...');}else{$description = preg_replace('/[\r\n\s]+/', ' ', trim(SubStrUTF8(TransferHTML($article->Content,'[nohtml]'),100)).'...');}{/php}
	<meta name="keywords" content="{$keywords}"/>
	<meta name="description" content="{$description}"/>
	<meta property="bytedance:published_time" content="{$article.Time('PostTime','Y-m-d\TH:i:s+01:00')}" />
	<meta property="bytedance:updated_time" content="{$article.Time('UpdateTime','Y-m-d\TH:i:s+01:00')}" />
	<meta property="og:type" content="article" />
	<meta property="og:title" content="{$title}">
	<meta property="og:description" content="{$description}" />
	<meta property="og:url" content="{$article.Url}">
	<meta property="og:updated_time" content="{$article.Time('UpdateTime','Y-m-d\TH:i:s+08:00')}" />
	<meta property="og:site_name" content="{$name}" />
{if rongkeji_Thumb($article) != ''}
	<meta property="og:image" content="{rongkeji_Thumb($article)}"/>
{/if}
	<meta name="mobile-agent" content="format=html5;url={$article.Url}">
<!-- 谷歌JavaScript结构化 -->
	<script type="application/ld+json">{"@context": "https://schema.org","@type": "NewsArticle","headline": "{$title}",{if rongkeji_Thumb($article) != ''}"image" : "{rongkeji_Thumb($article)}",{/if}"datePublished" : "{$article.Time('PostTime','Y-m-d\TH:i:s+08:00')}","dateModified": "{$article.Time('UpdateTime','Y-m-d\TH:i:s+08:00')}","author" : {"@type" : "Person","name" : "{$article.Author.Name}","url": "{$host}"}},</script>
<!-- 百度JavaScript结构化 -->
	<script type="application/ld+json">{"@context": "https://ziyuan.baidu.com/contexts/cambrian.jsonld","@id": "{$article.Url}",{if $zbp->Config('rongkeji')->baiduappid != ''}"appid": "{$zbp->Config('rongkeji')->baiduappid}",{/if}"title": "{$title}",{if rongkeji_Thumb($article) != ''}"images": ["{rongkeji_Thumb($article)}"],{/if}"description": "{$description}","pubDate": "{$article.Time('PostTime','Y-m-d\TH:i:s')}","upDate": "{$article.Time('UpdateTime','Y-m-d\TH:i:s')}"}</script>
{elseif $type=='page'}
	<title>{if $article.Metas.rongkeji_pstitle}{$article.Metas.rongkeji_pstitle}{else}{$title}{/if} {$zbp->Config('rongkeji')->lianjiefu} {$name}</title>
	<meta name="keywords" content="{if $article.Metas.rongkeji_psguanjianci}{$article.Metas.rongkeji_psguanjianci}{else}{$title},{$name}{/if}"/>
{php}if (strlen($article->Metas->rongkeji_psmiaoshu)>0){$description = $article->Metas->rongkeji_psmiaoshu;}else{$description = preg_replace('/[\r\n\s]+/', ' ', trim(SubStrUTF8(TransferHTML($article->Content,'[nohtml]'),100)).'...');}{/php}
	<meta name="description" content="{$description}"/>
	<meta property="bytedance:published_time" content="{$article.Time('PostTime','Y-m-d\TH:i:s+01:00')}" />
	<meta property="bytedance:updated_time" content="{$article.Time('UpdateTime','Y-m-d\TH:i:s+01:00')}" />
	<meta property="og:type" content="article">
	<meta property="og:title" content="{if $article.Metas.rongkeji_pstitle}{$article.Metas.rongkeji_pstitle}{else}{$title}{/if}" />
	<meta property="og:description" content="{$description}" />
	<meta property="og:url" content="{$article.Url}"> 
	<meta property="og:updated_time" content="{$article.Time('UpdateTime','Y-m-d\TH:i:s+08:00')}" />
	<meta property="og:site_name" content="{$name}" />
	<meta property="og:image" content="{$zbp->Config('rongkeji')->ogimg}">
	<meta name="mobile-agent" content="format=html5;url={$article.Url}">
<!-- 谷歌JavaScript结构化 -->
	<script type="application/ld+json">{"@context": "https://schema.org","@type": "NewsArticle","headline": "{$title}",{if rongkeji_Thumb($article) != ''}"image" : "{rongkeji_Thumb($article)}",{/if}"datePublished" : "{$article.Time('PostTime','Y-m-d\TH:i:s+08:00')}","dateModified": "{$article.Time('UpdateTime','Y-m-d\TH:i:s+08:00')}","author" : {"@type" : "Person","name" : "{$article.Author.Name}","url": "{$host}"}},</script>
<!-- 百度JavaScript结构化 -->
	<script type="application/ld+json">{"@context": "https://ziyuan.baidu.com/contexts/cambrian.jsonld","@id": "{$article.Url}",{if $zbp->Config('rongkeji')->baiduappid != ''}"appid": "{$zbp->Config('rongkeji')->baiduappid}",{/if}"title": "{$title}",{if rongkeji_Thumb($article) != ''}"images": ["{rongkeji_Thumb($article)}"],{/if}"description": "{$description}","pubDate": "{$article.Time('PostTime','Y-m-d\TH:i:s')}","upDate": "{$article.Time('UpdateTime','Y-m-d\TH:i:s')}"}</script>
{elseif $type=='tag'}
	<title>{if $tag->Metas->rongkeji_bqtitle}{$tag.Metas.rongkeji_bqtitle}{else}{$tag.Name}{/if} 是什么？ {$zbp->Config('rongkeji')->lianjiefu}{if $page>'1'} 第{$pagebar.PageNow}页 {$zbp->Config('rongkeji')->lianjiefu}{/if} {$name}</title>
	<meta name="Keywords" content="{if $tag->Metas->rongkeji_bqgjc}{$tag.Metas.rongkeji_bqgjc}{else}{$tag.Name}{/if}" />
	<meta name="description" content="{$tag.Intro}" />
	<meta property="og:type" content="website"/>
	<meta property="og:title" content="{if $tag->Metas->rongkeji_bqtitle}{$tag.Metas.rongkeji_bqtitle}{else}{$tag.Name}{/if} 是什么？{if $page>'1'} {$zbp->Config('rongkeji')->lianjiefu} 第{$pagebar.PageNow}页{/if}">
	<meta property="og:description" content="{$tag.Intro}" />
	<meta property="og:url" content="{$zbp->fullcurrenturl}"> 
	<meta property="og:site_name" content="{$name}" />
{elseif $type=='date'}
	<title>{$title} 发布于 {$name} 的内容</title>
	<meta name="Keywords" content="{$name}" />
	<meta name="description" content="由 {$title} 发布于 {$name} 的所有内容，如有其他相关内容可返回首页查阅。" />
{else}
	<title>{$title} {$zbp->Config('rongkeji')->lianjiefu} {$name}</title>
	<meta name="Keywords" content="{$title},{$name}" />
	<meta name="description" content="{$title} {$zbp->Config('rongkeji')->lianjiefu} {$name}" />
{/if}