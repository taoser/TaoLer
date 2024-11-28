<?php

namespace Applet\Assemble;

/**
 * 快手支付
 */
class KspayService
{
    protected $appid;
    protected $appSecret;
    protected $payment;
    protected $platform;
    public $data = null;
    
    public function __construct($payment,$platform = '')
    {
    	//从数据库读取快手appid 和 secret 信息
        $paymentConfig = json_decode(\model\Config::get(['name' => $platform])->value, true);

        $this->appid = $paymentConfig['app_id']; //小程序APPID
        $this->appSecret = $paymentConfig['secret']; //小程序的appsecret
         $this->platform = $platform;
        $this->payment = $payment;
    }
    /**
     * 通过跳转获取用户的openid，跳转流程如下：
     * @return 用户的openid
     */
    public function getOpenid($code)
    {
        $url = 'https://open.kuaishou.com/oauth2/mp/code2session';
        $dat = array(
            'app_id' => $this->appid,
            'app_secret' => $this->appSecret,
            'js_code' => $code
        );
        if(isset($code)){
            $res = self::curlPost($url,$dat);
            $data = json_decode($res,true);
            if($data['result'] == 1){
                $openid = $data['open_id'];
                return $openid;
            }else{
                echo "获取失败";
            }
        }else{
            echo "参数错误";
        }
    }

    /**
     * 获取accessToken
     * @return [type] [description]
     */
    public function _getAccessToken() {
        $postData['app_id'] = $this->appid;
        $postData['app_secret'] = $this->appSecret;
        $postData['grant_type'] = 'client_credentials';
        $res = $this->curlPost('https://open.kuaishou.com/oauth2/access_token', $postData);
        $res = json_decode($res, 1);
        return $res['access_token'];
    }

    /**
     * 生成签名
     *  @return 签名
     */
    public function makeSign($query, $postData) {
        unset($query['access_token']);
        $arr = array_merge($query, $postData);
        foreach ($arr as $k => $item) {
            if (empty($item)) {
                unset($arr[$k]);
            }
        }
        ksort($arr, 2);
        $str = '';
        foreach ($arr as $k => $v) {
            $str .= $k . '=' . $v . '&';
        }
        $str = substr($str, 0, strlen($str) - 1);
        $md5 = $str .$this->appSecret;
        return md5($md5);
    }
    /**
     * 预下单
     * @param  [type] $orderid   [description]
     * @param  [type] $price     [description]
     * @param  [type] $subject   [description]
     * @param  [type] $body      [description]
     * @param  [type] $openid    [description]
     * @param  [type] $notifyUrl [description]
     * @return [type]            [description]
     */
    public function createOrder($orderid,$price,$subject,$body,$openid,$notifyUrl){
        $time = time();
        $price = $price*100;
        $config = [
            'access_token' => $this->_getAccessToken(),
            'app_id' => $this->appid,
        ];
        // $provider = [
        //     "provider_channel_type" => "NORMAL",
        //     "provider" => "WECHAT"
        // ];
        $data = [
            'open_id' => $openid,
            'out_order_no' =>  $orderid, //订单号
            'total_amount' => $price,  //金额 单位:分
            'detail' => $body,         //支付的内容
            'subject' => $subject,     //支付的标题
            'type' => 3314,
            'expire_time' => 3600,
            'notify_url'=> $notifyUrl,//回调地址
            // 'provider' => json_encode($provider, JSON_UNESCAPED_UNICODE),
        ];
        $data['sign'] = $this->makeSign($config,$data);
        $url = 'https://open.kuaishou.com/openapi/mp/developer/epay/create_order?' . http_build_query($config);

        $json = json_encode($data, 320);
        $res = $this->jsonPost($url, $json);
        return json_decode($res,true);
    }
    /**
     * 订单结算
     * @return [type] [description]
     */
    public function settle($orderid,$amount){
        $config = [
            'access_token' => $this->_getAccessToken(),
            'app_id' => $this->appid,
        ];
        $params = [
            'out_order_no'  => $orderid,
            'out_settle_no' => 'js'.$orderid,
            'reason' =>'用户申请结算',//退款理由
            'notify_url' => 'https://'.$_SERVER['HTTP_HOST'].'/jiesuan_notify.php'
        ];
        $params['sign'] = $this->makeSign($config,$params);
        $url = "https://open.kuaishou.com/openapi/mp/developer/epay/settle?". http_build_query($config);
        $json = json_encode($params, 320);
        $res = $this->jsonPost($url, $json);
        return json_decode($res, true);
    }
    /**
     * 订单查询
     * @return [type] [description]
     */
    public function queryOrder($orderid){
        $config = [
            'access_token' => $this->_getAccessToken(),
            'app_id' => $this->appid,
        ];
        $params = [
            'out_order_no'	=> $orderid,
        ];
        $params['sign'] = $this->makeSign($config,$params);
        $url = "https://open.kuaishou.com/openapi/mp/developer/epay/query_order?". http_build_query($config);
        $json = json_encode($params, 320);
        $res = $this->jsonPost($url, $json);
        return json_decode($res, true);
    }
    /**
     * 退款
     * @return [type] [description]
     */
    public function createRefund($orderid,$amount){
        $config = [
            'access_token' => $this->_getAccessToken(),
            'app_id' => $this->appid,
        ];
        $params = [
            'out_order_no'	=> $orderid,
            'out_refund_no' => 'tk'.$orderid,
            'reason' =>'用户申请退款',//退款理由
            'notify_url' => 'https://'.$_SERVER['HTTP_HOST'].'/tk_notify.php',
            'refund_amount' => $amount,
        ];
        $params['sign'] = $this->makeSign($config,$params);
        $url = "https://open.kuaishou.com/openapi/mp/developer/epay/apply_refund?". http_build_query($config);
        $json = json_encode($params, 320);
        $res = $this->jsonPost($url, $json);
        return json_decode($res, true);
    }

    public function jsonPost($url, $data = NULL, $times = 0) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 2); //超时时间2秒
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    public static function curlGet($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public function curlPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}

