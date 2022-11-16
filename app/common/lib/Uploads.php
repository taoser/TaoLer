<?php
namespace app\common\lib;

use think\File;
use think\facade\Config;
use think\facade\Filesystem;
use think\exception\ValidateException;
use think\facade\Db;

class Uploads 
{
    protected $fileName;
    protected $dirName;
    protected $fileSize;
	protected $fileType;


    /**
     * 根据后台设置的文件类型，获取允许上传文件的mime和后缀
     * @param string $fileType 上传文件的类型只能为：视频video，文件application，图片image,文本text
     * @param string $type 获取文件的mime还是文件后缀ext
     * @return array
     */
    protected function getFileInfo(string $fileType,string $type)
    {
        $extType = Db::name('system')->where('id',1)->value('uptype');
        $extArr = explode(',',$extType);
        //查询系统配置中运行上传文件类型，组成新的数组
        $fileMime = [];
        foreach ($extArr as $k=>$v){
            $k = stristr($v,':',true);//返回字符:前字符串
            $fileMime[$k] = explode('|',substr($v,strrpos($v,":")+1)); //返回:后字符串转换为数组
        }
        //根据上传文件的类型得到允许的文件mime或后缀
        $arr = [];
        $fileTypeArr = explode('|',$fileType);
        foreach($fileTypeArr as $ft){
            if(array_key_exists($ft,$fileMime)){   //数组中是否存在上传的数组的键

                foreach($fileMime as $k=>$v){
                    if($ft == $k){//文件类型和配置中类型相同时
                        //获取文件mime
                        if($type == 'mime'){
                            //拼接字符串组成新mime数组
                            foreach($v as $m){
                                $arr[] = $ft. '/' . $m;
                            }
                        }
                        //获取文件后缀
                        if($type == 'ext'){
                            $arr =  array_merge($arr,$v);   //合并数组
                        }
                    }
                }

            }
        }
        return $arr;
    }
	

    /**
     * 上传文件
     * 1.可以指定目录（若目录前有SYS_前缀，会上传到public/sys系统目录，不指定前缀，会上传到public/storage/用户ID）
     * 2.命名规则 可定义文件名类型a.jpg,b.php，或使用sha1,uniqid,md5函数
     * 
     * @param string $fileName 文件名,form表单中的name
     * @param string $dirName 文件夹名,上传路径中的文件夹名称,sys_前缀存放在/public/sys系统目录
     * @param int $fileSize 文件大小，上传限制大小
     * @param string $fileType 文件类型，只能为：视频video，文件application，图片image,文本text
     * @param string $rule 文件命名规则，默认md5,uniqid,date,sha1，为空则取文件上传名称，或者自定义如a.jpg文件名
     * @return \think\response\Json
     */
    public function putFile(string $fileName, string $dirName, int $fileSize, string $fileType, string $rule = '')
    {
        if(stripos($fileName,'http') !== false) {
            $file = $fileName;
        } else {
            $file = request()->file($fileName);
        }
		
        //halt($file->getOriginalName());
		//$type = $file->getMime();
		$fileExt = $this->getFileInfo($fileType,'ext');
		$fileMime = $this->getFileInfo($fileType,'mime');

		try {
			validate([$fileName=>['fileSize'=>$fileSize * 1024,'fileExt'=>$fileExt,'fileMime'=>$fileMime]])
            ->check(['file'=>$file]);
			
		} catch (ValidateException $e) {
			return json(['status'=>-1,'msg'=>$e->getMessage()]);
		}
        // 解析存储位置 SYS_开头为系统位置
        $isSys = stripos($dirName, 'SYS_');
        if($isSys !== false) {
            $disk = 'sys';
            $dirName = substr($dirName,4);
            $uploadDir = Config::get('filesystem.disks.sys.url');
        } else {
            $disk = 'public';
            $dirName = session('user_id'). '/' .$dirName;
            $uploadDir = Config::get('filesystem.disks.public.url');
        }
        $rules = ['md5','date','sha1','uniqid'];
        // 解析是否自定义文件名
        if(!in_array($rule, $rules) && !is_null($rule)) {
            if(stripos($rule, '.') == false) {
                $rule = $file->getOriginalName();
            }
            $savename = Filesystem::disk($disk)->putFileAs($dirName, $file, $rule);
        } else {
            $savename = Filesystem::disk($disk)->putFile($dirName, $file, $rule);
        }		

		if($savename){
            //$name = $file->hashName();
            $name_path =str_replace('\\',"/", $uploadDir . '/' . $savename);
			//$image = \think\Image::open("uploads/$name_path");
			//$image->thumb(168, 168)->save("uploads/$name_path");

            $res = ['status'=>0,'msg'=>'上传成功','url'=> $name_path, 'location'=>$name_path];
        }else{
            $res = ['status'=>-1,'msg'=>'上传错误'];
        }

	    return json($res);
    }


    /**
     * 上传文件
     * @param string $fileName 文件名
     * @param string $dirName 目录名
     * @param int $fileSize 文件大小
     * @param string $fileType 文件类型
     * @param string $rule 文件命名规则 默认md5,uniqid,date,sha1，_self为上传文件名称作为文件名，或者自定义如a.jpg文件名
     * @return \think\response\Json
     */
    public function put(string $fileName, string $dirName, int $fileSize, string $fileType, string $rule = '')
    {
        if(stripos($fileName,'http') !== false) {
            $file = $fileName;
        } else {
            $file = request()->file($fileName);
        }

        //halt($file->getOriginalName());
        //$type = $file->getMime();
        $fileExt = $this->getFileInfo($fileType,'ext');
        $fileMime = $this->getFileInfo($fileType,'mime');

        try {
            validate([$fileName=>['fileSize'=>$fileSize * 1024,'fileExt'=>$fileExt,'fileMime'=>$fileMime]])
                ->check(['file'=>$file]);

        } catch (ValidateException $e) {
            return json(['status'=>-1,'msg'=>$e->getMessage()]);
        }

        // 解析存储位置 SYS_开头为系统位置
        $isSys = stripos($dirName, 'SYS_');
        if($isSys !== false) {
            $disk = 'sys';
            $dirName = substr($dirName,4);
            $uploadDir = Config::get('filesystem.disks.sys.url');
            $path = DIRECTORY_SEPARATOR . $disk . DIRECTORY_SEPARATOR . $dirName  . DIRECTORY_SEPARATOR . date('Ymd');
        } else {
            $disk = 'public';
            $dirName = session('user_id'). DIRECTORY_SEPARATOR . $dirName;
            $uploadDir = Config::get('filesystem.disks.public.url');
            $path = DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR . date('Ymd');
        }

        $realPath = app()->getRootPath() . 'public' . $path;

        $rules = ['md5','date','sha1','uniqid'];

        try{
            // 解析是否自定义文件名
            if(in_array($rule, $rules)) {
                // rule命名
                $info = $file->move($realPath, $file->hashName($rule));
            } elseif(!empty($rule)) {
                // 自定义文件名
                if(stripos($rule, '_self')) {
                    $info = $file->move($realPath, $file->getOriginalName());
                }
                $info = $file->move($realPath, $rule);
            } else {
                // 默认
                $info = $file->move($realPath, $file->hashName());
            }
        } catch (\Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }
//        halt($info->getBasename());
        $name_path = str_replace('\\',"/", $path . '/' . $info->getBasename());

        return json(['status'=>0,'msg'=>'上传成功','url'=> $name_path, 'location'=>$name_path]);
    }

}