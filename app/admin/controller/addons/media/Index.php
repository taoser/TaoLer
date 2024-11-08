<?php
/**
 * @Program: table.css 2023/5/22
 * @FilePath: app\admin\controller\addons\media\Index.php
 * @Description: Index.php
 * @LastEditTime: 2023-05-22 14:45:29
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\addons\media;

use app\common\controller\AdminController;
use think\facade\Db;
use think\facade\Request;
use think\facade\View;
use think\facade\Session;
use think\facade\Cache;
use addons\media\model\Media;

class Index extends AdminController
{
    public function index()
    {
        return View::fetch();
    }

    // 列表
    public function list()
    {
        $param = Request::param(['limit','page','type']);
        if(empty($param['type'])) {
            $param['type'] = 1;
        }

        $media = Media::with(['user'])
        ->where(['type' => (int)$param['type']])
        ->paginate([
            'list_rows' => $param['limit'],
            'page'      => $param['page']
        ])->toArray();

        if($media['total']) {
            return json(['code' => 0, 'msg' => 'ok', 'count' => $media['total'], 'data' => $media['data']]);
        }
        return json(['code' => -1, 'msg' => 'no data']);
    }

    
     /**
     * 图片、视频混合添加
     * @return string|\think\response\Json
     */
    public function add()
    {

        if(Request::isAjax()) {
            $param = Request::param(['title','type','description','industry','urls','tags','types','avatars']);

            $param['user_id'] = 1;
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
                $media = Media::create($param);
                return json(['code' => 0, 'msg'=> 'ok']);
            } catch (\Exception $e) {
                return json(['code' => -1, 'msg' => $e->getMessage()]);
            }
        }
       
        return View::fetch();

    }

    //  查看内容
    public function see()
    {
        $media = Media::with('user')->find(input('id'));
        $content = '';
        foreach($media['info'] as $m) {
            $url = $m['url'];
            if($m['type'] == 'image') {
                $content .= '<p><img src="'. $url . '" style="width: 100%;"><p>';
            }elseif($m['type'] == 'video'){
                $content .= '<p><video src="'.$url.'" width="100%" controls height="600px"></video><p>';
            }
        }

        $media->content = $content;
        View::assign([
            'media' => $media,
        ]);
        return View::fetch();
    }

    //审核作品
    public function check()
    {
        $data = Request::only(['id','status']);

        //获取状态
        $res = Db::name('media')->where('id',$data['id'])->save(['status' => $data['status']]);
        if($res){
            if($data['status'] == 1){
                return json(['code'=>0,'msg'=>'启用成功','icon'=>6]);
            } else {
                return json(['code'=>0,'msg'=>'已被禁用','icon'=>5]);
            }
        }
        return json(['code'=>-1,'msg'=>'审核出错']);
    }

    // 删除
    public function delete($id)
    {
        $slider = Media::find($id);
        $res = $slider->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        } else {
            return json(['code'=>-1,'msg'=>'删除失败']);
        }
    }

    /**
     * 图片墙
     * @return string
     */
    public function media()
    {
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
        Db::name('media')
            ->where('id', input('id'))
            ->inc('pv')
            ->update();
        $media = Media::with(['user',
            'comment' => function($query) {
                $query->where(['type' => 2]);
            }
            ])->withCount('zan')->find(input('id'));

        $content = '';

        if($media['type'] == 1) {
            foreach($media['info'] as $v) {
                if($v['type'] == 'image') {
                    $content .= '<p><img src="' . $v['url'] . '" style="width: 100%;"><p>';
                }
            }
        }

        if($media['type'] == 2) {
            foreach($media['info'] as $v) {
                if($v['type'] == 'video') {
                    $content .= '<p><video src="'. $v['url'] .'" controls></video><p>';
                }
            }
        }

        $media->content = $content;
        View::assign([
            'media' => $media,
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

        $images = Cache::remember('media_'.$param['type'], function () use($param){

            $imgArr = [];
            $videoArr = [];
            if($param['type'] == 'image') {
                $type = 1;
            }elseif($param['type'] == 'video') {
                $type = 2;
            } else {
                $type = 1;
            }
            $alls = Media::with('user')->withCount('zan')->field('id,user_id,info,title,tags,type')->where(['status' => 1, 'type' => $type])->order('create_time desc')->select();

            if(empty($alls)) return json(['code' => -1, 'data' => '']);

            foreach ($alls as $v) {
                //图片
                if($v['type'] == 1) {
                    //循环json中数据
                    foreach($v->info as $m) {
                        if($m['type'] == 'image') {
                            $imgArr[] = ['id' => $v->id, 'url'=> (string) url('media/detail',['id' => $v->id]), 'img' => $m['url'], 'title'=> $v->title, 'user_img'=> $v->user->user_img, 'auther' => $v->user->name, 'zan'=>$v->zan_count, 'tag'=> $m['tag'], 'type' => 'image'];
                        }
                    }
                }

                //视频
                if($v['type'] == 2) {
                    //循环json中数据
                    foreach($v->info as $m) {
                        if($m['type'] == 'video') {
                            $videoArr[] = ['id' => $v->id, 'url'=> (string) url('media/detail',['id' => $v->id]), 'avatar' => $m['avatar'] ,'video' => $m['url'], 'title'=> $v->title, 'user_img'=> $v->user->user_img, 'auther' => $v->user->name, 'zan'=>$v->zan_count, 'tag'=> $m['tag'], 'type' => 'video'];
                        }
                    }
                }
            }

            if($param['type'] == 'video') {
                return $videoArr;
            } else {
                return $imgArr;
            }

        }, 30);

        $meditArr = Cache::get('media_'.$param['type']);
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
            $path = 'storage/video/avatar/' . date('Ymd',time()) . '/';
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

}