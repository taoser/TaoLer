<?php
namespace app\admin\controller\product;

use app\admin\controller\AdminBaseController;
use think\facade\View;
use think\facade\Db;
use think\Request;

use app\model\Product as ProductModel;

class Product extends AdminBaseController
{
    public function index()
    {
        return View::fetch();
    }

    public function list()
    {
        $res = Db::name('product')->select();

        return json(['code'=>0,'msg'=>'success','data'=>$res]);
    }

    public function add(Request $request)
    {
        if(!$request->isPost()){
            return View::fetch();
        }
        
        $data = $request->post(['cover','covers','name','price','market_price','sketch','intro','is_recommend','is_new','sort','is_attribute']);
        $res = ProductModel::addOrEdit($data);
        if($res) {
            return json(['code'=>1,'msg'=>'error']);
        }
        return json(['code'=>0,'msg'=>'success']);
    }

    public function edit(Request $request)
    {
        if(!$request->isPost()) {
            return View::fetch();
        }

        $data = $request->post(['id','title','sort']);
        $res = ProductModel::update($data);
        if(!$res){
            return json(['code'=>1,'msg'=>'error']);
        }
        return json(['code'=>0,'msg'=>'success']);
        
    }

    public function info(Request $request)
    {
        $id = $request->get('id/d');
        
        $product = ProductModel::find($id);
        if(!$product){
            return json(['code'=>1,'msg'=>'error']);
        }
        return json(['code'=>0,'msg'=>'success','data' => $product]);
    }

}   