<?php
namespace app\admin\controller\product;

use app\admin\controller\AdminBaseController;
use think\facade\View;
use think\facade\Db;
use think\Request;

use app\model\ProductSpec as ProductSpecModel;

class ProductSpec extends AdminBaseController
{
    public function index()
    {
        return View::fetch();
    }

    public function list()
    {
        $res = Db::name('product_spec')->select();

        return json(['code'=>0,'msg'=>'success','data'=>$res]);
    }

    public function add()
    {
        if(Request::isPost()){
            $data = $this->request->param(['title','sort']);
            $res = ProductSpecModel::create($data);
            if(!$res->id){
                return json(['code'=>1,'msg'=>'error']);
            }
            return json(['code'=>0,'msg'=>'success']);
            
        }
        return View::fetch();
    }

    public function edit()
    {
        if(Request::isPost()){
            $data = $this->request->param(['id','title','sort']);
            $res = ProductSpecModel::update($data);
            if(!$res){
                return json(['code'=>1,'msg'=>'error']);
            }
            return json(['code'=>0,'msg'=>'success']);
        }

        return View::fetch();
    }

    public function info()
    {
        $id = $this->request->param('id');
        
        $spec = ProductSpecModel::find($id);
        if(!$spec){
            return json(['code'=>1,'msg'=>'error']);
        }
        return json(['code'=>0,'msg'=>'success','data' => $spec]);
    }

    public function getSpecData()
    {
        $res = ProductSpecModel::with(['child' => function($query){
            $query->withField(['product_spec_id','id','title'])->order('id','asc');
        }])
        ->field('id,title,ename,type')
        ->select();

        return json(['code'=>0,'msg'=>'success','data'=>$res]);
    }

    public function getSpec()
    {
        $res = ProductSpecModel::with(['child' => function($query){
            $query->order('id','asc');
        }])
        ->field('id,title')
        ->select();

        return json(['code'=>0,'msg'=>'success','data'=>$res]);
    }


}