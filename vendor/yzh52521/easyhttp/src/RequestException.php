<?php

namespace yzh52521\EasyHttp;

class RequestException
{
    public $exception;

    public function __construct($exception)
    {
        $this->exception = $exception;
    }

    public function getCode()
    {
        return $this->exception->getCode();
    }

    public function getMessage()
    {
        return $this->exception->getMessage();
    }

    public function getFile()
    {
        return $this->exception->getFile();
    }

    public function getLine()
    {
        return $this->exception->getLine();
    }

    public function getTrace()
    {
        return $this->exception->getTrace();
    }

    public function getTraceAsString()
    {
        return $this->exception->getTraceAsString();
    }
}
