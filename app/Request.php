<?php
namespace app;

// 应用请求对象类
class Request extends \think\Request
{
	//过滤空格
	protected $filter = ['trim','htmlspecialchars','strip_tags'];
	
}
