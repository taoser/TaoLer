<?php

namespace app\index\model;

use app\common\model\BaseModel;
use Exception;
use think\db\exception\DbException;
use think\db\Query;
use think\facade\Db;
use think\facade\Cache;
use app\common\lib\IdEncode;
use think\facade\Route;

class Category extends BaseModel
{
    // 表名
    protected $name = 'cate';

    // 新的数量, 数据介于两表之间分量时使用
    protected static $newLimit;
    // 当前分页数据偏移量
    protected static $offset;
    // 当前用到的数据总和
    protected static $currentTotalNum = 0;


    //关联文章
	public function article()
    {
        return $this->hasMany(Article::class);
    }
	
	// 查询类别信息
	public static function getCateInfoByEname(string $ename)
	{
		$cate = self::field('id,ename,catename,detpl,desc')
        ->where('ename', $ename)
        ->where('status', '1')
        ->cache('cate_'.$ename, 3600)
        ->find();

        // 抛出 HTTP 异常
        if(is_null($cate) && $ename != 'all') {
			throw new \think\exception\HttpException(404, '没有可访问的数据！');
		}

        return $cate;
	}

    // ID查询类别信息
    public function getCateInfoById(int $id)
    {
        return $this->field('id,catename,ename,detpl,pid,icon,sort,desc')->find($id);
    }

    // 查询父分类
    public function getParentCate(string $ename)
    {
        $pid = $this::where('ename', $ename)->value('pid');
        if($pid == 0) {
            return null;
        }
        return $this->field('ename,catename')->where('pid', $pid)->append(['url'])->select();
    }

    // 查询兄弟分类
    public function getBrotherCate(string $ename)
    {
        return $this->field('id,ename,catename,desc')->where('pid', $this::where('ename', $ename)->value('pid'))->append(['url'])->order('sort asc')->select();
    }

    // 查询子分类
    public function getSubCate(string $ename)
    {
        return $this->field('id,ename,catename,desc')->where('pid', $this::where('ename', $ename)->value('id'))->append(['url'])->select();
    }

    /**
     * 删除分类
     * @param $id
     * @return int|string
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
	public function del($id)
	{
		$cates = $this->field('id,pid')->with('article')->find($id);
		$sonCate = $this::where('pid',$cates['id'])->count();
		if($sonCate > 0) {
            return '存在子分类，无法删除';
		}
        $res = $cates->together(['article'])->delete();
        return $res ? 1 : '删除失败';
	}

    // 分类表
    public function getList()
    {
        $data = $this->field('id,pid,sort,catename,ename,detpl,icon,status,is_hot,desc')->cache(3600)->select()->toArray();
        if(count($data)) {
            // 排序
            $cmf_arr = array_column($data, 'sort');
            array_multisort($cmf_arr, SORT_ASC, $data);
            return json(['code'=>0,'msg'=>'ok', 'count' => count($data),'data'=>$data]);
        }
        return json(['code'=>-1,'msg'=>'no data','data'=>'']);
    }

    // 如果菜单下无内容，URl不能点击
    public function menu()
    {
        try {
            return $this->where(['status' => 1])
                ->cache(3600)
                ->append(['url'])
                ->select()
                ->toArray();
        } catch (DbException $e) {
            return $e->getMessage();
        }

    }

    // 分类导航菜单
    public function getNav()
    {
        try {
            $cateList = $this->where('status', '1')
                ->cache(3600)
                ->append(['url'])
                ->select()
                ->toArray();
            // 排序
            $cmf_arr = array_column($cateList, 'sort');
            array_multisort($cmf_arr, SORT_ASC, $cateList);
            return getTree($cateList);
        } catch (DbException $e) {
            return $e->getMessage();
        }
    }

    /**
     * 导航子菜单
     *
     * @param string $ename 父导航英文名
     * @return array
     */
    public function getSubnav(string $ename) : array
    {
        $subCateArray = Cache::remember("subnav_{$ename}", function() use($ename){
			$subCateList = []; // 没有点击任何分类，点击首页获取全部分类信息
			//1.查询父分类id
			$pCate = Db::name('cate')
			->field('id,pid,ename,catename,is_hot')
			->where(['ename' => $ename,'status'=>1,'delete_time'=>0])
			->find();

			if(!is_null($pCate)) {
				// 点击分类，获取子分类信息
				$parentId = $pCate['id'];

				$subCate = Db::name('cate')
				->field('id,ename,catename,is_hot,pid')
				->where(['pid'=>$parentId,'status'=>1,'delete_time'=>0])
				->select()
				->toArray();
					
				if(!empty($subCate)) { 
					// 有子分类
					$subCateList = array2tree($subCate);
				} else {
					//无子分类
					if($pCate['pid'] == 0) {
						//一级菜单
						$subCateList[] = $pCate;
					} else {
						//子菜单下如果无子菜单，则显示全部兄弟分类
						$parament = Db::name('cate')
						->field('id,ename,catename,is_hot,pid')
						->where(['pid'=>$pCate['pid'],'status'=>1,'delete_time'=>0])
						->order(['sort' => 'asc'])
						->select()
						->toArray();

						$subCateList = array2tree($parament);
					}
				}
			}

			return $subCateList;
		});
		
		return $subCateArray;
    }

