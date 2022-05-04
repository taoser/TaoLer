<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2022-04-13 09:54:31
 * @LastEditTime: 2022-05-02 11:54:00
 * @LastEditors: TaoLer
 * @Description: 搜索引擎SEO优化设置
 * @FilePath: \TaoLer\app\admin\controller\Seo.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */
declare(strict_types=1); 
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use taoser\SetArr;
use app\admin\model\PushJscode;

class Seo extends AdminController
{

    public function index()
    {
        // 站点地图
        $xml = '';
        $xmlArr = $this->getXmlFile(public_path());
        foreach($xmlArr as $v) {
            $map =  $this->getIndexUrl().'/'.$v;
            $xml .= $map."\n";                  
        }
        // robots
        if(is_file($rob = public_path().'robots.txt')){
            $robots = file_get_contents($rob);
        } else {
            $robots = '';
        }     
        // push_js
        $pushjs = new PushJscode();
        $jscode = $pushjs->getAllCodes();
        View::assign(['xml'=>$xml,'jscode'=>$jscode,'robots'=>$robots]);
        return View::fetch();
    }

    public function push()
    {
        $data = Request::only(['start_id','end_id','time']);

        if(empty(config('taoler.baidu.push_api'))) return json(['code'=>-1,'msg'=>'请先配置接口push_api']);
        $urls = [];
        if(empty($data['start_id']) || empty($data['end_id'])) {
            $artAllId = Db::name('article')->where(['delete_time'=>0,'status'=>1])->whereTime('create_time', $data['time'])->column('id');
        } else {
            $artAllId = Db::name('article')->where(['delete_time'=>0,'status'=>1])->where('id','between',[$data['start_id'],$data['end_id']])->whereTime('create_time', $data['time'])->column('id');
        }
        if(empty($artAllId)) return json(['code'=>-1,'msg'=>'没有查询到结果，无需推送']);
        // 组装链接数组
        foreach($artAllId as $aid) {
            $urls[] = $this->getRouteUrl($aid);
        }
        // 百度接口单次最大提交200，进行分组
        $urls = array_chunk($urls,2000);
        
        $api = config('taoler.baidu.push_api');
        $ch = curl_init();
        foreach($urls as $url) {
            $options =  array(
                CURLOPT_URL => $api,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => implode("\n", $url),
                CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
            );
            curl_setopt_array($ch, $options);
            $result = curl_exec($ch);
            if($result == false) {
                return json(['code'=>-1,'msg'=>'接口失败']);
            }
        }
        curl_close($ch);
        
        $res = stripos($result,'error');
        $data = json_decode($result); 
        if($res !== false) {    
            return json(['code'=>-1,'msg'=>$data->message]);
        };   
        return json(['code'=>0,'msg'=>'成功推送'.$data->success.'条,今天剩余'.$data->remain]);
    }

    /**
     * 百度接口配置
     *
     * @return void
     */
    public function config()
    {
        $baidu = [];
        $data = Request::only(['client_id','client_secret','push_api']);
        foreach($data as $k => $v) {
            if($v !== ''){
                if($k == 'push_api') {
                    $baidu[$k] = "'$v'";
                } else {
                    $baidu[$k] = $v;
                }
            }
        }
        $res = SetArr::name('taoler')->edit([
            'baidu' => $baidu,
        ]);
        if($res == true){
            return json(['code'=>0,'msg'=>'设置成功']);
        }
    }

