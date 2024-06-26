<?php

namespace app\common\lib;

use taoser\SetArr;
use think\facade\Db;
use think\facade\Lang;
use think\facade\Request;
use think\response\Json;

class Arts
{

    /**
     * 数据内容
     *
     * @var string
     */
    protected $content = '';

    //显示网站设置
    protected function getSystem()
    {
        //1.系统配置信息
        return Db::name('system')->cache('system',3600)->find(1);

    }

    //域名协议转换 把数据库中的带HTTP或不带协议的域名转换为当前协议的域名前缀
	protected function getHttpUrl($url)
	{
		//域名转换为无http协议
        $www = stripos($url,'://') ? substr(stristr($url,'://'),3) : $url;
		$htpw = Request::scheme().'://'. $www;
		return  $htpw;
	}

    protected function getDomain()
    {
        return $this->getHttpUrl($this->getSystem()['domain']);
    }

    //得到当前系统安装前台域名
    protected function getIndexUrl()
    {
        $sys = $this->getSystem();
        return $this->getHttpUrl($sys['domain']);
    }

    /**
     * 非admin应用的文章url路由地址
     * @param int $aid
     * @param $ename
     * @return string
     */
    public function getRouteUrl(int $aid, $ename = '')
    {
        $domain = $this->getDomain();
        $appName = app('http')->getName();
        $articleUrl = (string) url('article_detail', ['id' => $aid]);
        // 详情动态路由，$aid, $ename
        if(config('taoler.url_rewrite.article_as') == '<ename>/'){
            $articleUrl = (string) url('article_detail', ['id' => (int) $aid, 'ename'=> $ename]);
        }

//        // 判断应用是否绑定域名
//        $app_bind = array_search($appName, config('app.domain_bind'));
//        // 判断应用是否域名映射
//        $app_map = array_search($appName, config('app.app_map'));

        //a.appName不是admin
        return $domain . $articleUrl;
    }

