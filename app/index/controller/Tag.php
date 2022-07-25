<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2022-07-24 15:58:51
 * @LastEditTime: 2022-07-24 21:28:52
 * @LastEditors: TaoLer
 * @Description: Tag优化版
 * @FilePath: \TaoLer\app\index\controller\Tag.php
 * Copyright (c) 2020~2022 https://www.aieok.com All rights reserved.
 */
namespace app\index\controller;

use app\common\controller\BaseController;
use app\facade\Article;
use think\cache\driver\Redis;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\common\model\Slider;
use Overtrue\Pinyin\Pinyin;

class Tag extends BaseController
{
    //
    public function index()
    {
        return View::fetch();
    }

    // 获取tag列表
    public function List()
    {
        //
        $tag = Request::param('tag');

        $artList = Article::getAllTags($tag);

        $slider = new Slider();
        //首页右栏
        $ad_comm = $slider->getSliderList(2);
        //	查询热议
        $artHot = Article::getArtHot(10);

        $assign = [
            'tag'=>$tag,
            'artList'=>$artList,
            'ad_comm'=>$ad_comm,
            'artHot'=>$artHot,
            'jspage'=>''
        ];
        View::assign($assign);
        return View::fetch('index');
        //halt($tag);
    }

    //获取文章的tag
    public function getArtTag($tag)
    {
        //
        $pinyin = new Pinyin();
        $tags = [];
		if(!is_null($tag)){
		    $attr = explode(',', $tag);
    		foreach($attr as $v){
    			if ($v !='') {
    				$tags[] = ['tag'=>$v, 'url'=> (string) url('tag_list',['tag'=>$pinyin->permalink($v,'')])];
    			}
    		}
		}
        return $tags;
    }

    //获取热门tag
    public function getHotTag()
    {
        //
    }

    //获取tag链接
    public function setTagUrl()
    {
        
    }

}

