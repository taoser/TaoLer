<?php
namespace app\admin\controller;

header('Content-Type:application/json; charset=utf-8');

use app\common\controller\AdminController;
use think\facade\View;
use AngularFilemanager\LocalBridge\FileManagerApi;
use AngularFilemanager\LocalBridge\Rest;
use taoler\com\Api;



include '../extend/AngularFilemanager/LocalBridge/Response.php';
include '../extend/AngularFilemanager/LocalBridge/Rest.php';
include '../extend/AngularFilemanager/LocalBridge/Translate.php';
include '../extend/AngularFilemanager/LocalBridge/FileManagerApi.php';


class FileManager extends AdminController
{
	public function index()
	{
		
		return View::fetch();
	}
	
	public function handler()
	{
		$fileManagerApi = new FileManagerApi();

		$rest = new Rest();
		$rest->post([$fileManagerApi, 'postHandler'])
		     ->get([$fileManagerApi, 'getHandler'])
		     ->handle();
	}
	
	public function data()
	{
		
		
		
		
		$url = 'http://api.aieok.com/v1/handler';
/*		
		$data = json([
		    'action'=>'list',
		    'path'=> '/'
		])->header([
		    'Cache-control' => 'no-cache,must-revalidate',
		    'Content-Type' => 'application/json'
		]);
		
*/
		$datas = [
		    'action'=>'list',
		    'path'=> '/'
		];
		$jsonStr = json_encode($datas);
		list($returnCode, $returnContent) = $this->http_post_json($url, $jsonStr);
		
		if($returnCode == 200){
			$res = trim($returnContent,'"');
			return json_decode($res);
		}
		

		
		//var_dump($data);
		//$apiRes = Api::urlPost($url,$data);
		//var_dump($apiRes);
		
		//return $apiRes;
		
		
	    //$path = app()->getRootPath();
	   // var_dump($path);
		//Listing (URL: fileManagerConfig.listUrl, Method: POST)
		
	/*	
		$fileManagerApi = new FileManagerApi();
		$file = $fileManagerApi->listActionData('/');

		$data['result'] = $file;
		
		
	*/	
		//$rest = new Rest('post');
		//$rest->post([$fileManagerApi, 'postHandler'])
		//     ->get([$fileManagerApi, 'getHandler'])
		//     ->handle();
		     
		//$res = $fileManagerApi->postHandler([],['action'=>'list', 'path'=>'../'],'');
		//$rest = new Rest();
		//$rest->post([$fileManagerApi, 'postHandler'])
		//->handle();

		
	}
	
	
	public function http_post_json($url, $jsonStr)
	{
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 20);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	            'Content-Type: application/json; charset=utf-8',
	            
	        )
	    );
	    curl_setopt($ch, CURLOPT_HEADER, 0);           // 显示返回的Header区域内容
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
	    $response = curl_exec($ch);
	    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    curl_close($ch);
	 
	    return array($httpCode,$response);
	}
}


