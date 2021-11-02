<?php
namespace app\admin\controller;

use app\common\controller\AdminController;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use think\facade\Config;
use app\admin\model\Addons as AddonsModel;
use taoler\com\Files;
use taoler\com\Api;
use app\common\lib\SetConf;
use think\App;
use app\common\lib\ZipFile;

class Addons extends AdminController
{
    /**
     * @return string
     */
    public function index()
    {
		//$conf = new \addons\social\model\Conf;
		//$arr = $conf->getConf();
		//dump($arr);
//dump($arr[0]['value']['app_key']);
		return View::fetch();
    }
	
	 public function addonsList()
    {
       
		$type = input('type');
		$res = [];
        switch($type){
            //已安装
            case 'installed':
                $addons = Files::getDirName('../addons/');
                if($addons){
                    $res = ['code'=>0,'msg'=>'','count'=>5];
                        foreach($addons as $v){
                            $info_file = '../addons/'.$v.'/info.ini';
                            $info = parse_ini_file($info_file);
                            $res['data'][] = $info;
                        }
                    $res['col'] = [
                        ['type' => 'numbers'],
                        ['field' => 'name','title'=> '插件', 'width'=> 150],
                        ['field'=> 'title','title'=> '标题', 'width'=> 100],
                        ['field'=> 'version','title'=> '版本', 'width'=> 100],
                        ['field' => 'author','title'=> '作者', 'width'=> 100],
                        ['field' => 'description','title'=> '简介', 'minWidth'=> 200],
                        ['field' => 'status','title'=> '状态', 'width'=> 100],
                        ['field' => 'install','title'=> '安装', 'width'=> 100],
                        ['field' => 'ctime','title'=> '到期时间', 'width'=> 150],
                        ['title' => '操作', 'width'=> 220, 'align'=>'center', 'toolbar'=> '#addons-installed-tool']
                    ];
                } else {
					$res = ['code'=>-1,'msg'=>'没有安装任何插件'];
				}
                break;
            //在线
            case 'onlineAddons':
                $url = $this->getSystem()['api_url'].'/v1/addons';
                $addons = Api::urlPost($url,[]);
                if( $addons->code !== -1){
                    $res['code'] = 0;
                    $res['msg'] = '';
                    $res['data'] = $addons->data;
                    $res['col'] = [
                        ['type' => 'numbers'],
                        ['field' => 'name','title'=> '插件', 'width'=> 150],
                        ['field'=> 'title','title'=> '标题', 'width'=> 100],
                        ['field'=> 'version','title'=> '版本', 'width'=> 100],
                        ['field' => 'author','title'=> '作者', 'width'=> 100],
                        ['field' => 'description','title'=> '简介', 'minWidth'=> 200],
                        ['field' => 'price','title'=> '价格(元)'],
                        ['field' => 'status','title'=> '状态', 'width'=> 100],
                        ['field' => 'install','title'=> '安装', 'width'=> 100],
                        ['field' => 'ctime','title'=> '时间', 'width'=> 150],
                        ['title' => '操作', 'width'=> 150, 'align'=>'center', 'toolbar'=> '#addons-tool']
                    ];
                } else {
					$res = ['code'=>-1,'msg'=>''];
				}
                break;
        }
		return json($res);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function add()
    {
        //添加版本
		if(Request::isAjax()){
			$data = Request::param();
			$result = AddonsModel::create($data);
			if($result){
				$res = ['code'=>0,'msg'=>'添加成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'添加失败'];
			}
		return json($res);
		}
		
		return View::fetch();
    }


    /**
     * 编辑版本
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
		$addons = AddonsModel::find($id);

		if(Request::isAjax()){
			$data = Request::only(['id','addons_name','addons_version','addons_auther','addons_resume','addons_price','addons_src']);
			$result = $addons->where('id',$id)->save($data);
			if($result){
				$res = ['code'=>0,'msg'=>'编辑成功'];
			}else{
				$res = ['code'=>-1,'msg'=>'编辑失败'];
			}
			return json($res);
		}
		View::assign('addons',$addons);
		return View::fetch();
    }

    /**
     * 上传版本的zip资源
     *
     * @param
     * @param  int  $id
     * @return \think\Response
     */
    public function uploadZip()
    {
		$id = Request::param();
        $file = request()->file('file');
		try {
			validate(['file'=>'filesize:2048|fileExt:zip,rar,7z'])
            ->check(array($file));
			$savename = \think\facade\Filesystem::disk('public')->putFile('addons',$file);
		} catch (think\exception\ValidateException $e) {
			echo $e->getMessage();
		}
		$upload = Config::get('filesystem.disks.public.url');
		
		if($savename){
            $name_path =str_replace('\\',"/",$upload.'/'.$savename);
				$res = ['code'=>0,'msg'=>'插件上传成功','src'=>$name_path];
			} else {
				$res = ['code'=>-1,'msg'=>'上传错误'];
			}
		return json($res);
    }

    //安装插件
    public function install()
    {
        $data = Request::param();
        $url = $this->getSystem()['api_url'].'/v1/getaddons';
        $addons = Api::urlPost($url,['name'=>$data['name'],'version'=>$data['version']]);
        if( $addons->code == -1) {
            return json(['code'=>$addons->code,'msg'=>$addons->msg]);
        }
		//是否安装？
		$addInstalledVersion = get_addons_info($data['name']);
		if(!empty($addInstalledVersion)){
			$verRes = version_compare($data['version'],$addInstalledVersion['version'],'>');
	        if(!$verRes){
	            return json(['code'=>-1,'msg'=>'不能降级，请选择正确版本']);
	        }
		}

        $file_url = $addons->addons_src;
        //判断远程文件是否可用存在
        $header = get_headers($file_url, true);
        if(!isset($header[0]) && (strpos($header[0], '200') || strpos($header[0], '304'))){
            return json(['code'=>-1,'msg'=>'获取远程文件失败']);
        }

        //把远程文件放入本地

        //拼接路径
        $addons_dir = Files::getDirPath('../runtime/addons/');
        Files::mkdirs($addons_dir);

        $package_file = $addons_dir . $data['name'] .'.zip';  //升级的压缩包文件
//        halt($package_file);
        $cpfile = copy($file_url,$package_file);
        if(!$cpfile)
        {
            return json(['code'=>-1,'msg'=>'下载升级文件失败']);
        }

        $uzip = new ZipFile();
        $zipDir = strstr($package_file, '.zip',true);   //返回文件名后缀前的字符串
        $zipPath = Files::getDirPath($zipDir);  //转换为带/的路径 压缩文件解压到的路径
        $unzip_res = $uzip->unzip($package_file,$zipPath,true);

        if(!$unzip_res)
        {
            return json(['code'=>-1,'msg'=>'解压失败']);
        }

        //升级插件
        if(is_dir($zipPath))
        {
            //升级前的写入文件权限检查
            $allUpdateFiles = Files::getAllFile($zipPath);

            if (empty($allUpdateFiles)) return json(['code' => -1, 'msg' => '无可更新文件。']);
            $checkString    = '';
            foreach ($allUpdateFiles as $updateFile) {
                $coverFile  = ltrim(str_replace($zipPath, '', $updateFile), DIRECTORY_SEPARATOR);
                $dirPath    = dirname('../'.$coverFile);
                if (file_exists('../'.$coverFile)) {
                    if (!is_writable('../'.$coverFile)) $checkString .= $coverFile . '&nbsp;[<span class="text-red">' . '无写入权限' . '</span>]<br>';
                } else {
                    if (!is_dir($dirPath)) @mkdir($dirPath, 0777, true);
                    if (!is_writable($dirPath)) $checkString .= $dirPath . '&nbsp;[<span class="text-red">' . '无写入权限' . '</span>]<br>';
                }
            }

            if (!empty($checkString)) return json(['code' => -1, 'msg' => $checkString]);
            $addonsPath = '../';
            $cpRes = Files::copyDirs($zipPath,$addonsPath);
            $cpData = $cpRes->getData();
            //更新失败
            if($cpData['code'] == -1)
            {
                return json(['code'=>-1,'msg'=>$cpData['msg']]);
            }

            //清除
            Files::delDirAndFile('../runtime/addons/');

        }
		 return json(['code'=>0,'msg'=>'插件安装成功！']);

    }
    /**
     * 卸载插件
     */
    public function delete()
    {
        $name = input('name');
        $addonsPath = '../addons/'.$name;
        $staticPath = 'addons/'.$name;

        if (is_dir($staticPath)) {
            Files::delDir($staticPath);
        }
        $res = Files::delDir($addonsPath);
		if($res){
			return json(['code'=>0,'msg'=>'卸载成功']);
		} else {
			return json(['code'=>-1,'msg'=>'卸载失败']);
		}
    }
	
	//启用插件
	public function start()
	{
		$name = input('name');
		$arr = ['status' => 1];
		//$res = get_addons_info($name);
		//$res = get_addons_instance($name);
		$res = set_addons_info($name,$arr);
		return json(['code'=>0,'msg'=>$name.'插件已启用']);
		
	}
	
	//暂停插件
	public function shutDown()
	{
		$name = input('name');
		$arr = ['status' => 0];
		$res = set_addons_info($name,$arr);
		var_dump($res);
		return json(['code'=>-1,'msg'=>$name.'插件已禁用']);
		
	}
	
	//配置插件
	public function config($name)
	{
		$name = input('name');
		//var_dump($name);
		$config = get_addons_config($name);
		if(empty($config)) return json(['code'=>-1,'msg'=>'无配置项！']);
		if(Request::isAjax()){
			$params = Request::param('params/a');
			if ($params) {
                foreach ($config as $k => &$v) {
                    if (isset($params[$k])) {
                        if ($v['type'] == 'array') {
                            $arr = [];
                            $params[$k] = is_array($params[$k]) ? $params[$k] :[];
                            foreach ($params[$k]['key'] as $kk=>$vv){
                                $arr[$vv] =  $params[$k]['value'][$kk];
                            }
                            $params[$k] = $arr;
                            $value = $params[$k];
                            $v['content'] = $value;
                            $v['value'] = $value;
                        } else {
                            $value =  $params[$k];
                        }
                        $v['value'] = $value;
                    }
                }
                unset($v);
                set_addons_config($name,$config);
   
            }
			return json(['code'=>0,'msg'=>'配置成功！']);
		}

		//模板引擎初始化
        $view = ['formData'=>$config,'title'=>'title'];
		View::assign($view);
		return View::fetch();
		
	}
}
