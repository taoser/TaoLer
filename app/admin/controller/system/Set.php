<?php
/*
 * @Author: TaoLer <alipey_tao@qq.com>
 * @Date: 2021-12-06 16:04:50
 * @LastEditTime: 2026-11-04 09:58:37
 * @LastEditors: TaoLer
 * @Description: 设置
 * @FilePath: \TaoLer\app\admin\controller\system\Set.php
 * Copyright (c) 2020~2022 http://www.aieok.com All rights reserved.
 */

namespace app\admin\controller\system;

use app\admin\controller\AdminBaseController;
use think\Request;
use think\Response;
use think\facade\View;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Config;
use app\admin\model\System;
use app\common\helper\FileHelper;
use think\facade\Session;
use think\facade\Cookie;
use taoser\SetArr;
use app\common\helper\SetArr as SetArrConf;

use app\entity\SystemConfig;
use app\entity\SystemGroup;

class Set extends AdminBaseController
{

	protected $sysInfo = '';

	/**
	 * 系统配置实体
	 * @var SystemConfig
	 */
	protected $config;

	/**
	 * 系统分组实体
	 * @var SystemGroup
	 */
	protected $group;
	
	public function initialize()
	{
        parent::initialize();

		$this->config = new SystemConfig();
		$this->group = new SystemGroup();

		$this->sysInfo = $this->getSystem();
	}

	//网站设置显示
	public function index()
    {
		$template = FileHelper::getSubDirNames(root_path().'view');
		$email = Db::name('admin')->where('id',1)->value('email');
		
        View::assign(['sysInfo'=>$this->sysInfo,'template'=>$template,'email'=>$email]);
		
		return View::fetch();
    }

	/**
	 * 系统配置
     */
    public function config(Request $request): string
    {
		return View::fetch();
    }

	 /**
     * 获取配置表单页面数据源（分组配置，渲染网站设置表单）
     */
    public function configGroupList(Request $request): Response
    {
        return json([
            'code' => 0,
            'msg' => 'ok',
            'data' => $this->group->getGroupFormList()
        ]);
    }

    /**
     * 批量保存配置表单的值（网站设置页面提交）
     */
    public function batchSaveConfig(Request $request): Response
    {
        $post = $request->post();
        $systemGroupId = $post['_group'];
        unset($post['_group']); // 移除分组标识，避免当配置项处理
        
        $this->config->batchSaveValue($post, $systemGroupId);
        
        return json(['code' => 0, 'msg' => '保存成功']);
    }
	
    //网站设置
    public function website(Request $request) : Response
    {
		$data = $request->post(['webname','domain','template','cache','upsize','uptype','blackname','webtitle','keywords','descript','state','icp','showlist','copyright']);
		$system = new System();
		$result = $system->sets($data,$this->sysInfo['clevel']);
		if($result == 1){
			return json(['code'=>0,'msg'=>'更新成功']);
		}
		return json(['code'=>-1,'msg'=>$result]);
    }
	
		
}
