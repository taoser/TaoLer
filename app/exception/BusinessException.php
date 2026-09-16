<?php
/*
 * @Author: TaoLer <317927823@qq.com>
 * @Date: 2026-09-15 22:15:32
 * @LastEditTime: 2026-09-16 07:24:48
 * @LastEditors: TaoLer
 * @Description: 自定义业务异常类
 * @Version: V4.0.0
 * @FilePath: \TaoLer\app\exception\BusinessException.php
 * @Copyright: (c) 2020~2026 https://www.aieok.com All rights reserved.
 */
namespace app\exception;

use Exception;

class BusinessException extends Exception
{
    protected $data;

    public function __construct(string $message = '', int $code = -1, $data = [])
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
