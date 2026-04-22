<?php
/*
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\content\Forum.php
 * @Description: Forum
 * @LastEditTime: 2023-03-14 15:42:00
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\content;

use app\admin\controller\AdminBaseController;
use app\facade\Article;
use app\facade\Category;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Cache;
use app\common\lib\Msgres;
use think\response\Json;
use Exception;
use app\common\service\ArticleService;
use app\common\strategy\ArticleValidation;
use app\common\strategy\DataValidationStrategy;
use app\common\strategy\AuthValidationStrategy;
use app\common\decorator\MainArticleProcessorDecorator;
use app\common\decorator\SensitiveWordFilter;
use app\common\decorator\WordsDesc;
use app\common\decorator\Media;
use app\common\observer\ObserverManager;
use app\common\observer\LogObserver;
use app\common\observer\TagObserver;
use app\common\observer\MailObserver;


class Forum extends AdminBaseController
{
    protected $model;

    public function initialize()
    {
        parent::initialize();
        $this->model = new Article();
    }

    /**
     * 浏览
     * @return string
     */
	public function index()
	{
		return View::fetch();
	}

    public function list()
    {
        $data = Request::only(['id/d','name','title','sec','cate_id/d']);
        $page = Request::param('page/d', 1);
        $limit = Request::param('limit/d', 10);
        
        $list = Article::getFilterList($data, $page, $limit);
        
        if($list['total']) {
            return json(['code' => 0, 'msg' => 'ok', 'data' => $list['data'], 'count' => $list['total']]);
        }
        
        return json(['code' => -1, 'msg' => 'no data']);
    }

    /**
     * 添加帖子文章
     * @return string|\think\Response|\think\response\Json|void
     */
    public function add()
    {
        if (Request::isAjax()) {

            $data = Request::only(['cate_id', 'title', 'tiny_content', 'content', 'keywords', 'description', 'tagid']);
            $data['user_id'] = 1; //管理员ID
            $data['status'] = 1; //正常

             try{

				$articleServer = new ArticleService();
			
				// 校验策略
				$articleServer->setValidation(new ArticleValidation())
					->addValidation(new DataValidationStrategy())
					->addValidation(new AuthValidationStrategy())
					->addValidation(new \app\common\strategy\PostValidationStrategy());

				// 装饰
				$articleServer->setDecorator(new MainArticleProcessorDecorator())
					->addProcessor(new SensitiveWordFilter()) //违禁词过滤
					->addProcessor(new WordsDesc()) //关键词描述
					->addProcessor(new Media()) // 媒体处理
					->addProcessor(new \app\common\decorator\Image()); // 图片处理

				// 观察者策略
				$articleServer->setObserverManager(new ObserverManager())
					->addObserver(new LogObserver())
					->addObserver(new MailObserver());

				$data = $articleServer->add($data);
                

                return json(['code' => 0, 'msg' => 'ok']);
                
            } catch(Exception $e) {
                return json(['code' => -1, 'msg' => $e->getMessage()]);
            }
        }

        return View::fetch('add');
    }

    /**
     * 编辑文章
     * @param $id
     * @return string|\think\Response|\think\response\Json|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function edit()
    {
        $id = $this->request->param('id/d');
		// $id = IdEncode::decode($id);

		$article = $this->model::suffix($this->byIdGetSuffix($id))->find($id);
        
        View::assign('article', $article);

        return View::fetch();
    }

    public function editData()
    {
        $data = Request::only(['id/d','cate_id','title','content','keywords','description','tagid']);
		// $id = IdEncode::decode($data['id']);

		$article = $this->model::suffix($this->byIdGetSuffix($data['id']))->find($data['id']);
 
        if(is_null($article)) return json(['code' => -1, 'msg' => '不能编辑！']);

        
        try{
            $articleServer = new ArticleService();
            
            // 校验策略
            $articleServer->setValidation(new ArticleValidation())
                ->addValidation(new DataValidationStrategy())
                ->addValidation(new AuthValidationStrategy());

            // 装饰
            $articleServer->setDecorator(new MainArticleProcessorDecorator())
                ->addProcessor(new SensitiveWordFilter()) //违禁词过滤
                ->addProcessor(new WordsDesc()) //关键词描述
                ->addProcessor(new Media()) // 媒体处理
                ->addProcessor(new \app\common\decorator\Image()); // 图片处理

            // 观察者策略
            $articleServer->setObserverManager(new ObserverManager())
                ->addObserver(new TagObserver())
                ->addObserver(new MailObserver());

        
            $articleServer->edit($data, $article);

        } catch(Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }

        //删除原有缓存显示编辑后内容
        Cache::delete('article_'.$data['id']);
        // $link = $this->getArticleUrl((int) $id, 'index', $article->cate->ename);
        // hook('SeoBaiduPush', ['link'=>$link]); // 推送给百度收录接口
        // return Msgres::success('edit_success');

        return json(['code' => 0, 'msg' => 'ok']);
        
    }


    //删除帖子 多选和单独
	public function delete($id)
	{
		if(Request::isAjax()){
            try {
                $arr = explode(",",$id);
                foreach($arr as $v){
                    $article = Article::find($v);
                    $article->together(['comments'])->delete();
                }
                return json(['code'=>0,'msg'=>'删除成功']);
            } catch (\Exception $e) {
                return json(['code'=>-1,'msg'=>'删除失败']);
            }
		}
	}

    /**
	 * 置顶、加精、
	 *
	 * @return Json
	 */
	public function setFlag()
	{
		$param = Request::only(['id/d', 'name', 'value/d']);

        // halt($param);

        $data["flags->{$param['name']}"] = $param['value'];

        try{
            //获取状态
            Db::table($this->getTableName($param['id']))
            ->json(['flags'])
            ->where('id', $param['id'])
            ->update($data);

            $has = Db::table($this->getTableName($param['id']))
            ->where('id', $param['id'])
            ->where('type', $param['name'])
            ->find();

            // 增加
            if($param['value'] === 1) {
                Db::name('article_flag')->save([
                    'type' => $this->getTypeValue($param['name']),
                    'article_id' => $param['id'],
                    'create_time'   => date('Y-m-d H:i:s', time())
                ]);
            }
            // 删除
            if($param['value'] === 0) {
                Db::name('article_flag')
                ->where('article_id', $param['id'])
                ->where('type', $this->getTypeValue($param['name']))
                ->delete();
            }
            
            // Cache::delete('article_'.$param['id']);
            
			return json(['code' => 0, 'msg' => '设置成功', 'icon'=>6]);
        } catch(Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage(), 'icon'=>6]);
        }
	}

    protected function getTypeValue($type) {
        return match($type) {
            'is_top'    => 1,
            'is_good'   => 2,
            'is_wait'   => 3,
        };
    }

	/**
	 * 评论开关，审核等状态管理
	 *
	 * @return Json
	 */
	public function check()
	{
		$param = Request::only(['id/d', 'name', 'value/d']);

        try{
            //获取状态
            Db::table($this->getTableName($param['id']))
            ->where('id', $param['id'])
            ->update([
                $param['name'] => $param['value']
            ]);

            Cache::delete('article_'.$param['id']);

			return json(['code' => 0, 'msg' => '设置成功', 'icon'=>6]);
        } catch(Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage(), 'icon'=>6]);
        }
	}

    /**
	 * 多选批量审核
	 *
	 * @return Json
	 */
	public function checkSelect()
	{
        $param = Request::param('data');
        $data = [];
        foreach($param as $v) {
            $data[] = ['id' => (int)$v['id'], 'status' => $v['check'] == '1' ? '-1' : '1'];
        }

		//获取状态
		$res = $this->model->saveAll($data);
	
		if($res){
			return json(['code'=>0,'msg'=>'审核成功','icon'=>6]);
		}else {
			return json(['code'=>-1,'msg'=>'失败啦','icon'=>6]);
		}
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

    /**
     * 分类树
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
	public function getCategoryTree()
	{
		//
		$cate = Db::name('cate')->field('id,pid,catename,ename,sort')->order(['id' => 'ASC','sort' => 'ASC'])->where(['delete_time'=>0])->select()->toArray();
        $data = getTree($cate);
		$count = count($cate);
        $tree = [];
        if($count){
            $tree = ['code'=>0,'msg'=>'','count'=>$count];

            //构造一个顶级菜单pid=0的数组。把权限放入顶级菜单下子权限中
            $tree['data'][] = ['id'=>0,'catename'=>'顶级','pid'=>0,'children'=>$data];
        }
		return json($tree);
	}

    /**
     * 分类
     * @return \think\response\Json
     */
    public function getCategoryList()
    {
        $cateList = Category::field('id,pid,catename,sort')->where(['status' => 1])->select()->toArray();
        // 排序
        $cmf_arr = array_column($cateList, 'sort');
        array_multisort($cmf_arr, SORT_ASC, $cateList);

        $list =  getTree($cateList);
        $count = count($list);
        $tree = [];
        if($count){
            $tree = ['code'=>0, 'msg'=>'ok','count'=>$count];
            $tree['data'] = $list;
        }

        return json($tree);
    }

    //array_filter过滤函数
    protected function  filtr($arr){
        if($arr === '' || $arr === null){
            return false;
        }
        return true;
    }

    /**
	 * 设置多媒体数据
	 *
	 * @param string $content
	 * @return array
	 */
	protected function setMediaData(string $content): array {
		$data = [];

		$data['media'] = [
			'images' => [],
			'videos' => [],
			'audios' => []
		];

		$images = get_all_img($content);
		$video = get_one_video($content);

		if(!empty($images)) {
			$data['media']['images'] = $images;
			$data['has_image'] = count($images);
            $data['thum_img'] = $images[0];
		}
		
		if(!empty($video)) {
			$data['media']['videos'] = $video;
			$data['has_video'] = count($video);
		}

		return $data;
	}

}
