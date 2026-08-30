<?php

use taoser\SetArr;
use think\Request;
use think\facade\Db;
use think\facade\Session;
use taoser\think\Auth;

if(!function_exists('system_config'))
{
    /**
     * 系统配置
     * @param string $name 配置键名
     * @param mixed $default 默认值
     * @return mixed
     */
    function system_config(string $name, mixed $default = null): mixed
    {
        $config = new \app\entity\SystemConfig();

        return $config->getValue($name, $default);
    }
}

if(!function_exists('system_config_set'))
{
    /**
     * 设置系统配置
     * @param string $name 配置键名
     * @param mixed $value 配置值
     * @return mixed
     */
    function system_config_set(string $name, mixed $value): mixed
    {
        $config = new \app\entity\SystemConfig();
        return $config->setValue($name, $value);
    }
}

if(!function_exists('create_order_no'))
{
    /**
     * 生成唯一订单号
     * @param string $prefix 订单号前缀
     * @return string
     */
    function create_order_no(?string $prefix =''): string
    {
        $rand = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . date('YmdHis') . substr(microtime(), 2, 6) . $rand;
    }
}

if(!function_exists('build_tree')) {
    /**
     * 无限极菜单构建函数（高性能版 20260711）
     *
     * 采用引用映射方式构建树形结构，时间复杂度 O(n)
     * 支持自定义字段名，适合处理大量分类数据
     *
     * @param array $data 原始数据数组，每个元素必须包含 id、pid、sort 字段
     * @param string $sortField 排序字段名（默认'sort'，空字符串表示不排序）
     * @param int|string $rootPid 根节点父ID（默认0，表示顶级菜单）
     * @param string $idField 主键字段名（默认'id'）
     * @param string $pidField 父级ID字段名（默认'pid'）
     * @param string $childrenField 子节点存储字段名（默认'children'）
     * @param bool $asc 是否升序排序（默认true）
     * @return array 树形结构数组
     */
    function build_tree(
            array $data,
            string $sortField = '',
            $rootPid = 0,
            string $idField = 'id',
            string $pidField = 'pid',
            string $childrenField = 'children',
            bool $asc = true
        ): array 
    {
        // 空数据处理
        if (empty($data)) {
            return [];
        }

        // 使用array_column获取所有ID，确保ID存在

        // $ids = array_column($items, $idField);
        // if (in_array(null, $ids, true)) {
        //     throw new InvalidArgumentException("Missing or invalid value in '{$idField}' field");
        // }

        // 使用array_column获取所有PID，确保PID存在

        // $pids = array_column($items, $pidField);
        // if (in_array(null, $pids, true)) {
        //     throw new InvalidArgumentException("Missing or invalid value in '{$pidField}' field");
        // }

        // 如果指定了排序字段且数据中存在该字段才排序
        if ($sortField !== '') {

            // 检查排序字段是否存在

            // $sortValues = array_column($items, $sortField);
            // if (in_array(null, $sortValues, true)) {
            //     throw new InvalidArgumentException("Missing or invalid value in '{$sortField}' field");
            // }

            $firstItem = reset($data);
            if (is_array($firstItem) && array_key_exists($sortField, $firstItem)) {
                usort($data, function($a, $b) use ($sortField, $asc) {
                    $aVal = $a[$sortField] ?? 0;
                    $bVal = $b[$sortField] ?? 0;
                    return $asc ? $aVal <=> $bVal : $bVal <=> $aVal;
                });
            }
        }

        // 构建引用映射（核心优化）
        $tree = [];
        $refs = [];

        // 初始化引用映射并确保children字段存在
        foreach ($data as &$item) {
            $id = $item[$idField];
            $item[$childrenField] = [];
            $refs[$id] = &$item;
        }
        unset($item); // 解除引用

        // 构建树形结构
        foreach ($data as $item) {
            $id = $item[$idField];
            $pid = $item[$pidField];

            if ($pid === $rootPid) {
                // 根节点直接挂载树
                $tree[] = &$refs[$id];
            } elseif (isset($refs[$pid])) {
                // 子节点挂载父节点
                $refs[$pid][$childrenField][] = &$refs[$id];
            } 
            // 孤儿节点：默认丢弃，如需挂到根节点可取消下方注释
            // else {
            //     // $tree[] = &$refs[$id]; 
            // }

        }

        // 统一设置 isParent：children 不为空则 true，否则 false
        foreach ($refs as &$ref) {
            $ref['isParent'] = !empty($ref[$childrenField]);
        }
        unset($ref);

        return $tree;
    }
}

