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

    public function add()
    {
        if(Request::isPost()){
            $data = $this->request->param(['title','sort']);
            $res = ProductSkuModel::create($data);
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
            $res = ProductSkuModel::update($data);
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
        
        $sku = ProductSkuModel::find($id);
        if(!$sku){
            return json(['code'=>1,'msg'=>'error']);
        }
        return json(['code'=>0,'msg'=>'success','data' => $sku]);
    }

}