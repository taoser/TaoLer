<?php

namespace app\admin\controller\addons\reptiles;

use app\common\lib\facade\HttpHelper;
use think\App;
use think\facade\Request;
use think\facade\View;
use app\common\controller\AdminController;
use think\facade\Db;
use QL\QueryList;
use app\common\model\Article;

class Index extends AdminController
{
    protected $model;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new \app\admin\model\addons\reptiles\AddonReptiles();
    }

    /**
     * 列表
     * @return string|\think\response\Json
     */
    public function index()
    {
        if(Request::isAjax()) {
            $param = Request::param(['page','limit']);
            return $this->model->getList($param['page'],$param['limit']);
        }
        return View::fetch();
    }

    /**
     * 添加任务
     * @return string|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add()
    {
        if(Request::isAjax()) {
            $data = Request::param();
            if(isset($data['id'])) {
                $rep  = $this->model->find((int)$data['id']);
                $res = $rep->save($data);
            } else {
                $res = $this->model->save($data);
            }
            if($res) {
                return json(['code'=> 0, 'msg' => '添加成功']);
            }
            return json(['code'=> -1, 'msg' => '添加失败']);
        }

        $cate = Db::name('cate')->field('id,catename')->where(['delete_time'=>0,'status'=>1])->select();
        $user = Db::name('user')->field('id,name')->where(['delete_time'=>0,'status'=>1])->select();
        View::assign(['cate'=>$cate, 'user'=>$user]);
        return View::fetch();
    }

    /**
     * 配置数据后下发配置状态
     * @return \think\response\Json
     */
    public function setStatus()
    {
        if(Request::isAjax()) {
            $param = Request::param();
            $rep = $this->model->where('id',$param['id'])->find();
            //halt(md5($rep['id'].$rep['url']));
            // 启动事务
            $this->model->startTrans();
            try {
                // 请求下发数据
                $token = md5('rid'.$rep['id'].'uid'.$rep['uid']);
                $data = [
                    'addon_reptiles_id' => $rep['id'],
                    'status'    => $param['status'],
                    'url'       => $rep['url'],
                    'token'     => $token,
                    'end'       => $rep['end']
                ];
                
                $response = HttpHelper::withHost($rep['domain'])->post('/taocai/setstatus', $data);
                if($response->ok()) {
                    $this->model->where('id',$param['id'])->update(['status' => (int)$param['status']]);
                }
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                $this->model->rollback();
                return json(['code'=> -1, 'msg' => $e->getMessage()]);
            }
            return json($response->toJson());
        }
        return json(['code'=> -1, 'msg' => '非法请求']);

    }

    /**
     * 启动任务
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function start()
    {
        $rep = Db::name('addon_reptiles')->find(input('id'));
        $url = $rep['url'];
        // 元数据DOM解析规则
        $rules = [
            // DOM解析文章标题
            //'title' => [$rep['list_title'],'text'],
            // DOM解析链接
            'link' => [$rep['list_link'],'href'],
            // DOM解析缩略图
            //'img' => [$rep['list_img'],'src'],
            // DOM解析文档简介
            //'desc' => [$rep['list_desc'],'text']
        ];

        $range = $rep['range'];
        $rt = QueryList::get($url)->rules($rules)
           ->range($range)->query()->getData();
        $arrayLink = $rt->all();
        // 获取列表中的链接
        $urls = array_column($arrayLink, 'link');

        $singleRules = [
            'title'     => [$rep['single_title'],'text'],
            'content'   => [$rep['single_content'],'html']
        ];
        // 由于DOM解析的都是同一个网站的网页，所以DOM解析规则是可以复用的
        //$ql = QueryList::rules([...])->range('...');
        $ql = QueryList::rules($singleRules);

        foreach ($urls as $url) {
            $data = $ql->get($url)->query()->getData()->all();

            // 释放资源，销毁内存占用
            QueryList::destructDocuments();
            $data['user_id'] = $rep['user_id'];
            $data['cate_id'] = $rep['cate_id'];
//            $keywords = $this->setKeywords(['flag' =>'word','keywords'=>$data['title'],'content' =>$data['content']])->getData();
//            halt($keywords);
            $data['keywords'] = '';
            $data['description'] = getArtContent($data['content']);
            $article = new Article();
            $res = $article->save($data);
            if($res) {
                echo '入库成功';
            }
            sleep(2);
        }

        return json(['code'=> 0, 'msg' => '启动成功']);
    }

    // 删除
    public function delete()
    {
        $res = $this->model->destroy(input('id'));
        if($res) {
            return json(['code'=> 0, 'msg' => '删除成功']);
        } else {
            return json(['code'=> -1, 'msg' => '删除失败']);
        }
    }


}