if(!function_exists('cut_byte')) {
    /**
     * 按字节截取，兼容UTF8中文，不产生乱码，控制总字节不超限
     * @param string $str 原字符串
     * @param int $maxByte 最大字节数
     * @param string $suffix 截断后缀
     * @return string
     */
    function cut_byte(string $str, int $maxByte = 256, string $suffix = '...'): string
    {
        $str = trim($str);
        $rawLen = strlen($str);
        $suffixLen = strlen($suffix);

        if ($rawLen <= $maxByte) {
            return $str;
        }

        // 若最大字节不足以容纳后缀，直接返回空或仅后缀（根据业务决定）
        if ($maxByte <= $suffixLen) {
            return $suffix;
        }

        // 使用 mb_strcut 按字节截取，确保不破坏字符
        $temp = mb_strcut($str, 0, $maxByte - $suffixLen, 'UTF-8');
        
        return $temp . $suffix;
    }
}


//过滤文章摘要
function getArtContent(string $content)
{
    //过滤html标签
    // $content = strip_tags($content);
    // 去除所有& nbsp和html标签
    $content = preg_replace("/(\s|\&nbsp\;|\&ldquo\;|\&rdquo\;| |\xc2\xa0)/", "", strip_tags($content));
    // 过滤音视频图片
    $content = preg_replace('/(?:img|audio|video)(\(\S+\))?\[\S+\]/','',$content);
    $content = preg_replace('/\s*/','',$content);
    $content = preg_replace('/\[[^\]]+\]/','',$content);
    return mb_substr($content,0,150).'...';
}

//根据评论时间查询是否已过修改期
function getLimtTime(string $create_time)
{
    $nt = time();
    $lt = intval(($nt - strtotime($create_time))/86400);
    
    return $lt;
}

//按钮权限检查
function checkRuleButton(string $rules_button)
{
	$admin_id = Session::get('admin_id');
	$auth = new Auth();
	$res = $auth->check($rules_button, $admin_id );
	
	if($res || $admin_id == 1){
		return true;
	} else {
		return false;
	}
}

//提取内容第一张图片
function getOnepic(string $str)
{
    //匹配格式为 <img src="http://img.com" />
    $pattern = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";
    //匹配格式为 img[/storage/1/article_pic/20220428/6c2647d24d5ca2c179e4a5b76990c00c.jpg]
    $pattern2 = "/(?<=img\[)[^\]]*(?=\])/";

    preg_match($pattern, $str, $matchContent);
    if(isset($matchContent[1])){
        $img = $matchContent[1];
        return $img;
    } 
    //$temp="./images/no-image.jpg";//在相应位置放置一张命名为no-image的jpg图片
    preg_match($pattern2, $str, $matchContent2);
    if(isset($matchContent2[0])){
        $img = $matchContent2[0];
        return $img;
    }
    
    return false;
}

if (!function_exists('get_all_img')) {
    /**
     * 提取字符串中所有图片
     * @param string $text
     * @return array
     */
    function get_all_img(string $text)
    {
        // 定义正则表达式来匹配图片链接，支持更多图片格式
        $pattern = '/<img[^>]+src=["\']([^"\']+\.(jpg|jpeg|png|gif|svg))["\']/i';
        $imageLinks = [];
        if (preg_match_all($pattern, $text, $matches)) {
            $imageLinks = $matches[1];
        }

        return $imageLinks;
    }
}

