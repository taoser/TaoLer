<?php

namespace app\common\lib;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Exception;

class HttpHelper
{
    protected $response;

    private $client;
    
    private $host = '';

    private $url = '';

    private $options = [];

    public function __construct(){
        
        // verify 校验ssl, 填写cacert.pem路径或者false
        if(file_exists(public_path().'cacert.pem')) {
            $this->client = new Client(['verify' => 'cacert.pem']);
        } else {
            $this->client = new Client(['verify' => false]);
        }
    }

    /**
     * 携带指定接口
     * @param string $url
     * @return $this
     */
    public function withHost(string $url = 'https://www.aieok.com/api'): HttpHelper
    {
        $this->host = $url;
        
        return $this;
    }

    /**
     * 添加请求头
     * @param array $data
     * @return $this
     */
    public function withHeaders(array $headers = []): HttpHelper
    {
        $this->options = array_merge_recursive($this->options, [
            'headers' => $headers,
        ]);

        return $this;
    }

    /**
     * get请求
     * @param string $url
     * @param array $data
     * @return $this
     */
    public function get(string $url, array $data = []): HttpHelper|null
    {
        try{

            $this->url = $this->host . $url;
            if(!empty($data)) {
                $this->options = array_merge($this->options, ['query' => $data]);
            }
            $this->response = $this->client->get($this->url, $this->options);

        } catch (Exception $e) {
            echo $e->getMessage();
            return null;   
        } catch (RequestException $e) {
            $this->handleException($e);
            return null;
        }

        return $this;
    }

    /**
     * POST请求
     * @param string $url
     * @param array $data
     * @return $this
     */
    public function post(string $url, array $data = []): HttpHelper|null
    {
        try {

            $this->url = $this->host . $url;
            $this->options = array_merge($this->options, $data);
            $this->response = $this->client->post($this->url, ['json' => $this->options]);
        } catch (Exception $e) {
            echo $e->getMessage();
            return null;
        } catch (RequestException $e) {
            $this->handleException($e);
            return null;
        }

        return $this;
    }

    /**
     * 返回JSON数据
     * @return mixed
     */
    public function toJson()
    {
        $body = $this->response->getBody()->getContents();
        
        return json_decode($body);
    }

    /**
     * 返回ARRAY数据
     * @return array
     */
    public function toArray(): array
    {
        $body = $this->response->getBody()->getContents();

        return json_decode($body, true);
    }

    /**
     * @return bool
     */
    public function ok() : bool
    {

        if($this->response !== null) {
            return $this->response->getStatusCode() === 200;
        }

        return false;
    }

    /**
     * 处理请求异常
     * @param RequestException $e 异常对象
     */
    private function handleException(RequestException $e)
    {
        if ($e->hasResponse()) {
            $this->response = $e->getResponse();
            $statusCode = $this->response->getStatusCode();
            $body = $this->response->getBody()->getContents();
            echo "请求出错，状态码: {$statusCode}，响应内容: {$body}\n";
        } else {
            echo "请求出错: ". $e->getMessage(). "\n";
        }
    }

}