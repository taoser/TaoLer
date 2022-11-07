<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use taoser\SetArr;
use think\facade\Request;
use think\facade\Db;
use think\facade\Session;
use taoser\think\Auth;

// 应用公共文件
function mailto($to,$title,$content)
{
	$mail = new PHPMailer(true);	// Passing `true` enables exceptions
	$mailserver = Db::name('mail_server')->find(1);
try {
    //Server settings
    $mail->SMTPDebug = 0;                       // Enable verbose debug output
//    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->CharSet = 'utf-8';           //b
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = $mailserver['host'];  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $mailserver['mail'];                 // SMTP username
    $mail->Password = $mailserver['password'];                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
//    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $mailserver['port'];                                    // TCP port to connect to

    //Recipients
    $mail->setFrom($mailserver['mail'], $mailserver['nickname']);
    $mail->addAddress($to);     // Add a recipient
    //$mail->addAddress('ellen@example.com');               // Name is optional
    //$mail->addReplyTo('info@example.com', 'Information');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('bcc@example.com');

    //Attachments
    //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

    //Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = $title;
    $mail->Body    = $content;
    //$mail->AltBody =$user ;


    $mail->send();
    //echo 'Message has been sent';
	//return true;
	} catch (Exception $e) {
    //echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
	//return false;
    return $mail->ErrorInfo;
    }
    return 1;
}

//根据user area_id查询区域简称
function getAsing($area_id){
	return Db::name('user_area')->where('id',$area_id)->cache(3600)->value('asing');
}

//根据用户主键ID，查询用户名称
if(!function_exists('getUserName'))
{
    function getUserName($user_id)
    {
       return Db::name('user')->where('id',$user_id)->value('name');
    }
}

if(!function_exists('getUserImg'))
{
    function getUserImg($user_id)
    {
       return Db::name('user')->where('id',$user_id)->value('user_img');
    }
}

//过滤文章摘要
function getArtContent($content)
{
    //过滤html标签
    $content = strip_tags($content);
    // 过滤音视频图片
    $content = preg_replace('/(?:img|audio|video)(\(\S+\))?\[\S+\]/','',$content);
    $content = preg_replace('/\s*/','',$content);
    $content = preg_replace('/\[[^\]]+\]/','',$content);
    return mb_substr(strip_tags($content),0,150).'...';
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

//根据用户名查询主页
function jump()
{
    $username = Request::param('username');
    return Db::name('user')->where('name',$username)->find();
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
function getTree($data, $pId='0')
{
    // 递归
    $tree = [];
    foreach ($data as $k => $v) {
        if ($v['pid'] == $pId) {
            $child = getTree($data, $v['id']);
            if(!empty($child)) {
                $v['children'] = $child;
            }
            $tree[] = $v;
        }
    }
    // 排序
    $cmf_arr = array_column($tree, 'sort');
    array_multisort($cmf_arr, SORT_ASC, $tree);
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

//菜单结构
function getSpaceNmu($level)
{
	return str_repeat('---',$level);
}

//链接投放开关，有设置则打开
function showSlider($type)
{
    $sliders = new \app\common\model\Slider();
    $sliderArr = $sliders->getSliderList($type);
    if(!empty($sliderArr)) {
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
        preg_match_all($pattern,$str,$matchContent);
        if(isset($matchContent[1][0])){
            $img = $matchContent[1][0];
        } else {
            //$temp="./images/no-image.jpg";//在相应位置放置一张命名为no-image的jpg图片

            //匹配格式为 img[/storage/1/article_pic/20220428/6c2647d24d5ca2c179e4a5b76990c00c.jpg]
            $pattern = "/(?<=img\[)[^\]]*(?=\])/";
            preg_match($pattern,$str,$matchContent);
            if(isset($matchContent[0])){
                $img = $matchContent[0];
            }else{
                return false;
            }
        }

    return $img;
}

//判断蜘蛛函数
function find_spider(){
    $useragent = strtolower(empty($useragent) ? Request::header('USER_AGENT') : '');
    $spider_arr=array(
        'bot',
        'spider',
        'slurp',
        'ia_archiver',
    );	
    foreach($spider_arr as $spider){
        $spider = strtolower($spider);
        if(strstr($useragent,$spider)){
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