if (!function_exists('get_one_video')) {
    /**
     * 提取字符串中所有视频
     * @param string $str
     * @return array
     */
    function get_one_video(string $str)
    {
        //$pattern_video = "/(src)=( \\\?)([\"|' ]?)([^ \"'>]+\.(swf|flv|mp4|rmvb|avi|mpeg|ra|ram|mov|wmv)((\?[^ \"'>]+)?))\\2\\3/i";
        // $pattern_music = "/(src)=( \\\?)([\"|' ]?)([^ \"'>]+\.(mp3|wav|wma|ogg|ape|acc))\\2\\3/i";
        // $pattern_img = "/(src)=( \\\?)([\"|' ]?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png))\\2\\3/i";

        //匹配格式为 <video src="http://img.com" > </video> 的视频
        $pattern = "/<[video|VIDEO][\s\S]*src=[\'|\"](.*?(?:[\.mp4|\.mkv|\.flv|\.avi]))[\'|\"].*?[<\/video]>/";
        preg_match($pattern, $str, $matchs);
        if(isset($matchs[1])) {
            return $matchs[1];
        }
        return [];
    }
}

if (!function_exists('get_all_video')) {
    /**
     * 提取字符串中所有视频
     * @param $str
     * @return array
     */
    function get_all_video(string $str)
    {
        //匹配格式为 <video src="http://img.com" > </video> 的视频
        $pattern = "/<[video|VIDEO][\s\S]*src=[\'|\"](.*?(?:[\.mp4|\.mkv|\.flv|\.avi]))[\'|\"].*?[<\/video]>/";
        preg_match_all($pattern, $str,$matchs);
        if(isset($matchs[1][0])) {
            return array_unique($matchs[1]);
        }
        return [];
    }
}

//判断蜘蛛函数
function find_spider(){
    $useragent = strtolower(request()->header('user-agent'));
    if(empty($useragent)) return false;
    
    $spider_arr = [
        'spider',
        'bot',
        'slurp',
        'ia_archiver',
    ];

    foreach($spider_arr as $spider){
        $spider = strtolower($spider);
        if(strstr($useragent, $spider)){
            return true;
        }
    }

    return false;
}

if (!function_exists('__')) {
    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array  $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return \think\facade\Lang::get($name, $vars, $lang);
    }
}

function advanced_compress_html_js(string $code) {
    // 去除html注释
    $code = preg_replace('~<!--.*?-->~s', '', $code);
    // // 去除单行注释 不包括网址中的//
    // $code = preg_replace('~(?:^|[^:])//.*$~m', '', $code);
    // // 去除多行注释
    $code = preg_replace('/\/\*.*?\*\//s', '', $code);
    // halt($code);
    // 先处理JavaScript部分，合并变量声明（简单示例）
    // $jsPattern = '/var\s+([a-zA-Z_][a-zA-Z0 - 9_]*)\s*=\s*([^;]+);\s*var\s+([a-zA - Z_][a-zA - Z0 - 9_]*)\s*=\s*([^;]+);/';
    // $code = preg_replace($jsPattern, 'var $1 = $2; var $3 = $4;', $code);
    // 处理HTML标签属性，去除属性值前后多余的空格
    // $htmlPattern = '/(\<[a - zA - Z]+)(\s+[a - zA - Z_]+="\s*([^"]+)\s*")/';
    // $code = preg_replace($htmlPattern, '$1 $2', $code);
    // 去除换行符和制表符等空白字符（与之前类似）
    $code = str_replace("\n", "", $code);
    $code = str_replace("\t", "", $code);
    // $code = preg_replace('/\s+/', ' ', $code);

    return $code;
}

// 文件压缩
function compressHtmlJs(string $html) {
    // 移除 HTML 注释
    $html = preg_replace('/<!--(?!\[if|\<\!\[endif\])(.*?)-->/is', '', $html);

    // 移除 JS 多行注释
    $html = preg_replace('/\/\*(.*?)\*\//is', '', $html);

    // 移除 JS 单行注释 排除网址外的单行注释
    $html = preg_replace_callback(
        '/(https?:\/\/[^\s<>]*|\/\/.*?(\n|$))/',
        function ($matches) {
            if (str_starts_with($matches[0], '//')) {
                return isset($matches[2]) ? $matches[2] : '';
            }
            return $matches[0];
        }, $html);

    // 移除 JS 单行注释 正则以//开头，内容中不包含>，以换行符结尾的单行注释给移除
    // $html = preg_replace_callback('/\/\/([^>\r\n]*)(\n|\r\n)/', function ($matches) {
    //     return $matches[2];
    // }, $html);

    // 压缩 HTML 空白字符
    $html = preg_replace('/\s+/', ' ', $html);
    $html = preg_replace('/>\s+</', '><', $html);

    return $html;

}