    /**
     * 百度谷歌sitemap生成xml文件
     *
     * @return void
     */
    public function map()
    {
        $data = Request::only(['map_num','map_time','map_level']);
        // 写文件字符串
        $str = '';
        // 标记第一次调用写ID
        $flag= true;
        // 写ID
        $w_id = '';
        // 生成新文件编号，防止重复写入，
        $i = 1;
        // 获取public下所有xml文件
        $newFile = $this->getXmlFile(public_path());
        // 没有xml文件时，不存在重写
        if(empty($newFile)) {
            $rewrite = false;
        } else {
            $xmlFile = end($newFile);
            $strFile = file_get_contents(public_path().$xmlFile);
            $num = substr_count($strFile, '<url>');
            // 是否有需要追加在文件，判断最新文件是否未写满
            $rewrite = $num < $data['map_num'] ? true : false;
        }
        
       if($rewrite){
            // 需要追加的数量
           $limit = (int) $data['map_num'] - $num;
           $name = pathinfo($xmlFile,PATHINFO_FILENAME);
           $arr = explode('_', $name);
           // 检验当天，避免重复生成
           if($arr[0] == date('Y-d-m'))
           {
               $i = $arr[1] + 1;
           }
       } else {
            $limit = (int) $data['map_num'];
       }
        // 最新ID
        $maxId = Db::name('article')->where(['delete_time'=>0,'status'=>1])->max('id');

        do
        {
            $wr_id = $flag ? config('taoler.sitemap.write_id') : $w_id;
            $artAllId = Db::name('article')
                        ->where(['delete_time'=>0,'status'=>1])
                        ->where('id', '>', (int) $wr_id)
                        ->order('id','asc')->limit($limit)->column('update_time','id');
            if(empty($artAllId)) {
                return json(['code'=>-1,'msg'=>'本次无需生成']);
            } else {
                // 本次最新文件ID
                $last_id = array_key_last($artAllId);
                // 循环拼接文件字符串
                foreach($artAllId as $aid => $uptime) {
                    // url生成
                    $url = $this->getRouteUrl($aid);
                    $time = date('Y-m-d', $uptime);
                    // 组装字符串
                    $str .= <<<STR
                    <url>
                        <loc>$url</loc>
                        <lastmod>$time</lastmod>
                        <changefreq>daily</changefreq>
                        <priority>0.5</priority>
                    </url>\n
                    STR;         
                }
                
                // 写文件
                if($rewrite){
                    // 写入旧xml文件
                    $reps = $str.'</urlset>';
                    $xml = preg_replace('/<\/urlset>/', $reps, $strFile);
                    $res = file_put_contents(public_path().$xmlFile, $xml);
                    if($res == false){
                        return json(['code'=>-1,'msg'=>$xmlFile.'写入失败']);
                    }
                    $limit = $data['map_num'];
                    $rewrite = false;
                } else {
                    // 生成新xml
                    $xml = <<<XML
                    <?xml version="1.0" encoding="utf-8"?>	<!-- XML文件需以utf-8编码-->
                    <urlset>\n$str</urlset>
                    XML;              
                    $res = file_put_contents(public_path().date('Y-m-d').'_'.$i.'.xml', $xml);
                    if($res == false){
                        return json(['code'=>-1,'msg'=>date('Y-m-d').'_'.$i.'.xml写入失败']);
                    }
                }
                // 重置标记内容
                $str = '';
                $i++;
                $w_id = $last_id;
                $flag = false;
            }
        }
        while($last_id < (int) $maxId);
        // 写配置，标记最后写入ID
        $res = SetArr::name('taoler')->edit([
            'sitemap' => [
                'map_num'	=> $data['map_num'],
                'write_id'	=> $last_id,
            ],
        ]);
        if($res == false){
            return json(['code'=>-1,'msg'=>'写xml配置失败']);
        }
        
        return json(['code'=>0,'msg'=>'本次成功生成'.count($artAllId).'条xml']);
    }

