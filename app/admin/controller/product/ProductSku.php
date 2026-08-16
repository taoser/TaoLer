<?php
namespace app\admin\controller\product;

use app\admin\controller\AdminBaseController;
use think\facade\View;
use think\facade\Db;
use think\Request;

use app\model\product\ProductSku as ProductSkuModel;

class ProductSku extends AdminBaseController
{
    public function index()
    {
        return View::fetch();
    }

    public function list()
    {
        $res = Db::name('product_sku')->select();

        return json(['code'=>0,'msg'=>'success','data'=>$res]);
    }

    public function add(Request $request)
    {
        if(!$request->isPost()){
            return View::fetch();
        }
        
        $data = $request->post(['title','sort']);
        $res = ProductSkuModel::create($data);
        if(!$res->id){
            return json(['code'=>1,'msg'=>'error']);
        }
        return json(['code'=>0,'msg'=>'success']);
    }

    public function edit(Request $request)
    {
        if(!$request->isPost()){
            return View::fetch();
        }

        $data = $request->post(['id','title','sort']);
        $res = ProductSkuModel::update($data);
        if(!$res){
            return json(['code'=>1,'msg'=>'error']);
        }
        return json(['code'=>0,'msg'=>'success']);
    }

    public function info(Request $request)
    {
        $id = $request->get('id');
        
        $sku = ProductSkuModel::find($id);
        if(!$sku){
            return json(['code'=>1,'msg'=>'error']);
        }
        return json(['code'=>0,'msg'=>'success','data' => $sku]);
    }

}