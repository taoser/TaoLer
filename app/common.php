<?php

use taoser\SetArr;
use think\facade\Request;
use think\facade\Db;
use think\facade\Session;
use taoser\think\Auth;

//根据用户主键ID，查询用户名称
if(!function_exists('getUserName'))
{
    function getUserName($uid)
    {
       return Db::name('user')->where('id', $uid)->value('name');
    }
}

if(!function_exists('getUserImg'))
{
    function getUserImg($uid)
    {
       return Db::name('user')->where('id', $uid)->value('user_img');
    }
}

//过滤文章摘要
function getArtContent($content)
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

//根据帖子收藏主键ID，查询帖子名称
if(!function_exists('getArticName'))
{
    function getArticName($article_id)
    {
       return Db::name('article')->where('id',$article_id)->value('title');
    }
}

//根据评论时间查询是否已过修改期
function getLimtTime($create_time)
{
    $nt = time();
    $lt = intval(($nt - strtotime($create_time))/86400);
    
    return $lt;
}

//菜单无限极分类
function getTree($data, $pId = 0)
{
    // 递归
    $tree = [];
    foreach ($data as $k => $v) {
        if ((int) $v['pid'] == $pId) {
            $child = getTree($data, $v['id']);
            if(!empty($child)) {
                $v['children'] = $child;
            }
            $tree[] = $v;
            //把这个节点从数组中移除,减少后续递归消耗
            unset($data[$k]);
        }
    }
    // 包含sort字段才能排序
    // $cmf_arr = array_column($tree, 'sort');
    // array_multisort($cmf_arr, SORT_ASC, $tree);
    return $tree;
}

/**
 * 将一维数组转换为树形结构（无限级分类）
 * @param array $items 原始数据数组
 * @param int $rootPid 根节点ID（支持数值或字符串）
 * @param string $idField 主键字段名
 * @param string $pidField 父级ID字段名
 * @param string $childrenField 子节点存储字段名
 * @param string $sortField 排序字段名（空字符串表示不排序）
 * @param bool $asc 是否升序（仅在排序时有效）
 * @return array 树形结构数组
 */
function getArrayTree(
    array $items,
    int $rootPid = 0,
    string $idField = 'id',
    string $pidField = 'pid',
    string $childrenField = 'children',
    string $sortField = 'sort',
    bool $asc = true
): array {
    if (empty($items)) {
        return [];
    }

    // 使用array_column获取所有ID，确保ID存在
    $ids = array_column($items, $idField);
    if (in_array(null, $ids, true)) {
        throw new InvalidArgumentException("Missing or invalid value in '{$idField}' field");
    }

    // 使用array_column获取所有PID，确保PID存在
    $pids = array_column($items, $pidField);
    if (in_array(null, $pids, true)) {
        throw new InvalidArgumentException("Missing or invalid value in '{$pidField}' field");
    }

    // 自定义排序
    if (!empty($sortField)) {
        // 检查排序字段是否存在
        $sortValues = array_column($items, $sortField);
        if (in_array(null, $sortValues, true)) {
            throw new InvalidArgumentException("Missing or invalid value in '{$sortField}' field");
        }
        
        usort($items, function ($a, $b) use ($sortField, $asc) {
            $result = $a[$sortField] <=> $b[$sortField];
            return $asc ? $result : -$result;
        });
    }

    // 构建引用关系
    $tree = [];
    $reference = [];

    // 初始化引用映射并确保children字段存在
    foreach ($items as &$item) {
        $id = $item[$idField];
        $item[$childrenField] = [];
        $reference[$id] = &$item;
    }
    unset($item);

    // 构建树形结构
    foreach ($items as $item) {
        $id = $item[$idField];
        $pid = $item[$pidField];
        
        if ($pid === $rootPid) {
            $tree[] = &$reference[$id];
        } elseif (isset($reference[$pid])) {
            $reference[$pid][$childrenField][] = &$reference[$id];
        }
    }

    return $tree;
}    

//按钮权限检查
function checkRuleButton($rules_button)
{
	$admin_id = Session::get('admin_id');
	$auth = new Auth();
	$res = $auth->check($rules_button,$admin_id );
	
	if($res || $admin_id == 1){
		return true;
	} else {
		return false;
	}
}

//提取内容第一张图片
function getOnepic($str)
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
     * @param $str
     * @return array
     */
    function get_all_img($text)
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
     * @param $str
     * @return array
     */
    function get_one_video($str)
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
    function get_all_video($str)
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
    $useragent = strtolower(Request::header('user-agent'));
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

function advanced_compress_html_js($code) {
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
function compressHtmlJs($html) {
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



