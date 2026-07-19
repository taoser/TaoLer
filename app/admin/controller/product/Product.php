<?php
namespace app\admin\controller\product;

use app\admin\controller\AdminBaseController;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;

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

    public function add()
    {
        if(Request::isAjax()){
            $data = $this->request->param(['cover','covers','name','price','market_price','sketch','intro','is_recommend','is_new','sort','is_attribute']);
            $res = ProductModel::addOrEdit($data);
            if($res) {
                return json(['code'=>1,'msg'=>'error']);
            }
            return json(['code'=>0,'msg'=>'success']);
            
        }
        return View::fetch();
    }

    public function edit()
    {
        if(Request::isAjax()){
            $data = $this->request->param(['id','title','sort']);
            $res = ProductModel::update($data);
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
        
        $product = ProductModel::find($id);
        if(!$product){
            return json(['code'=>1,'msg'=>'error']);
        }
        return json(['code'=>0,'msg'=>'success','data' => $product]);
    }

}   