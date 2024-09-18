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

/**
 * 数组层级缩进转换
 * @param array $array 源数组
 * @param int   $pid
 * @param int   $level
 * @return array
 */
function array2level($array, $pid = 0, $level = 1)
{
    static $list = [];

    foreach ($array as $v) {

        if ($v['pid'] == $pid) {

            $v['level'] = $level;
            $list[]     = $v;

            array2level($array, $v['id'], $level + 1);
        }
    }

    return $list;
}

/**
 * 构建层级（树状）数组
 * @param array  $array          要进行处理的一维数组，经过该函数处理后，该数组自动转为树状数组
 * @param string $pid_name       父级ID的字段名
 * @param string $child_key_name 子元素键名
 * @return array|bool
 */
function array2tree(&$array, $pid_name = 'pid', $child_key_name = 'children')
{
    $counter = array_children_count($array, $pid_name);
    if (!isset($counter[0]) || $counter[0] == 0) {
        return $array;
    }
    $tree = [];
    while (isset($counter[0]) && $counter[0] > 0) {
        $temp = array_shift($array);
        if (isset($counter[$temp['id']]) && $counter[$temp['id']] > 0) {
            array_push($array, $temp);
        } else {
            if ($temp[$pid_name] == 0) {
                $tree[] = $temp;
            } else {
                $array = array_child_append($array, $temp[$pid_name], $temp, $child_key_name);
            }
        }
        $counter = array_children_count($array, $pid_name);
    }

    return $tree;
}

/**
 * 子元素计数器
 * @param array $array
 * @param int   $pid
 * @return array
 */
function array_children_count($array, $pid)
{
    $counter = [];
    foreach ($array as $item) {
        $count = isset($counter[$item[$pid]]) ? $counter[$item[$pid]] : 0;
        $count++;
        $counter[$item[$pid]] = $count;
    }

    return $counter;
}

/**
 * 把元素插入到对应的父元素$child_key_name字段
 * @param        $parent
 * @param        $pid
 * @param        $child
 * @param string $child_key_name 子元素键名
 * @return mixed
 */
function array_child_append($parent, $pid, $child, $child_key_name)
{
    foreach ($parent as &$item) {
        if ($item['id'] == $pid) {
            if (!isset($item[$child_key_name])) {
                $item[$child_key_name] = [];
            }

            $item[$child_key_name][] = $child;
        }
    }

    return $parent;
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
    function get_all_img($str)
    {
        //匹配格式为 <img src="http://img.com" />的图片
        $pattern = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern, $str,$matchContent);
        if(isset($matchContent[1][0])) {
            return array_unique($matchContent[1]);
        }
        return [];
    }
}

if (!function_exists('get_all_video')) {
    /**
     * 提取字符串中所有图片
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


