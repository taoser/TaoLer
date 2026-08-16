<?php
namespace app\common\helper;

use Exception;
use think\facade\Filesystem;
use think\Request;


class Uploader
{
    // 允许后缀白名单
    protected $allowExt  = ['mp4','avi','mov','zip','rar','7z','pdf','jpg','jpeg','png','gif'];
    // 禁止可执行脚本后缀
    protected $denyExt   = ['php','php5','phtml','exe','sh','bat','py','asp','jsp','cgi'];
    // 单文件最大10GB
    protected $maxFileSize = 10 * 1024 * 1024 * 1024;
    // 单分片最大10M
    protected $maxChunkSize = 10 * 1024 * 1024;



    // 接收分片
    public function chunk($file, $fileId, $index)
    {
        // $file   = Request::file('file');
        // $fileId = Request::post('fileId', '', 'trim');
        // $index  = Request::post('index', '', 'intval');

        // 1. 参数安全校验
        if (empty($fileId) || preg_match('/[^\w_]/', $fileId)) {
            throw new Exception('非法文件标识');
        }
        if ($index < 0) {
            throw new Exception('分片序号错误');

        }
        if (!$file) {
            throw new Exception('未上传分片文件');
        }

        // 2. 分片大小限制
        if ($file->getSize() > $this->maxChunkSize) {
            throw new Exception('分片超出10M限制');
        }

        // 保存分片
        $chunkPath = "chunk/{$fileId}/";
        Filesystem::disk('public')->putFileAs($chunkPath, $file, $index);

        return true;
    }

    /**
     * 合并分片 + 完整安全校验
     * @param string $fileId 文件ID
     * @param string $fileName 文件名
     * @param int $total 分片总数
     * @return string 合并后的文件URL
     */
    public function merge(string $fileId, string $fileName, int $total): string
    {
        // $fileId   = Request::post('fileId', '', 'trim');
        // $fileName = Request::post('fileName', '', 'trim');
        // $total    = (int)Request::post('total');

        // 1. 参数基础校验
        if (empty($fileId) || preg_match('/[^\w_]/', $fileId)) {
            throw new Exception('非法文件ID');
        }
        if (empty($fileName) || $total <= 0) {
            throw new Exception('参数缺失');
        }
        // 拦截路径穿越字符 ../ / \ %00
        if (preg_match('/(\.\.|\/|\\|\%00)/', $fileName)) {
            $this->clearChunk($fileId);
            throw new Exception('文件名包含非法字符');
        }

        // 2. 后缀黑白名单校验
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (in_array($fileExt, $this->denyExt)) {
            $this->clearChunk($fileId);
            throw new Exception('禁止上传可执行文件');
        }
        if (!in_array($fileExt, $this->allowExt)) {
            $this->clearChunk($fileId);
            throw new Exception('不支持该文件类型');
        }

        // 3. 校验分片完整性
        $chunkDir = public_path("storage/chunk/{$fileId}/");
        if (!is_dir($chunkDir)) {
            throw new Exception('分片目录不存在');
        }
        for ($i = 0; $i < $total; $i++) {
            if (!file_exists($chunkDir . $i)) {
                $this->clearChunk($fileId);
                throw new Exception("分片{$i}丢失");
            }
        }

        // 4. 流式合并文件，同时校验总大小
        $saveDir = public_path("storage/upload/");
        is_dir($saveDir) || mkdir($saveDir, 0755, true);
        // 随机重命名，杜绝覆盖、XSS
        $newFileName = date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $fileExt;
        $targetFile  = $saveDir . $newFileName;
        $handle      = fopen($targetFile, 'ab');
        $totalFileSize = 0;

        for ($i = 0; $i < $total; $i++) {
            $chunkFile = $chunkDir . $i;
            $chunkData = file_get_contents($chunkFile);
            $chunkSize = strlen($chunkData);
            $totalFileSize += $chunkSize;

            // 总文件大小超限拦截
            if ($totalFileSize > $this->maxFileSize) {
                fclose($handle);
                unlink($targetFile);
                $this->clearChunk($fileId);
                throw new Exception('文件超出最大10GB限制');
            }
            fwrite($handle, $chunkData);
            unlink($chunkFile);
        }
        fclose($handle);
        rmdir($chunkDir);

        // 5. 文件二进制头校验，防止改后缀木马
        if (!$this->checkFileHeader($targetFile, $fileExt)) {
            unlink($targetFile);
            throw new Exception('文件格式伪造，上传失败');
        }

        $url = "/storage/upload/" . $newFileName;

        return $url;
    }

    /**
     * 获取已上传分片列表（续传用）
     * @param string $fileId 文件ID
     * @return array 已上传分片序号列表 为空数组表示未上传任何分片
     */
    public function getUploadedChunk($fileId): array
    {
        // 非法标识校验
        if(empty($fileId) || preg_match('/[^\w_]/',$fileId)){
            return [];
        }
        $chunkDir = public_path("storage/chunk/{$fileId}/");
        if(!is_dir($chunkDir)){
            return [];
        }
        $files = glob($chunkDir.'*');
        $uploaded = [];
        foreach ($files as $f){
            $idx = basename($f);
            if(is_numeric($idx)) $uploaded[] = (int)$idx;
        }
        return $uploaded;
    }

    /**
     * 取消上传，清理分片
     */
    public function cancelUpload($fileId)
    {
        $this->clearChunk($fileId);
        return true;
    }

    /**
     * 清理分片垃圾文件
     * @param string $fileId 文件ID
     * @return void
     */
    protected function clearChunk($fileId): void
    {
        $chunkDir = public_path("storage/chunk/{$fileId}/");
        if (!is_dir($chunkDir)) return;
        $files = glob($chunkDir . '*');
        foreach ($files as $f) {
            is_file($f) && unlink($f);
        }
        rmdir($chunkDir);
    }

    /**
     * 文件二进制头校验，防止改后缀木马
     * @param string $filePath 文件路径
     * @param string $ext 文件后缀
     * @return bool 校验结果
     */
    protected function checkFileHeader($filePath, $ext): bool
    {
        $handle = fopen($filePath, 'rb');
        $header = bin2hex(fread($handle, 8));
        fclose($handle);

        $signMap = [
            'jpg'  => ['ffd8ff'],
            'jpeg' => ['ffd8ff'],
            'png'  => ['89504e47'],
            'gif'  => ['47494638'],
            'mp4'  => ['000000'],
            'avi'  => ['52494646'],
            'zip'  => ['504b0304','504b0506','504b0708'],
            'rar'  => ['52617221'],
            '7z'   => ['377abcaf'],
            'pdf'  => ['25504446']
        ];
        if (!isset($signMap[$ext])) return true;
        foreach ($signMap[$ext] as $sign) {
            if (str_starts_with($header, $sign)) return true;
        }
        return false;
    }
}