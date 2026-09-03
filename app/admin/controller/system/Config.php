<?php
namespace app\admin\controller\system;

use think\Request;
use think\Response;
use think\facade\View;
use app\entity\SystemConfig;
use app\admin\controller\AdminBaseController;

class Config extends AdminBaseController
{
    protected SystemConfig $entity;

    public function initialize()
    {
        parent::initialize();

        $this->entity = new SystemConfig();
    }

    public function index()
    {
        return View::fetch();
    }

    public function set()
    {
        return View::fetch('config_form');
    }

    /**
     * 获取配置表单页面数据源（分组配置，渲染网站设置表单）
     */
    public function getFormData(Request $request): Response
    {
        return json([
            'code' => 0,
            'msg' => 'ok',
            'data' => $this->entity->getGroupFormList()
        ]);
    }

    /**
     * 批量保存配置表单的值（网站设置页面提交）
     */
    public function batchSave(Request $request): Response
    {
        $post = $request->post();
        $group = trim((string)$request->post('group', ''));
        unset($post['group']); // 移除分组标识，避免当配置项处理
        
        $this->entity->batchSaveValue($post, $group);
        return json(['code' => 0, 'msg' => '保存成功']);
    }

    /**
     * 获取配置项管理列表（增删改配置元数据）
     */
    public function list(Request $request): Response
    {
        $page = $request->post('page',1);
        $limit = $request->post('limit',20);
        $param = $request->post('search-word','');
        $data = $this->entity->getConfigList($param, $page, $limit);
        return json([
            'code'=>0,
            'msg'=>'ok',
            'count'=>$data['total'],
            'data'=>$data['list']
        ]);
    }

    /**
     * 保存配置项（新增/编辑配置元）
     */
    public function save(Request $request): Response
    {
        $data = $request->post();
        // var_dump($data); // 调试输出
        // halt($data);
        $id = $this->entity->saveItem($data);
        return json(['code'=>0,'msg'=>'保存成功','data'=>['id'=>$id]]);
    }

    /**
     * 删除配置项
     */
    public function delete(Request $request): Response
    {
        $id = $request->post('id',0);
        $ok = $this->entity->deleteItem($id);
        if(!$ok){
            return json(['code'=>1,'msg'=>'删除失败']);
        }
        return json(['code'=>0,'msg'=>'删除成功']);
    }

    /**
     * 获取单个配置值，API对外调用
     */
    public function getConfig(Request $request): Response
    {
        $name = $request->get('name','');
        $val = $this->entity->getValue($name);
        return json(['code'=>0,'data'=>$val]);
    }

    /**
     * 获取全部配置键值对，给前台使用
     */
    public function getAll(): Response
    {
        return json(['code'=>0,'data'=>$this->entity->getAllConfig()]);
    }

    /**
     * 更新配置项排序（表格双击编辑sort）
     */
    public function updateSort(Request $request): Response
    {
        $id   = (int)$request->post('id', 0);
        $sort = (int)$request->post('sort', 0);

        if ($id <= 0) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        $this->entity->updateSort($id, $sort);
        return json(['code' => 0, 'msg' => '排序已更新']);
        
    }

    /**
     * 更新配置项启用状态（表格内switch开关）
     */
    public function updateStatus(Request $request): Response
    {
        $id     = (int)$request->post('id', 0);
        $status = (int)$request->post('status', 0);

        if ($id <= 0) {
            return json(['code' => 1, 'msg' => '参数错误']);
        }

        $this->entity->updateStatus($id, $status);
        return json(['code' => 0, 'msg' => '状态已更新']);
        
    }



}
