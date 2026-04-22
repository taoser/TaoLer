<?php
/**
 * @Program: TaoLer 2023/3/14
 * @FilePath: app\admin\controller\content\Comment.php
 * @Description: Comment
 * @LastEditTime: 2023-03-14 15:38:55
 * @Author: Taoker <317927823@qq.com>
 * @Copyright (c) 2020~2023 https://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\content;

use app\admin\controller\AdminBaseController;
use think\App;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use app\index\model\Comment as CommentModel;
use think\facade\Log;

class Comment extends AdminBaseController
{

    protected $model;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new CommentModel();
    }

    /**
     * 浏览
     * @return string
     */
	public function index()
    {
        return View::fetch();
    }

	/**
	 * 帖子评论列表
	 * 
	 * 获取评论列表，支持按用户名、内容、状态进行筛选
	 * 
	 * @return Json 返回评论列表数据
	 */
	public function list()
	{
        try {
            // 获取请求参数并进行安全过滤
            $page = max(1, (int)input('page', 1));
            $limit = min(100, (int)input('limit', 10));
            $name = trim(Request::param('name', ''));
            $content = trim(Request::param('content', ''));
            $status = Request::param('status', '');
            
            // 构建查询条件
            $where = [];
            
            // 内容搜索 - 使用参数绑定防止SQL注入
            if (!empty($content)) {
                $where[] = ['a.content', 'like', '%' . $content . '%'];
            }
            
            // 状态筛选
            if ($status !== '') {
                $where[] = ['a.status', '=', (int)$status];
            }
            
            // 用户名搜索
            if (!empty($name)) {
                $where[] = ['u.name', 'like', '%' . $name . '%'];
            }
            
            // 构建查询
            $query = Db::name('comment')
                ->alias('a')
                ->join('user u', 'a.user_id = u.id')
                ->join('article c', 'a.article_id = c.id')
                ->join('cate ca', 'c.cate_id = ca.id')
                ->field('a.id as aid, u.name, ca.ename, c.title, u.user_img, a.content, a.create_time, a.status as astatus, c.id as cid')
                ->where('a.delete_time', 0)
                ->where($where)
                ->order('a.create_time', 'desc');
            
            // 执行分页查询
            $replys = $query->paginate([
                'list_rows' => $limit,
                'page' => $page
            ]);
            
            // 处理返回数据
            $count = $replys->total();
            $data = [];
            
            if ($count) {  
                foreach ($replys as $v) {
                    $data[] = [
                        'id'        => (int)$v['aid'],
                        'replyer'   => htmlspecialchars($v['name']),
                        'title'     => htmlspecialchars($v['title']),
                        'avatar'    => $v['user_img'],
                        'content'   => mb_substr(strip_tags($v['content']), 0, 100, 'UTF-8'),
                        'replytime' => date('Y-m-d H:i:s', $v['create_time']),
                        'check'     => (int)$v['astatus'],
                        'url'       => $this->getArticleUrl((int)$v['cid'], 'index', $v['ename'])
                    ];
                }
            }
            
            // 统一返回格式
            return json([
                'code'  => 0,
                'msg'   => $count ? 'ok' : 'no data',
                'count' => $count,
                'data'  => $data
            ]);
        } catch (\Exception $e) {
            // 捕获异常并记录日志
            	Log::error('Comment list error: ' . $e->getMessage());
            
            // 返回错误信息
            return json([
                'code' => -1,
                'msg' => '系统错误，请稍后重试',
                'count' => 0,
                'data' => []
            ]);
        }
	}
	
	//评论编辑
	public function edit()
	{
		return View::fetch();
	}
	
	/**
	 * 评论删除
	 * 
	 * 支持单个或批量删除评论
	 * 
	 * @return Json 返回删除结果
	 */
	public function delete()
	{
		if (Request::isAjax()) {
            try {
                $id = Request::param('id');
                // 验证参数
                if (empty($id)) {
                    return json(['code' => -1, 'msg' => '参数错误']);
                }
                
                // 处理ID列表
                $ids = array_filter(explode(",", $id), function($item) {
                    return is_numeric($item) && $item > 0;
                });
                
                if (empty($ids)) {
                    return json(['code' => -1, 'msg' => '无效的ID']);
                }
                
                // 批量删除评论
                $result = CommentModel::destroy($ids);
                
                if ($result) {
                    return json(['code' => 0, 'msg' => '删除成功']);
                } else {
                    return json(['code' => -1, 'msg' => '删除失败']);
                }
            } catch (\Exception $e) {
                // 记录错误日志
                Log::error('Comment delete error: ' . $e->getMessage());
                return json(['code' => -1, 'msg' => '系统错误，请稍后重试']);
            }
        }
        
        return json(['code' => -1, 'msg' => '非法请求']);
	}

	/**
	 * 评论审核
	 * 
	 * 审核评论状态，设置为通过或禁止
	 * 
	 * @return Json 返回审核结果
	 */
	public function check()
	{
		try {
            // 验证请求类型
            if (!Request::isAjax()) {
                return json(['code' => -1, 'msg' => '非法请求']);
            }
            
            // 获取并验证参数
            $id = Request::param('id/d');
            $status = (int)Request::param('status', 0);
            
            if ($id <= 0) {
                return json(['code' => -1, 'msg' => '参数错误']);
            }
            
            // 验证状态值
            if (!in_array($status, [0, 1, -1])) {
                return json(['code' => -1, 'msg' => '无效的状态值']);
            }
            
            // 更新评论状态
            $result = Db::name('comment')->where('id', $id)->save(['status' => $status]);
            
            if ($result == false) {
                return json(['code' => -1, 'msg' => '审核出错']);
            }

            if ($status == 1) {
                return json(['code' => 0, 'msg' => '评论审核通过', 'icon' => 6]);
            }

            return json(['code' => 0, 'msg' => '评论被禁止', 'icon' => 5]);

        } catch (\Exception $e) {
            // 记录错误日志
            Log::error('Comment check error: ' . $e->getMessage());

            return json(['code' => -1, 'msg' => config('app.debug') ? $e->getMessage() : '系统错误，请稍后重试']);
        }
	}

    /**
	 * 多选批量审核
	 * 
	 * 批量审核评论状态，根据check值设置status
	 * - check=1 时，status=-1（禁止）
	 * - check=0 或 check=-1 时，status=1（通过）
	 *
	 * @return Json 返回审核结果
	 */
	public function checkSelect()
	{
        try {
            // 验证请求类型
            if (!Request::isAjax()) {
                return json(['code' => -1, 'msg' => '非法请求', 'icon' => 5]);
            }
            
            // 获取并验证参数
            $param = Request::param('data');
            
            if (empty($param) || !is_array($param)) {
                return json(['code' => -1, 'msg' => '参数错误', 'icon' => 5]);
            }
            
            // 处理审核数据
            $data = [];
            foreach ($param as $v) {
                // 验证每个元素的结构
                if (!isset($v['id']) || !isset($v['check'])) {
                    continue;
                }
                
                // 根据check值计算status
                $status = ($v['check'] == 1) ? -1 : 1;
                
                $data[] = [
                    'id' => (int)$v['id'],
                    'status' => $status
                ];
            }
            
            // 检查是否有有效的审核数据
            if (empty($data)) {
                return json(['code' => -1, 'msg' => '没有有效的审核数据', 'icon' => 5]);
            }
            
            // 批量更新状态
            $result = $this->model->saveAll($data);
            
            if ($result) {
                return json(['code' => 0, 'msg' => '审核成功', 'icon' => 6]);
            }

            return json(['code' => -1, 'msg' => '审核失败', 'icon' => 5]);
        } catch (\Exception $e) {
            // 记录错误日志
            Log::error('Comment checkSelect error: ' . $e->getMessage());
            return json(['code' => -1, 'msg' => '系统错误，请稍后重试', 'icon' => 5]);
        }
	}
	

	
	//array_filter过滤函数
	public function  filtr($arr)
    {
        if($arr === '' || $arr === null) return false;
        return true;
	}

}