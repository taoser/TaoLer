<?php
/**
 * @Program: table.css 2023/5/20
 * @FilePath: app\index\controller\Works.php
 * @Description: Works.php
 * @LastEditTime: 2023-05-20 17:34:12
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\index\controller;

use addons\picwall\model\Works as WorksModel;
use app\common\controller\BaseController;
use addons\picwall\model\WorksComment;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\View;

class Works extends BaseController
{
//    protected $middleware = [
//        'logincheck' => ['except' 	=> ['detail','picwall','getData'] ],
//    ];

    public function index()
    {
        //
    }

    /**
     * 图片、视频混合添加
     * @return string|\think\response\Json
     */
    public function add()
    {
        if(!Session::has('user_id')){
            return redirect(url('user_login'));
        }
        if(Request::isAjax()) {
            $param = Request::param(['title','type','description','industry','urls','tags','types','avatars']);

            $param['user_id'] = $this->uid;
            $jsonArr = [];
            foreach($param['urls'] as $k => $v) {
                $jsonArr[$k]['url'] = $v;
            }
            foreach($param['tags'] as $k => $v) {
                $jsonArr[$k]['tag'] = $v;
            }
            foreach($param['types'] as $k => $v) {
                $jsonArr[$k]['type'] = $v;
            }
            if(isset($param['avatars'])) {
                foreach($param['avatars'] as $k => $v) {
                    $jsonArr[$k]['avatar'] = $v;
                }
                unset($param['avatars']);
            }

            unset($param['urls']);
            unset($param['tags']);
            unset($param['types']);

            $param['info'] = $jsonArr;

            try {
                $works = WorksModel::create($param);
                $id = $works->id;
                return json(['code' => 0, 'msg'=> 'ok', 'url' => (string) url('works/detail', ['id' => $id])]);
            } catch (\Exception $e) {
                return json(['code' => -1, 'msg' => $e->getMessage()]);
            }
        }
       
        View::assign([
            'jspage'    => 'jie',
        ]);
        return View::fetch();

    }

    /**
     * 图片墙
     * @return string
     */
    public function picwall()
    {
        View::assign([
            'jspage' => 'jie'
        ]);
        return View::fetch();
    }

    /**
     * 作品详情
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail()
    {
        //浏览pv
        Db::name('works')
            ->where('id', input('id'))
            ->inc('pv')
            ->update();
        $works = WorksModel::with(['user',
            'comment' => function($query) {
                $query->where(['type' => 2]);
            }
            ])->withCount('zan')->find(input('id'));

        $content = '';

        if($works['type'] == 1) {
            foreach($works['info'] as $v) {
                if($v['type'] == 'image') {
                    $content .= '<p><img src="' . $v['url'] . '" style="width: 100%;"><p>';
                }
            }
        }

        if($works['type'] == 2) {
            foreach($works['info'] as $v) {
                if($v['type'] == 'video') {
                    $content .= '<p><video src="'. $v['url'] .'" controls></video><p>';
                }
            }
        }

//        foreach($works['info'] as $v) {
//            if($v['type'] == 'image') {
//                $content .= '<p><img src="'. $v['url'] . '" style="width: 100%;"><p>';
//            }elseif($v['type'] == 'video'){
//                $content .= '<p><video src="'. $v['url'] .'" width="100%" controls height="600px"></video><p>';
//            }
//        }

        $works->content = $content;
        View::assign([
            'works' => $works,
            'jspage' => ''
        ]);
        return View::fetch();
    }

    /**
     * 动态加载数据
     * @return \think\response\Json
     * @throws \Throwable
     */
    public function getData()
    {
        $param = Request::param(['type','page']);

        $images = Cache::remember('picwall_'.$param['type'], function () use($param){

            $imgArr = [];
            $videoArr = [];
            if($param['type'] == 'image') {
                $type = 1;
            }elseif($param['type'] == 'video') {
                $type = 2;
            } else {
                $type = 1;
            }
            $alls = WorksModel::with('user')->withCount('zan')->field('id,user_id,info,title,tags,type')->where(['status' => 1, 'type' => $type])->order('create_time desc')->select();

            if(empty($alls)) return json(['code' => -1, 'data' => '']);

            foreach ($alls as $v) {
                //图片
                if($v['type'] == 1) {
                    //循环json中数据
                    foreach($v->info as $m) {
                        if($m['type'] == 'image') {
                            $imgArr[] = ['id' => $v->id, 'url'=> (string) url('works/detail',['id' => $v->id]), 'img' => $m['url'], 'title'=> $v->title, 'user_img'=> $v->user->user_img, 'auther' => $v->user->name, 'zan'=>$v->zan_count, 'tag'=> $m['tag'], 'type' => 'image'];
                        }
                    }
                }

                //视频
                if($v['type'] == 2) {
                    //循环json中数据
                    foreach($v->info as $m) {
                        if($m['type'] == 'video') {
                            $videoArr[] = ['id' => $v->id, 'url'=> (string) url('works/detail',['id' => $v->id]), 'avatar' => $m['avatar'] ,'video' => $m['url'], 'title'=> $v->title, 'user_img'=> $v->user->user_img, 'auther' => $v->user->name, 'zan'=>$v->zan_count, 'tag'=> $m['tag'], 'type' => 'video'];
                        }
                    }
                }

                //循环json中数据
//                foreach($v->info as $m) {
//                    if($m['type'] == 'image') {
//                        $imgArr[] = ['id' => $v->id, 'url'=> (string) url('works/detail',['id' => $v->id]), 'img' => $m['url'], 'title'=> $m['tag'], 'type' => 'image'];
//                    }elseif($m['type'] == 'video'){
//                        $imgArr[] = ['id' => $v->id, 'url'=> (string) url('works/detail',['id' => $v->id]), 'video' => $m['url'], 'title'=> $m['tag'], 'type' => 'video'];
//                    }
//                }
            }
            if($param['type'] == 'video') {
                return $videoArr;
            } else {
                return $imgArr;
            }

        }, 30);

        $meditArr = Cache::get('picwall_'.$param['type']);
        $newImg = array_chunk($meditArr, 25);
        //总数
        $num = count($newImg);
        //当前页
        $page = (int)$param['page'];

        if($page < $num - 1) {
            return json(['code' => 1, 'data' => $newImg[$page]]);
        } elseif($page == $num - 1) {
            return json(['code' => 2, 'data' => $newImg[$page], 'msg' => '没有更多数据']);
        }
        return json(['code' => -1, 'msg' => '没有更多数据']);

    }

    /**
     * 上传接口
     *
     * @return void
     */
    public function uploads()
    {
        if(!Session::has('user_id')){
            return redirect(url('user_login'));
        }
        $type = Request::param('type');

        return $this->uploadFiles($type);
    }

    // 存base64封面截图
    public function saveBase()
    {
        $base64Data = input('base64');
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64Data, $result)) {
            $type     = $result[2];
            //$new_file = "./test.{$type}";
            $file = md5(time()) . '.' . $type;
            $path = 'storage' . DS . $this->uid . DS .'video'. DS .'avatar' . DS . date('Ymd',time()) . DS;
            $new_file = public_path() . $path . $file;
            if(!file_exists($path)) {
                mkdir($path,0755,true);
            }
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64Data)))) {
                //echo '新文件保存成功：', $new_file;
                return json(['code' => 0, 'msg' => 'ok', 'url' => str_replace('/','\\',DS . $path.$file)]);
            }
        }

    }

    public function zan()
    {
        if(!Session::has('user_id')){
            return json(['code' =>-1, 'msg' => '登录再点赞']);
        }

        $zan = new \app\common\model\UserZan;
        $uzan = $zan->where('user_id', $this->uid)->findOrEmpty();
        if(!$uzan->isEmpty()) return json(['code' => -1, 'msg' => '不能重复点赞哦！']);

        $res = $zan->save([
            'article_id' => (int) input('article_id'),
            'type'       => 3,
            'user_id'   => $this->uid,
        ]);

        if($res) {
            return json(['code' => 0, 'msg' => '感谢点赞']);
        }

    }

    public function comment()
    {
        if(!Session::has('user_id')){
            return json(['code' =>-1, 'msg' => '登录再评论']);
        }
        $data = Request::param();
        if(empty($data['content'])) return json(['code' =>-1, 'msg' => '评论内容不能为空！']);
        $data['user_id'] = $this->uid;
        $data['works_id'] = (int) $data['article_id'];
        $data['type'] = 2;
        $res = WorksComment::create($data);
        if($res->id) {
            return json(['code' =>0, 'msg' => '评论成功！']);
        }
        json(['code' =>-1, 'msg' => '评论失败！']);
    }

}