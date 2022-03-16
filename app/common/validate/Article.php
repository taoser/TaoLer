<?php
namespace app\common\validate;


use think\Validate;

class Article extends Validate
{
    protected $rule = [
        'title|标题' => 'require|min:4|max:50',
        'content|内容' => 'require',
        'cate_id|分类' => 'require',
        'captcha|验证码' => 'require|captcha'
    ];
	
	public function sceneArtadd()
	{
		return $this->only(['cate_id','title','content']);
	}
}