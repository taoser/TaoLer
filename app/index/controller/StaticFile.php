<?php

namespace app\index\controller;

use think\Response;

class StaticFile
{
    public function verify()
    {

        $pathInfo = request()->pathinfo() ?? '';
        $imagePath = trim($pathInfo, '/');  // 例如：protected/cat.jpg → cat.jpg
        $imageName = basename($imagePath);  // 防止目录遍历攻击


        $realImagePath = str_replace('\\','/', root_path() . "data/storage/$imageName");
        // halt($realImagePath);

        // 5. 设置响应头（Content-Type、缓存等）
        $mime = mime_content_type($realImagePath);

        header("Content-Type: $mime");
        header("Cache-Control: max-age=86400, public");  // 缓存1天
        header('Content-Length: ' . filesize($realImagePath));
        header("X-Frame-Options: SAMEORIGIN");  // 防止Frame嵌入

        // 6. 输出图片内容（两种方式）
        // 方式一：使用X-Sendfile（推荐，Nginx高效传输）
        // header("X-Accel-Redirect: /internal_images/{$imageName}");
        readfile($realImagePath);
        
        return;
    }

    public function showImg() {
        
        $pathInfo = request()->pathinfo() ?? '';

        $imagePath = trim($pathInfo, '/');  // 例如：protected/cat.jpg → cat.jpg

        // 3. 构建图片的完整物理路径
        $filePath = root_path() . 'data/' . $imagePath . '.jpg'; // 或者 Filesystem::disk('local')->path($filename)

        // 4. 检查文件是否存在
        if (!file_exists($filePath)) {
            return json(['code' => 404, 'msg' => 'Image not found.'], 404);
        }

		// $filePath = '../data/storage/1/licence_pic/kefu.jpg';
		// 5. 读取文件内容并输出
        $fileContent = file_get_contents($filePath);
        $mimeType = mime_content_type($filePath); // 获取正确的MIME类型

		return Response::create($fileContent)->contentType($mimeType);
	}
}