<?php
namespace app\admin\controller\system;

use app\admin\controller\AdminBaseController;
use app\common\helper\HttpHelper;
use think\response\Json;

class Index extends AdminBaseController
{
    public function initialize()
    {
        parent::initialize();
    }

    public function upgradeCheck()
    {
        $response = HttpHelper::withHost()->get('/v1/upload/check', ['pn'=>$this->pn,'ver'=>$this->sys_version])->toJson();

        if($response->code !== -1){
            return $response->code ? "<span style='color:#b2aeae'>有{$response->up_num}个版本需更新,当前可更新至{$response->version}</span>" : $response->msg;
        }

        return lang('No new messages');
    }

    /**
	 * 系统调试
	 * @param string $status 调试状态
	 * @return \think\response\Json
	 */
	public function debugSwitch(): Json
	{
		$status = $this->request->param('status');

		try {
			$envFile = root_path() . '.env';
			
			if(file_exists($envFile)) {
				$envStr = file_get_contents($envFile);
				$envPatk = '/APP_DEBUG[^\r?\n]*/';
				if($status == 'true'){
					$envReps = 'APP_DEBUG = true';
					$msg = '调试打开';
				} else {
					$envReps = 'APP_DEBUG = false';
					$msg = '调试关闭';
				}

				$envStr = preg_replace($envPatk, $envReps, $envStr);

				file_put_contents($envFile, $envStr);
				
				return json(['code' => 0, 'msg' => $msg]);
			}

		} catch (\Exception $e) {
			return json(['code'=>-1,'msg'=>$e->getMessage()]);
		}

		return json(['code' => 0, 'msg' => '调试模式无法切换']);
		
	}

    public function authorizationCheck()
    {
        if(empty($this->sys['key'])) {
			return json(['code' => -1, 'msg' => '请在 系统升级-》配置KEY']);
		}
        $data = ['u'=>$this->sys['domain'],'key'=>$this->sys['key']];

        $response = HttpHelper::withHost()->get('/v1/cy', [
			'u'=>$this->sys['domain'],
			'key'=>$this->sys['key']
		])->toJson();

        if($response->code == 0){
            Db::name('system')->save(['id' => 1, 'clevel' => $response->data->level]);
            return json(['code' => 0, 'msg' => $response->data->info, 'data' => $response->data]);
        }

        return json(['code' => -1, 'msg' => $response->msg]);
    }
}