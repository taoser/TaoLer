<?php
namespace app\index\controller;


use app\common\helper\UploadHelper;
use think\facade\Request;
use think\facade\View;

class Upload extends IndexBaseController
{
    protected $uploadHelper;

    public function initialize()
    {
        parent::initialize();
        $this->uploadHelper = new UploadHelper();
    }
    public function index()
    {
        return View::fetch('upl');
    }

    public function chunk()
    {
        $file = Request::file('file');
        $fileId = Request::post('fileId', '', 'trim');
        $index  = Request::post('index', 0, 'intval');
        $this->uploadHelper->chunk($file, $fileId, $index);
        // return $this->success('分片上传成功');
        return json(['code' => 0, 'msg' => '分片上传成功']);
    }

    public function merge()
    {
        $fileId = Request::post('fileId', '', 'trim');
        $fileName = Request::post('fileName', '', 'trim');
        $total    = Request::post('total', 0, 'intval');
        $url = $this->uploadHelper->merge($fileId, $fileName, $total);
        // return $this->success('合并成功', $url);
        return json(['code' => 0, 'msg' => '合并成功', 'url' => $url]);
    }

    public function getUploadedChunk()
    {
        $fileId = Request::post('fileId', '', 'trim');
        $uploaded = $this->uploadHelper->getUploadedChunk($fileId);
        return json(['code' => 0, 'msg' => '获取成功', 'data' => $uploaded]);
    }

    public function cancelUpload()
    {
        $fileId = Request::post('fileId', '', 'trim');
        $this->uploadHelper->cancelUpload($fileId);
        return json(['code' => 0, 'msg' => '取消上传成功']);
    }
}