    /**
     * 分类文章，支持分表分页
     *
     * @param string $ename 英文别名
     * @param integer $page 页码
     * @param string $type 筛选类型
     * @param integer $limit 每页数
     * @return array
     */
    public static function getArticlesByCategoryEname(string $ename = 'all', int $page = 1, string $type = 'all', int $limit = 15): array
    {
        // 查询条件
        $where = [];
        // 数据
        $data = [];
        
        $cate = self::getCateInfoByEname($ename);

        if(!empty($cate['id'])){
            $where[] = ['cate_id' ,'=', $cate['id']];
        }

        $where[] = ['status', '=', 1];

        switch ($type) {
            //查询文章,15个分1页
            case 'top':
                $where[] = ['flags->is_top' ,'=', '1'];
                break;
            case 'good':
                $where[] = ['flags->is_good','=', '1'];
                break;
            case 'end':
                $where[] = ['flags->is_wait','=', '1'];
                break;
            case 'wait':
                $where[] = ['flags->is_wait','=', '0'];
                break;
        }

        // $limit = 5;
        // $page = 3;

        // $m = self::getSuffixMap(['status' => 1]);
        // halt($m);

        // 文章表数据
        $map = Cache::remember("cate_count_{$ename}_{$type}", function() use($where){

            return self::getSuffixMap($where, Article::class);

            // return [
            //     'countArr'    => $countArr,
            //     'totals'    => $totals,
            //     'tableSuffixArr' => $tableSuffixArr,
            //     'tableCount' => $tableCount
            // ];

        }, 900);


        // 总共页面数
        $lastPage = (int) ceil($map['totals'] / $limit); // 向上取整
 
        if($map['totals']) {

            if($page > $lastPage) {
                throw new Exception('no data');
            }

            $data = Cache::remember("cateroty_{$ename}_{$type}_{$page}", function() use($where, $page, $limit, $map, $cate) {

                $datas = [];
                // 最大偏移量
                $maxNum = $page * $limit;
                // 开始时的偏移量
                self::$offset = ($page - 1) * $limit;
                // newLimit首次=limit, newLimit 在数据介于两表之间时分量使用
                self::$newLimit = $limit;

                $field = 'id,cate_id,user_id,title,content,description,create_time,pv,has_image,has_video,has_audio,media,comments_num,flags';

                // 循环 取每个表中的数据
                for($i = 0; $i < $map['tableCount']; $i++) {
                    // 当前表 取到的数据总数
                    self::$currentTotalNum += $map['countArr'][$i];

                    // 1.可以完全取到 在第一组分表中就可以完全查询到
                    if((self::$currentTotalNum - $maxNum) >= 0){
                        // echo 123;
                    
                        $articles = Article::suffix($map['tableSuffixArr'][$i])
                        ->field('id')
                        ->where($where)
                        ->order('id', 'desc')
                        ->limit(self::$offset, self::$newLimit)
                        ->select();

                        $ids = $articles->toArray();
                        $idArr = array_column($ids, 'id');

                        $list =  Article::suffix($map['tableSuffixArr'][$i])
                        ->field($field)
                        ->whereIn('id', $idArr)
                        ->with([
                            'user' => function(Query $query){
                                $query->field('id,name,nickname,user_img,vip');
                            }
                        ])
                        ->order('id', 'desc')
                        ->select()
                        ->toArray();
                        
                        $datas = array_merge($datas, $list);
                        break;
                    } 

                    // 2.数据介于2表之间 第一组和第二组各取部分数据
                    if((self::$currentTotalNum - $maxNum) < 0 && ($maxNum - self::$currentTotalNum - $limit) < 0 ) {
                        // echo 234;

                        $articles = Article::suffix($map['tableSuffixArr'][$i])
                        ->field('id')
                        ->where($where)
                        ->order('id', 'desc')
                        ->limit(self::$offset, self::$newLimit)
                        ->select();
                        $ids = $articles->toArray();
                        $idArr = array_column($ids, 'id');

                        $list =  Article::suffix($map['tableSuffixArr'][$i])
                        ->field($field)
                        ->whereIn('id', $idArr)
                        ->with([
                            'user' => function(Query $query){
                                $query->field('id,name,nickname,user_img,vip');
                            }
                        ])
                        ->order('id', 'desc')
                        ->select()
                        ->toArray();
                        
                        $datas = array_merge($datas, $list);
                        
                        // 介于2表之间 第二张表分量从0开始
                        self::$offset = 0;
                        // 第二张表分量数
                        self::$newLimit = $page * $limit - self::$currentTotalNum;
            
                    }

                    // 3.第一组完全取不到 数据没有在第一组，刚好从第二组开头取, 只能从后面一组从0开始继续找 ，需要跳过当次循环
                    if($maxNum - self::$currentTotalNum - $limit == 0) {

                        // echo 345;

                        self::$offset = 0;
                    }

                    // 4.第一组完全取不到 且不是从第二组开头找，需要跳过当次循环
                    if((self::$currentTotalNum - $maxNum < 0) && ($maxNum - self::$currentTotalNum - $limit > 0) ) {

                        // echo 456;

                        // 第一组可分页面数
                        $p = (int) floor(self::$currentTotalNum  / self::$newLimit);
                        // 第一组余量数
                        $n = self::$currentTotalNum  % self::$newLimit;

                        // 第二组的偏移量
                        self::$offset = ($page - 1 - $p) * self::$newLimit - $n;
                    }               

                }
                
                // 路由设置模式
                $routeRewrite = config('taoler.url_rewrite.article_as');

                if(!is_null($cate)) {
                    // 往datas数组中追加cate和url 减少查询
                    foreach($datas as &$da) {
                        $da['cate'] = ['catename' => $cate['catename'], 'ename' => $cate['ename']];

                        $id = IdEncode::encode($da['id']);
                        
                        if(empty($routeRewrite)) {
                            $da['url'] = (string) Route::buildUrl('article_detail', ['id' => $id,'ename' => $cate['ename']])->domain(true);
                            // $da['url'] = (string) Route::buildUrl("{$cate['ename']}/{$id}")->domain(true);
                        } else {
                            $da['url'] = (string) Route::buildUrl('article_detail', ['id' => $id])->domain(true);
                        }
                        
                        // $da['master_pic'] = $da['has_image'] > 0 ? $da['media']['images'][0] : '';
                    }
                } else {
                    // 往datas数组中追加cate和url 减少查询
                    foreach($datas as &$da) {
                        $category = self::field('catename,ename')->where('id', $da['cate_id'])->cache(true)->find();
                        $da['cate'] = ['catename' => $category['catename'], 'ename' => $category['ename']];

                        $id = IdEncode::encode($da['id']);
                        if(empty($routeRewrite)) {
                            $da['url'] = (string) Route::buildUrl('article_detail', ['id' => $id,'ename' => $category['ename']])->domain(true);
                        } else {
                            $da['url'] = (string) Route::buildUrl('article_detail', ['id' => $id])->domain(true);
                        }

                        // $da['master_pic'] = $da['has_image'] > 0 ? $da['media']['images'][0] : '';
                    }
                }

                unset($da);

                return $datas;

            }, 600);
        }

        return [
            'total' => $map['totals'],
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => $lastPage,
            'data' => $data
        ];

    }

    // 获取url
    public function getUrlAttr($value, $data)
    {
        return (string) url('cate', ['ename' => $data['ename']]);
    }

}