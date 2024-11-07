<?php /*a:1:{s:48:"E:\github\TaoLer\addons\ads\view\ads_slider.html";i:1686394417;}*/ ?>
<div class="fly-panel"><div class="layui-row"><div class="layui-carousel fly-topline" id="TAOLER-SLIDER"><div carousel-item=""><?php $slider = new \addons\ads\model\Slider();$__slider__ = request()->isMobile() ? $slider->getSliderList(12) : $slider->getSliderList(1); if(is_array($__slider__) || $__slider__ instanceof \think\Collection || $__slider__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__slider__;if( count($__LIST__)==0 ) : echo "还没有内容" ;else: foreach($__LIST__ as $key=>$slider): $mod = ($i % 2 );++$i;?><div time-limit=""><a href="<?php echo htmlentities((string) $slider['slid_href']); ?>" target="_blank" rel="nofollow"><img src="<?php echo htmlentities((string) app('request')->domain()); ?><?php echo htmlentities((string) $slider['slid_img']); ?>" alt="<?php echo htmlentities((string) $slider['slid_name']); ?>" /></a></div><?php endforeach; endif; else: echo "还没有内容" ;endif; ?></div></div></div></div><script>layui.extend({
  ads: '{/}/static/addons/ads/js/ads'
}).use(['ads'],function (){
  //var ads = layui.ads;
})
</script>