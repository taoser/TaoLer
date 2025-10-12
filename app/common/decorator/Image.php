<?php

namespace app\common\decorator;

use app\common\lib\FileHelper;
use think\facade\Request;
use app\common\lib\HttpClient;

class Image extends ArticleProcessorDecorator
{
    protected string $content = '';
    public function process($data) {

		$data = parent::process($data);
		$this->content = $data['content'];
		
		$imagesArr = FileHelper::getImagesLink($data['content']);

		$httpClinet = new HttpClient();


		if(count($imagesArr)) {
			foreach($imagesArr as $image) {
				//1.带http地址的图片，2.非本站的网络图片 3.非带有？号等参数的图片
				if((stripos($image,'http') !== false) && (stripos($image, Request::domain()) === false) && (stripos($image, '?') == false)) { 
                    // 如果图片中没有带参数或者加密可下载
                    //下载远程图片(可下载)

					$filename = pathinfo($image, PATHINFO_BASENAME);
					//$dirname = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_DIRNAME);

					//路径
					$path =  'storage/download/article_pic/' . date('Ymd',time()) . '/';
					//绝对文件夹
					$fileDir = public_path() . $path;
					//文件绝对路径
					$filePath =  $fileDir . $filename;
					//相对路径文件名
					$realFile = '/' . $path . $filename;

					$res = $httpClinet->download($image, $filePath);
					if($res !== false) {
						//替换图片链接
                    	$this->content = str_replace($image, Request::domain().$realFile, $this->content);
						$data['content'] = $this->content;
					}
				}
			}
            //不可下载的图片，如加密或者带有参数的图片如？type=jpeg,直接返回content
		}

		return $data;
    }

}