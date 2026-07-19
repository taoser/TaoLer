<?php
namespace app\admin\controller\product;

use app\admin\controller\AdminBaseController;
use think\facade\View;
use think\facade\Db;
use think\facade\Request;

use app\model\ProductSpecValue as ProductSpecValueModel;

class ProductSpecValue extends AdminBaseController
{
    public function index()
    {
        return View::fetch();
    }

    public function list()
    {
        $res = Db::name('product_spec_value')
        ->alias('psv')
        ->join('product_spec ps','psv.product_spec_id = ps.id')
        ->field('psv.*,ps.title as spec_title')
        ->order('psv.product_spec_id','asc')
        ->select();

        return json(['code'=>0,'msg'=>'success','data'=>$res]);
    }

    public function add()
    {
        if(Request::isAjax()){
            $data = $this->request->param(['product_spec_id','title','sort']);
            $res = ProductSpecValueModel::create($data);
            if(!$res->id){
                return json(['code'=>1,'msg'=>'error']);
            }
            return json(['code'=>0,'msg'=>'success']);
            
        }

        $spec = Db::name('product_spec')->field('id,title')->select();
        View::assign('spec', $spec);

        return View::fetch();
    }

    public function edit()
    {
        if(Request::isAjax()){
            $data = $this->request->param(['id','product_spec_id','title','sort']);
            $res = ProductSpecValueModel::update($data);
            if(!$res){
                return json(['code'=>1,'msg'=>'error']);
            }
            return json(['code'=>0,'msg'=>'success']);
        }

        $spec = Db::name('product_spec')->select();
        View::assign('spec',$spec);

        return View::fetch();
    }

    public function info()
    {
        $id = $this->request->param('id');
        
        $spec = ProductSpecValueModel::find($id);
        if(!$spec){
            return json(['code'=>1,'msg'=>'error']);
        }
        return json(['code'=>0,'msg'=>'success','data' => $spec]);
    }

}   