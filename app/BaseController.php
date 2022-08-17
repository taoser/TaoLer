<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;
use think\Response;
use think\exception\HttpResponseException;
use think\facade\Db;
use think\facade\Request;
use think\facade\Lang;
use think\facade\View;
use taoser\SetArr;
use app\common\lib\Uploads;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {}

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
    * 操作错误跳转
    * @param  mixed   $msg 提示信息
    * @param  string  $url 跳转的URL地址
    * @param  mixed   $data 返回的数据
    * @param  integer $wait 跳转等待时间
    * @param  array   $header 发送的Header信息
    * @return void
    */
    protected function error($msg = '', string $url = null, $data = '', int $wait = 3, array $header = []): Response
    {
        if (is_null($url)) {
            $url = request()->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app('route')->buildUrl($url);
        }

        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = (request()->isJson() || request()->isAjax()) ? 'json' : 'html';
        if ('html' == strtolower($type)) {
            $type = 'jump';
        }

        $response = Response::create($result, $type)->header($header)->options(['jump_template' => app('config')->get('app.dispatch_error_tmpl')]);

        throw new HttpResponseException($response);
    }

    /**
     * 返回封装后的API数据到客户端
     * @param  mixed   $data 要返回的数据
     * @param  integer $code 返回的code
     * @param  mixed   $msg 提示信息
     * @param  string  $type 返回数据格式
     * @param  array   $header 发送的Header信息
     * @return Response
     */
    protected function result($data, int $code = 0, $msg = '', string $type = '', array $header = []): Response
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => time(),
            'data' => $data,
        ];

        $type     = $type ?: 'json';
        $response = Response::create($result, $type)->header($header);

        throw new HttpResponseException($response);
    }

    /**
     * 操作成功跳转
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function success($msg = '', string $url = null, $data = '', int $wait = 3, array $header = []): Response
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app('route')->buildUrl($url);
        }

        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = (request()->isJson() || request()->isAjax()) ? 'json' : 'html';
        // 把跳转模板的渲染下沉，这样在 response_send 行为里通过getData()获得的数据是一致性的格式
        if ('html' == strtolower($type)) {
            $type = 'jump';
        }

        $response = Response::create($result, $type)->header($header)->options(['jump_template' => app('config')->get('app.dispatch_success_tmpl')]);

        throw new HttpResponseException($response);
    }


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
	
	//得到当前系统安装前台域名
	protected function getIndexUrl()
	{
		$sys = $this->getSystem();
		$domain = $this->getHttpUrl($sys['domain']);
		$syscy = $sys['clevel'] ? Lang::get('Authorized') : Lang::get('Free version');
        $runTime = $this->getRunTime();
		View::assign(['domain'=>$domain,'insurl'=>$sys['domain'],'syscy'=>$syscy,'clevel'=>$sys['clevel'],'runTime'=>$runTime]);
        return $domain;
	}

	protected function getRunTime()
    {
        //运行时间
        $now = time();
        $sys = $this->getSystem();
        $count = $now-$sys['create_time'];
        $days = floor($count/86400);
        $hos = floor(($count%86400)/3600);
        $mins = floor(($count%3600)/60);
        $years = floor($days/365);
        if($years >= 1){
            $days = floor($days%365);
        }
        $runTime = $years ? "{$years}年{$days}天{$hos}时{$mins}分" : "{$days}天{$hos}时{$mins}分";
        return $runTime;
    }

    /**
     * 获取文章链接地址
     *
     * @param integer $aid
     * @return string
     */
    protected function getRouteUrl(int $aid,string $ename = '') : string
    {
        $indexUrl = $this->getIndexUrl();
        if(config('taoler.url_rewrite.article_as') == '<ename>/'){
            // 分类可变路由
            $artUrl = (string) url('article_detail', ['id' => (int) $aid, 'ename'=> $ename]);
            //$artUrl = (string) Route::buildUrl('article_detail', ['id' => $aid, 'ename'=> $ename]);
        } else {
            $artUrl = (string) url('article_detail', ['id' => $aid]);
        }
        //dump($artUrl);

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

    /**
	 * 关键词
     * 通过百度分词接口获取关键词或者标签
     * flag 1.为word时获取分词，2.为tag时获取标签
	 *
	 * @return void
	 */
    public function setKeywords($data)
    {
		$keywords = [];
        // 百度分词自动生成关键词
        if(!empty(config('taoler.baidu.client_id')) == true) {
            //headers数组内的格式
            $headers = array();
            $headers[] = "Content-Type:application/json";

            switch($data['flag']) {
                //分词
                case 'word':
                    $url = 'https://aip.baidubce.com/rpc/2.0/nlp/v1/lexer?charset=UTF-8&access_token='.config('taoler.baidu.access_token');
                    $body = ["text" => $data['keywords']];
                    break;
                //标签
                case 'tag':
                    $url = 'https://aip.baidubce.com/rpc/2.0/nlp/v1/keyword?charset=UTF-8&access_token='.config('taoler.baidu.access_token');
                    $body = ['title' => $data['keywords'], 'content'=>$data['content']];
                    break;
                default:
                    $url = 'https://aip.baidubce.com/rpc/2.0/nlp/v1/lexer?charset=UTF-8&access_token='.config('taoler.baidu.access_token');
                    $body = ["text" => $data['keywords']];
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
                        
                        switch($data['flag']) {
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
                    $post_data['grant_type']     = config('taoler.baidu.grant_type');;
                    $post_data['client_id']      = config('taoler.baidu.client_id');
                    $post_data['client_secret']  = config('taoler.baidu.client_secret');

                    $o = "";
                    foreach ( $post_data as $k => $v ) 
                    {
                        $o.= "$k=" . urlencode( $v ). "&" ;
                    }
                    $post_data = substr($o,0,-1);
                    $res = $this->request_post($url, $post_data);
                    // 写入token
                    SetArr::name('taoler')->edit([
                        'baidu'=> [
                            'access_token'	=> json_decode($res)->access_token,
                        ]
                    ]);
                    echo 'api接口数据错误 - ';
                    echo $dataItem->error_msg; 
                }
            }
        }
        return json(['code'=>0,'data'=>$keywords]);
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
	 * @return void
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
	protected function baiduPushUrl(string $link)
	{
		// baidu 接口
		$api = config('taoler.baidu.push_api');
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
                $upRes = $uploads->put('file','article_pic',1024,'image');
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
                $upRes = $uploads->put('file','article_file',1024,'image');
                break;
        }
        return $upRes;
    }

    //获取artcile所有图片
	protected function getArticleAllpic($str)
	{
		// <img src="http://img.com" />
		$pattern = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";
		preg_match_all($pattern,$str,$matchContent);
		if(isset($matchContent[1])){
			$img = $matchContent[1];
		}else{
			$temp = "./images/no-image.jpg";//在相应位置放置一张命名为no-image的jpg图片
		}
		
		return $img;

	}


    //下载图片
	private function downloadImage($url)
	{
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'GET' );  
    	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false ); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		$file = curl_exec($ch);
		curl_close($ch);
		return $this->saveAsImage($url, $file);

	}

	//保存图片
	private function saveAsImage($url, $file)
	{
		$filename = pathinfo($url, PATHINFO_BASENAME);
		//$dirname = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_DIRNAME);
		$dirname = date('Ymd',time());
		//路径
		$path =  'storage/' . $this->uid . '/article_pic/' . $dirname . '/';
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
    public function downUrlPicsReaplace($content)
	{
		// 批量下载网络图片并替换
		$images = $this->getArticleAllpic($content);
		if(count($images)) {
			foreach($images as $image){
				//1.网络图片
				//halt((stripos($image, Request::domain()) === false));
				if((stripos($image,'http') !== false) && (stripos($image, Request::domain()) === false)) { 
					
					//2.下载远程图片
					$newImageUrl = $this->downloadImage($image);
					
					$content = str_replace($image,Request::domain().$newImageUrl,$content);
	
				}
			}
		}
		
		return $content;
	}



}
