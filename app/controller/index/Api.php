<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\lib\facade\HttpHelper;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\common\model\UpgradeAuth;

class Api extends BaseController
{
    protected $middleware = ['logincheck'];
    public function key()
    {
        return View::fetch('index');
    }

    public function keyList()
    {
        if(Request::isAjax()){
            $keys = UpgradeAuth::where('user_id', $this->uid)->select();
            $count = $keys->count();
            $res = [];
            if($count){
                $res = ['code'=>0,'msg'=>'ok','count'=>$count];
                foreach($keys as $k=>$v){
                    $res['data'][] = ['domain'=>$v['domain'],'key'=>$v['key'],'auth_level'=>$v['auth_level'],'status'=>$v['status']? '正常':'待审','ctime'=>$v['create_time']];
                }

            } else {
                $res = ['code'=>-1,'msg'=>'还没有任何授权！'];
            }
            return json($res);
        }
        return json(['code' => -1, 'msg' => '请求非法！']);
    }

    public function setKey()
    {
        if(Request::isAjax()){
            $data = Request::only(['user','user_id','domain']);
            //域名转换，去掉协议
            $url = rtrim(trim($data['domain']),"/"); //去掉空格和最后的/
            //字符串截取方法
            //$data['domain'] = stripos($url,'://') ? substr(stristr($url,'://'),3) : $url;

            //parse_url函数方法，必须带http协议
            $data['domain'] = stripos($url,'://') ? parse_url($data['domain'])['host'] : $url;
            //$domainArr = parse_url($data['domain']);
            //$data['domain'] = $domainArr['host'];

            $d = UpgradeAuth::where('domain',$data['domain'])->find();

            if(!is_null($d) && $data['domain'] == $d['domain']) {
                $res = ['code'=>-1,'msg'=>'此域名已被申请,不能重复'];
            } else {
                $user = UpgradeAuth::where('user',$data['user'])->select();
                $domain_num = count($user);
                if($domain_num >= 5){
                    $res = ['code'=>-1,'msg'=>'申请超过数量'];
                } else {
                    $data['key'] = sha1(substr_replace($data['domain'],$data['user'],0,0));
                    $result = UpgradeAuth::create($data);
                    if($result){
                        $res = ['code'=>0,'msg'=>'提交成功'];
                    } else {
                        $res = ['code'=>-1,'msg'=>'申请失败，稍后重试'];
                    }
                }
            }
            return json($res);
        }
        return json(['code' => -1, 'msg' => '请求非法！']);
    }

    // 购买授权
    public function buyAuth()
    {
        //
        if(Request::isAjax()){
            $param = Request::param('name');
            $data = ['name' => $param, 'uid' => (int)$this->uid, 'type' => 3];

            // 授权查询
            //$keyAuth = Db::name('upgrade_auth')->where(['domain' => $param, 'user_id'=>$this->uid])->find();

            // 接口
            $response = HttpHelper::withHost()->post('/v1/order/ispay', $data)->toJson();
            if($response->code == 0) {
                return json(['code' => 0, 'msg' => '已授权，无需支付！']);
            } else {
                return json(['code' => -2, 'msg' => 'WAIT_BUYER_PAY']);
            }
        }
        return json(['code' => -1, 'msg' => '请求非法！']);

    }

    /**
     * 订单
     * @return string|\think\response\Json
     */
    public function pay()
    {
            $data = ['name' => input('name'), 'uid' => (int)$this->uid, 'type'=>3, 'price' => 288];
            $response = HttpHelper::withHost()->post('/v1/order/createorder', $data);
            if ($response->ok()) {
                View::assign(['orderData' => $response->toArray()['data']]);
                return View::fetch();
            } else {
                return json($response->toJson());
            }

    }

    /**
     * 支付查询
     * @return \think\response\Json
     */
    public function isPay()
    {
        if(Request::isAjax()){
            $param = Request::only(['name']);
            $data = [
                'name'=>$param['name'],
                'uid'=> (int)$this->uid,
                'type' => 3
            ];
            $response = HttpHelper::withHost()->post('/v1/order/ispay', $data)->toJson();
            if($response->code == 0) {
                Db::name('upgrade_auth')->where(['domain'=>$param['name'], 'user_id'=>$this->uid])->update(['auth_level' => 2, 'update_time' => time()]);
            }
            return json($response);
        }

        return json(['code' => -1, 'msg' => '请求非法！']);
    }


}