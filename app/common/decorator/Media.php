<?php

namespace app\common\decorator;

class Media extends ArticleProcessorDecorator {
    
    public function process($data) {
		$data = parent::process($data);
		
		$data['media'] = [
			'images' => [],
			'videos' => [],
			'audios' => []
		];

		$images = get_all_img($data['content']);
		$video = get_one_video($data['content']);

		if(!empty($images)) {
			$data['media']['images'] = $images;
			$data['has_image'] = count($images);
			$data['thum_img'] = $images[0];
		}
		
		if(!empty($video)) {
			$data['media']['videos'] = $video;
			$data['has_video'] = count($video);
		}

		return $data;
    }

}