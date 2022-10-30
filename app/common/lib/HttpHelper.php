<?php

namespace app\common\lib;

use yzh52521\EasyHttp\Http;
use yzh52521\EasyHttp\Request;
use yzh52521\EasyHttp\Response;
use yzh52521\EasyHttp\RequestException;

class HttpHelper
{
    /**
     * @var Http;
     */
    protected $http;

    protected $response;

    public function __construct(){
//        $this->http = new Http();
        if(!$this->http) {
            $this->http = new Request();
        }
    }

    /**
     * 携带指定接口
     * @param string $url
     * @return $this
     */
    public function withHost(string $url = 'http://api.aieok.com'): HttpHelper
    {
        $this->http = $this->http->withHost($url);
        return $this;
    }

    /**
     * 添加请求头
     * @param array $data
     * @return $this
     */
    public function withHeaders(array $data = []): HttpHelper
    {
        $this->http = $this->http->withHeaders($data);
        return $this;
    }

    /**
     * get请求
     * @param string $url
     * @param array $data
     * @return $this
     */
    public function get(string $url, array $data = []): HttpHelper
    {
        try {
            $this->response = $this->http->get($url, $data);
        } catch (\Exception $e) {
            //echo $e->getMessage();
        }
//        $this->response = $this->http->get($url, $data);
        return $this;
    }

    /**
     * POST请求
     * @param string $url
     * @param array $data
     * @return $this
     */
    public function post(string $url, array $data = [])
    {
        try {
            $this->response = $this->http->post($url, $data);
        } catch (\Exception $e) {
            //echo $e->getMessage();
        }
//        $this->response = $this->http->post($url, $data);
        return $this;
    }

    /**
     * 返回JSON数据
     * @return mixed
     */
    public function toJson()
    {
        if($this->ok()) {
            return $this->response->json();
        } else {
//            return json(['code' => -1, 'msg' => 'server failed']);
            return json_decode('{"code": -1, "msg": "server failed"}');
        }
    }

    /**
     * 返回ARRAY数据
     * @return array
     */
    public function toArray()
    {
        if($this->ok()) {
            return $this->response->array();
        } else {
            return ['code' => -1, 'msg' => 'server failed'];
        }
    }

    /**
     * @return bool
     */
    public function ok() : bool
    {
//        return $this->response->status() === 200;
//halt($this->response);

      if($this->response !== null) {
          return $this->response->status() === 200;
      }
      return false;

    }

}