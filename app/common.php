<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
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
    $mail->CharSet = 'utf-8';           //b
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = $mailserver['host'];  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $mailserver['mail'];                 // SMTP username
    $mail->Password = $mailserver['password'];                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
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
	return true;
	} catch (Exception $e) {
    //echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
	return false;
    }
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


//根据文章分类ID查询分类名
function getCateName($ename)
    {
       
        return  Db::name('cate')->where('ename',$ename)->cache(3600)->value('catename');  
    }

//根据文章分类ID查询分类描述
function getCateDesc($ename)
    {
        return  Db::name('cate')->where('ename',$ename)->cache(3600)->value('desc');
    }

//过滤文章摘要
function getArtContent($content)
{
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


//菜单递归
function getTree($data)
{
	$tree = [];
	foreach ($data as $array) {

		if(isset($data[$array['pid']])) {
			$data[$array['pid']]['children'][] = &$data[$array['id']];
			//$tree = $data;
		} else {
			$tree[] = &$data[$array['id']];
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

//菜单结构
function getSpaceNmu($level)
{
	return str_repeat('---',$level);
}
