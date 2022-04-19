<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2022-04-13 09:54:31
 * @LastEditTime: 2022-04-19 16:42:47
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

class Seo extends AdminController
{

    public function index()
    {
        if(is_file($rob = public_path().'robots.txt')){
            $robots = file_get_contents($rob);
        } else {
            $robots = '';
        }
        
        View::assign('robots',$robots);
        return View::fetch();
    }

    public function push()
    {
        $data = Request::only(['start_id','end_id']);

        if(empty(config('taoler.baidu.push_api'))) return json(['code'=>-1,'msg'=>'请先配置接口push_api']);
        $urls = [];
        if(empty($data['start_id']) || empty($data['end_id'])) {
            $artAllId = Db::name('article')->where(['delete_time'=>0,'status'=>1])->column('id');
        } else {
            $artAllId = Db::name('article')->where(['delete_time'=>0,'status'=>1])->where('id','between',[$data['start_id'],$data['end_id']])->column('id');
        }

        foreach($artAllId as $aid) {
            $urls[] = $this->getRouteUrl($aid);
        }
        $api = config('taoler.baidu.push_api');
        $ch = curl_init();
        $options =  array(
            CURLOPT_URL => $api,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $urls),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        if($result == false) {
            return json(['code'=>-1,'msg'=>'接口失败']);
        }
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
        // 新文件编号
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
                        <mobile:mobile type="pc,mobile"/>
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
        
        return json(['code'=>0,'msg'=>'生成xml成功']);
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
     * 获取文章链接地址
     *
     * @param integer $aid
     * @return string
     */
    protected function getRouteUrl(int $aid) : string
    {
        $indexUrl = $this->getIndexUrl();
        $artUrl = (string) url('detail_id', ['id' => $aid]);

        // 判断是否开启绑定
        //$domain_bind = array_key_exists('domain_bind',config('app'));

        // 判断index应用是否绑定域名
        $bind_index = array_search('index',config('app.domain_bind'));
        // 判断admin应用是否绑定域名
        $bind_admin = array_search('admin',config('app.domain_bind'));

        // 判断index应用是否域名映射
        $map_index = array_search('index',config('app.app_map'));
        // 判断admin应用是否域名映射
        $map_admin = array_search('admin',config('app.app_map'));

        $index = $map_index ? $map_index : 'index'; // index应用名
        $admin = $map_admin ? $map_admin : 'admin'; // admin应用名

        if($bind_index) {
            // index绑定域名
            $url = $indexUrl . str_replace($admin.'/','',$artUrl);
        } else { // index未绑定域名
            // admin绑定域名
            if($bind_admin) {
                $url =  $indexUrl .'/' . $index . $artUrl;
            } else {
                $url =  $indexUrl . str_replace($admin,$index,$artUrl);
            }
            
        }

        return $url;
    }

}