<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\response\Json;
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

    /**
     * 运行时间计算
     * @return string
     */
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
     * 非admin应用的文章url路由地址
     * @param int $aid
     * @param $ename
     * @return string
     */
    protected function getRouteUrl(int $aid, $ename = '')
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
	 * 上传接口
	 *
	 * @return void
	 */
	public function uploadFiles($type)
    {
        $max_file_seze = $this->getSystem()['upsize'];
        $uploads = new Uploads();
        switch ($type){
            case 'image':
                $upRes = $uploads->put('file','article_pic',$max_file_seze,'image');
                break;
            case 'zip':
                $upRes = $uploads->put('file','article_zip',$max_file_seze,'application|image');
                break;
            case 'video':
                $upRes = $uploads->put('file','article_video',$max_file_seze,'video|audio');
                break;
            case 'audio':
                $upRes = $uploads->put('file','article_audio',$max_file_seze,'audio');
                break;
            default:
                $upRes = $uploads->put('file','article_file',$max_file_seze,'image');
                break;
        }
        return $upRes;
    }

    //获取artcile内容所有图片，返回数组
	protected function getArticleAllpic($str)
	{
		//正则匹配<img src="http://img.com" />
		$pattern = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.jpeg]))[\'|\"].*?[\/]?>/";
		preg_match_all($pattern,$str,$matchContent);
		if(isset($matchContent[1])){
			$imgArr = $matchContent[1];
		}else{
			$temp = "./images/no-image.jpg";//在相应位置放置一张命名为no-image的jpg图片
		}
	
		return $imgArr;
	}

    //下载远程图片
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

	//把图片保存到本地
	private function saveAsImage($url, $file)
	{
		$filename = pathinfo($url, PATHINFO_BASENAME);
		//$dirname = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_DIRNAME);

		//路径
		$path =  'storage/download/article_pic/' . date('Ymd',time()) . '/';
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
				//1.带http地址的图片，2.非本站的网络图片 3.非带有？号等参数的图片
				if((stripos($image,'http') !== false) && (stripos($image, Request::domain()) == false) && (stripos($image, '?') == false)) { 
                    // 如果图片中没有带参数或者加密可下载
                    //下载远程图片(可下载)
                    $newImageUrl = $this->downloadImage($image);
                    //替换图片链接
                    $content = str_replace($image, Request::domain().$newImageUrl, $content);
				}
			}
            //不可下载的图片，如加密或者带有参数的图片如？type=jpeg,直接返回content
		}
				
		return $content;
	}

    /**
     * array_filter过滤返回函数
     * @param $arr
     * @return bool
     */
    protected function  filter($arr) :bool
    {
        if($arr === '' || $arr === null){
            return false;
        }
        return true;
    }

    /**
     * 过滤为空null等筛选参数
     * @param array $array
     * @return array
     */
    public function getParamFilter(array $array) :array
    {
        return array_filter($array, function($arr){
            if($arr === '' || $arr === null){
                return false;
            }
            return true;
        });
    }

    /**
     * 数组根据sort字段数值进行排序
     * @param array $array 数组
     * @param string $sort 排序字段
     * @return array 返回数组
     */
    public function getArrSort(array $array, string $sort = 'sort') :array
    {
        // 排序
        $cmf_arr = array_column($array, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $array);

        return $array;
    }

    /**
     * 过滤字符串中表情
     * @param $str string 字符串内容
     * @return string
     */
    public function filterEmoji(string $str): string
    {
        $str = preg_replace_callback('/./u', function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        }, $str);
        return $str;
    }

}