    /**
     * 关键词
     * 通过百度分词接口获取关键词或者标签
     * @param $flag string 为word时获取分词，2.为tag时获取标签
     * @param $title string
     * @param $content string
     * @return array
     */
    public function setKeywords(string $flag,string $title,string $content) :array
    {
        // 获取seo插件配置
        $conf = get_addons_config('seo');

        $keywords = [];

        // seo插件配置
        $addon = get_addons_instance('seo');
        $config = $addon->getConfig();
        $seo_token = $config['baidufenci']['access_token'];

        // 百度分词自动生成关键词
        if(!empty($seo_token)) {
            //headers数组内的格式
            $headers = [];
            $headers[] = "Content-Type:application/json";

            switch($flag) {
                //分词
                case 'word':
                    $url = 'https://aip.baidubce.com/rpc/2.0/nlp/v1/lexer?charset=UTF-8&access_token='.$seo_token;
                    $body = ["text" => $title];
                    break;
                //标签
                case 'tag':
                    $url = 'https://aip.baidubce.com/rpc/2.0/nlp/v1/keyword?charset=UTF-8&access_token='.$seo_token;
                    $body = ['title' => $title, 'content' => $content];
                    break;
                default:
                    $url = 'https://aip.baidubce.com/rpc/2.0/nlp/v1/lexer?charset=UTF-8&access_token='.$seo_token;
                    $body = ["text" => $title];
            }

            $postBody    = json_encode($body);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);//设置请求头
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postBody);//设置请求体
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');//使用一个自定义的请求信息来代替"GET"或"HEAD"作为HTTP请求。(这个加不加没啥影响)
            $datas = curl_exec($curl);
            if($datas == false) {
                echo '接口无法链接';
            } else {
                $res = stripos($datas,'error_code');
                // 接收返回的数据
                $dataItem = json_decode($datas);
                if($res == false) {
                    // 数据正常
                    $items = $dataItem->items;
                    foreach($items as $item) {

                        switch($flag) {
                            case 'word':
                                if($item->pos == 'n' && !in_array($item->item,$keywords)){
                                    $keywords[] = $item->item;
                                }
                                break;
                            case 'tag':
                                if(!in_array($item->tag,$keywords)){
                                    $keywords[] = $item->tag;
                                }
                                break;
                            default:
                                if($item->pos == 'n' && !in_array($item->item,$keywords)){
                                    $keywords[] = $item->item;
                                }
                        }
                    }
                } else {
                    // 接口正常但获取数据失败，可能参数错误，重新获取token
                    $url = 'https://aip.baidubce.com/oauth/2.0/token';
                    $post_data['grant_type']     = $config['baidufenci']['grant_type'];;
                    $post_data['client_id']      = $config['baidufenci']['client_id'];
                    $post_data['client_secret']  = $config['baidufenci']['client_secret'];

                    $o = "";
                    foreach ( $post_data as $k => $v )
                    {
                        $o.= "$k=" . urlencode( $v ). "&" ;
                    }
                    $post_data = substr($o,0,-1);
                    $res = $this->request_post($url, $post_data);

                    // 保存token
                    $conf['baidufenci']['value']['access_token'] = json_decode($res)->access_token;
                    set_addons_config('seo', $conf);

                    echo 'api接口数据错误 - ';
                    echo $dataItem->error_msg;
                }
            }
        }
        return $keywords;
    }

    //	api_post接口
    function request_post($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        $curl = curl_init();//初始化curl
        curl_setopt($curl, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($curl, CURLOPT_HEADER, 0);//设置header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_POST, 1);//post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($curl);//运行curl
        curl_close($curl);
        return $data;
    }

    /**
     * 标题调用百度关键词词条
     *
     * @return Json
     */
    public function getBdiduSearchWordList($words)
    {
        if(empty($words)) return json(['code'=>-1,'msg'=>'null']);
        $url = 'https://www.baidu.com/sugrec?prod=pc&from=pc_web&wd='.$words;
        //$result = Api::urlGet($url);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $datas = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($datas,true);
        if(isset($data['g'])) {
            return json(['code'=>0,'msg'=>'success','data'=>$data['g']]);
        } else {
            return json(['code'=>-1,'msg'=>'null']);
        }

    }

    /**
     * baidu push api
     *
     * @param string $link
     * @return void
     */
    public function baiduPushUrl(string $link)
    {
        // seo插件配置
        $addon = get_addons_instance('seo');
        $config = $addon->getConfig();
        // baidu 接口
        $api = $config['baidupush']['push_api'];
        if(!empty($api)) {
            $url[] = $link;
            $ch = curl_init();
            $options =  array(
                CURLOPT_URL => $api,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => implode("\n", $url),
                CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
            );
            curl_setopt_array($ch, $options);
            curl_exec($ch);
            curl_close($ch);
        }

    }

    /**
     * 上传接口
     *
     * @return void
     */
    public function uploadFiles($type)
    {
        $uploads = new Uploads();
        switch ($type){
            case 'image':
                $upRes = $uploads->put('file','article_pic',2048,'image');
                break;
            case 'zip':
                $upRes = $uploads->put('file','article_zip',1024,'application|image');
                break;
            case 'video':
                $upRes = $uploads->put('file','article_video',102400,'video|audio');
                break;
            case 'audio':
                $upRes = $uploads->put('file','article_audio',102400,'audio');
                break;
            default:
                $upRes = $uploads->put('file','article_file',2048,'image');
                break;
        }
        return $upRes;
    }

    //获取artcile内容所有图片，返回数组
    protected function getArticleAllpic($str)
    {
        //正则匹配<img src="http://img.com" />
        $pattern = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";
        preg_match_all($pattern,$str,$matchContent);
        if(isset($matchContent[1])){
            $img = $matchContent[1];
        }else{
            $temp = "./images/no-image.jpg";//在相应位置放置一张命名为no-image的jpg图片
        }

        return $img;

    }


    //下载远程图片
    private function downloadImage($url, $userId = 1)
    {
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'GET' );
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        return $this->saveAsImage($url, $file, $userId);

    }

    //把图片保存到本地
    private function saveAsImage($url, $file, $userId = 1)
    {
        $filename = pathinfo($url, PATHINFO_BASENAME);
        //$dirname = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_DIRNAME);

        $uid = session('user_id') ?: $userId;
        //路径
        $path =  'storage/' . $uid . '/article_pic/' . date('Ymd',time()) . '/';
        //绝对文件夹
        $fileDir = public_path() . $path;
        //文件绝对路径
        $filePath =  $fileDir . $filename;
        //相对路径文件名
        $realFile = '/' . $path . $filename;
        // 如果目录不存在，则创建

        if(!is_dir($fileDir)) {
            mkdir($fileDir, 0777, true);
        }

        if(file_exists($filePath)) {
            //$this->output->writeln("【已存在】输出路径" . $fullpath);
            return $realFile;

        } else {
            $resource = fopen($filePath, 'a');
            $res = fwrite($resource, $file);
            fclose($resource);
            if($res !== false) {
                return $realFile;
            }
        }
    }

    //下载网络图片到本地并替换
    public function downUrlPicsReaplace($content, $userId = 1)
    {
        $this->content = $content;
        // 批量下载网络图片并替换
        $images = $this->getArticleAllpic($this->content);
        if(count($images)) {
            foreach($images as $image){
                //1.带http地址的图片，2.非本站的网络图片 3.非带有？号等参数的图片
                if((stripos($image,'http') !== false) && (stripos($image, Request::domain()) === false) && (stripos($image, '?') === false)) {
                    // 如果图片中没有带参数或者加密可下载
                    //下载远程图片(可下载)
                    $newImageUrl = $this->downloadImage($image, $userId);
                    //替换图片链接
                    $this->content = str_replace($image,Request::domain().$newImageUrl,$this->content);
                }
            }
            //不可下载的图片，如加密或者带有参数的图片如？type=jpeg,直接返回content
        }

        return $this->content;
    }

}