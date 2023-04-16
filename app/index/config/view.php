<?php
// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------
use think\facade\Db;
	//如果网站安装从数据库查询选择的模板
	if(file_exists('./install.lock')){
			$template = Db::name('system')->where('id',1)->value('template');
	} else {
		$template = '';
	}

return [
    // 模板引擎类型使用Think
    'type'          => 'Think',
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule'     => 1,
    // 模板目录名
    'view_dir_name' => 'view' . DIRECTORY_SEPARATOR . $template,
    // 模板后缀
    'view_suffix'   => 'html',
    // 预先加载的标签库
    'taglib_pre_load' => 'app\common\taglib\Article,app\common\taglib\Taoler,app\common\taglib\Comment',
    // 模板文件名分隔符
    'view_depr'     => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'     => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'       => '}',
    // 标签库标签开始标记
    'taglib_begin'  => '{',
    // 标签库标签结束标记
    'taglib_end'    => '}',
	
	'default_filter' => 'htmlspecialchars',

	//模板输出替换
	// 'tpl_replace_string'  =>  [
    // '__STATIC__'=>'/static/layui',
	// '__JS__' => '/static/res/',
	// ],

];