    /**
     * 返回public目录下xml名称数组
     *
     * @param string $dir
     * @return array
     */
    public function getXmlFile(string $dir) : array
    {
        $arr = [];
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            if(is_file("$dir/$file") && !is_link($dir) && (pathinfo("$dir/$file", PATHINFO_EXTENSION)) == 'xml') 
            {
                $arr[] =  "$file";
            }
        }
        return $arr;
    }
    
    /**
     * 生成robots
     *
     * @return void
     */
    public function robots()
    {
        if(Request::isPost()){
            $txt = input('robots');
            $xmlArr = $this->getXmlFile(public_path());
            foreach($xmlArr as $v) {
                $res = stripos($txt, $v);
                if($res == false){
                    $map =  $this->getIndexUrl().'/'.$v;
                    $txt .= "\nsitemap:".$map;                   
                }
            }
            $res = file_put_contents(public_path().'robots.txt',$txt);
            if($res == false){
                return json(['code'=>-1,'msg'=>$v.'写入失败']);
            }
            return json(['code'=>0,'msg'=>'设置成功']);
        }
    }

    /**
     * 保存搜索平台js代码
     *
     * @return void
     */
    public function savePushJs()
    {
        $data = Request::only(['name','jscode']);
        if(empty($data['name'])) {
            return json(['code'=>-1,'msg'=>'请术输入名称']);
        }
        if(empty($data['jscode'])){
            return json(['code'=>-1,'msg'=>'请术输入代码']);
        }
        $push = new PushJscode();
        $res = $push->saveCode($data);
        if(!$res) {
            return json(['code'=>-1,'msg'=>'保存失败']);
        }
        return json(['code'=>0,'msg'=>'保存成功']);
    }

    /**
     * 删除平台js代码
     *
     * @return void
     */
    public function delPushJs()
    {
        $id = (int) input('id');
        $push = new PushJscode();
        $res = $push->delCode($id);
        if(!$res) {
            return json(['code'=>-1,'msg'=>'删除失败']);
        }
        return json(['code'=>0,'msg'=>'删除成功']);
    }

    public function searchLog()
    {
        $time = input('search_time');
        $name = input('spider_name');
        $page = input('page') ? input('page') : 1;
        $limit = input('limit') ? input('limit') : 20;
        $logPath = app()->getRootPath().'runtime/log/browse/'.$time.'.log';
        $logPath = str_replace('\\','/',$logPath);
        if(!file_exists($logPath)) {
            return json(['code'=>-1,'msg'=>'还没有要分析的日志哦!']);
        }
        $log = file_get_contents($logPath);
        $log = preg_replace('/\[info\][^\n]*compatible;/', '', $log);
        $log = preg_replace('/\[info\][^\n]*(?=YisouSpider)/', ' ', $log);
        
        switch($name) {
            case 'Baiduspider':
                preg_match_all('/(.*?)(?:Baiduspider)+[^\n]*\r?\n/',$log,$arr);
            break;
            case 'Bytespider':
                preg_match_all('/(.*?)(?:Bytespider)+[^\n]*\r?\n/',$log,$arr);
            break;
            case 'Googlebot':
                preg_match_all('/(.*?)(?:Googlebot)+[^\n]*\r?\n/',$log,$arr);
            break;
            case 'bingbot':
                preg_match_all('/(.*?)(?:bingbot)+[^\n]*\r?\n/',$log,$arr);
            break;
            default:
                // 正则全部蜘蛛
                preg_match_all('/(.*?)(?:bingbot|Googlebot|Baiduspider|Bytespider|360Spider|YisouSpider|Sosospider|Sogou News Spider|SemrushBot|AhrefsBot|MJ12bot)+[^\n]*\r?\n/',$log,$arr);
        }

        // $string = '';
        // foreach($arr[0] as $str) {
        //     $str = preg_replace('/\[(.*?)T/', '', $str);
        //     $str = preg_replace('/\+08:00\]/', '', $str);
        //     $string .= preg_replace('/\/(.*?)\)/', '', $str);
        // }

        // if(strlen($string)) {
        //     return json(['code'=>0,'msg'=>'分析成功','data'=>$string]);
        // } else {
        //     return json(['code'=>-1,'msg'=>'还没有蜘蛛来哦']);
        // }
        $data = [];
        $list = [];
        if(count($arr[0])) {
            $list['code']= 0;
            $list['msg'] = '分析成功';
            $list['count'] = count($arr[0]);
            foreach($arr[0] as $k =>$str) {
                // $str = preg_replace('/\[(.*?)T/', '', $str);
                // $str = preg_replace('/\+08:00\]/', '', $str);
                $str = preg_replace('/\/(.*?)\)/', '', $str);
                // 时间
                $ptime = "/([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})/";
                preg_match($ptime, $str,$at);
                $time = str_replace('T',' ',$at[0]);
                //$list[$k]['time'] = $time;
                // ip
                $pip = '/((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})(\.((2(5[0-5]|[0-4]\d))|[0-1]?\d{1,2})){3}/';
                preg_match($pip, $str,$aip);
                $ip = $aip[0];
                // url
                $pattern="/(http|https):\/\/.*$/i";
                preg_match($pattern, $str,$url);
                // name
                preg_match('/(?:bingbot|Googlebot|Baiduspider|Bytespider|360Spider|YisouSpider|Sosospider|Sogou News Spider|SemrushBot|AhrefsBot|MJ12bot)/', $str, $n);
                $name = $n[0];

               //$list['data'][] = ['id'=>$k + 1, 'time'=>$time, 'name'=>$name, 'ip'=>$ip, 'url'=>$url[0]];
               $data[] = ['id'=>$k + 1, 'time'=>$time, 'name'=>$name, 'ip'=>$ip, 'url'=>$url[0]];
            }
            $datas = array_chunk($data,(int)$limit);
            //$pages = count($datas);

            foreach($datas as $k=>$v) {
                if($page-1 == $k) {
                    $list['data'] = $v;
                }
            }

            return json($list);
        } else {
            return json(['code'=>-1,'msg'=> '没有需要分析的数据']);
        }

    }

}