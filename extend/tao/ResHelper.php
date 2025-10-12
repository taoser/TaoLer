<?php
namespace tao;

use think\Response;

class ResHelper
{
     /**
     * 成功响应
     * @param mixed $data 响应数据
     * @param string $msg 提示信息
     * @param int $code 状态码
     * @param int|null $count 数据量统计（默认为null时自动计算数组长度）
     * @return Response
     */
    public static function success($data = [], string $msg = 'ok', int $code = 0, ?int $count = null): Response
    {
        // 自动计算数组数据量（如果未手动传入count）
        if ($count === null && is_array($data)) {
            $count = count($data);
        }

        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'count' => $count
        ]);
    }

    /**
     * 错误响应
     * @param string $msg 提示信息
     * @param int $code 状态码
     * @param mixed $data 响应数据
     * @return \think\Response
     */
    public static function error(string $msg = 'error', int $code = 1, $data = []): Response
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ]);
    }

}