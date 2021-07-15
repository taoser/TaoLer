<?php
namespace app\common\lib;

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
     * 获取上传文件的mime和后缀
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
	
	//上传文件

    /**
     * 上传文件
     * @param string $fileName 文件名,form表单中的name
     * @param string $dirName
     * @param int $fileSize
     * @param string $fileType
     * @return \think\response\Json
     */

    /**
     * @param string $fileName 文件名,form表单中的name
     * @param string $dirName 文件夹名,上传路径中的文件夹名称
     * @param int $fileSize 文件大小，上传限制大小
     * @param string $fileType 文件类型，只能为：视频video，文件application，图片image,文本text
     * @param string $rule 文件命名规则，默认md5,uniqid,date,sha1
     * @return \think\response\Json
     */
    public function put(string $fileName, string $dirName, int $fileSize, string $fileType, string $rule = null)
    {
		$file = request()->file($fileName);
		//$type = $file->getMime();
		$fileExt = $this->getFileInfo($fileType,'ext');
		$fileMime = $this->getFileInfo($fileType,'mime');

		try {
			validate([$fileName=>['fileSize'=>$fileSize * 1024,'fileExt'=>$fileExt,'fileMime'=>$fileMime]])
            ->check(['file'=>$file]);
			
		} catch (ValidateException $e) {
			return json(['status'=>-1,'msg'=>$e->getMessage()]);
		}
		
		$savename = \think\facade\Filesystem::disk('public')->putFile(session('user_id'). '/' .$dirName, $file, $rule);
		$upload = Config::get('filesystem.disks.public.url');

		if($savename){
            //$name = $file->hashName();
            $name_path =str_replace('\\',"/",$upload.'/'.$savename);
			//halt($name_path);
			//$image = \think\Image::open("uploads/$name_path");
			//$image->thumb(168, 168)->save("uploads/$name_path");

            $res = ['status'=>0,'msg'=>'上传成功','url'=> $name_path];
        }else{
            $res = ['status'=>-1,'msg'=>'上传错误'];
        }
	return json($res);
    }

}