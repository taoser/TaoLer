<?php
namespace app\index\validate;


use think\Validate;

class Article extends Validate
{
    protected $rule = [
        'title|标题' => 'require|min:4|max:50',
        'content|内容' => 'require',
        'category_id|分类' => 'require',
        'captcha|验证码' => 'require|captcha'
    ];
	
	public function sceneArtadd()
	{
		return $this->only(['category_id','title','content']);
	}
}