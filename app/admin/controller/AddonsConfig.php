<?php
namespace app\admin\controller;

use think\facade\View;
use think\facade\Validate;
use think\Response;
use Symfony\Component\VarExporter\VarExporter;

class AddonsConfig
{
    protected string $configFile;

    public function __construct()
    {
        $this->configFile = root_path() . 'addons/asite/config.php';
    }

    protected function readConfig(): array
    {
        if (!file_exists($this->configFile)) {
            return [];
        }
        return include $this->configFile;
    }

    protected function writeConfig(array $configData): bool
    {
        $content = "<?php\n/**\n * addons插件系统配置，请勿手动修改\n */\nreturn " . VarExporter::export($configData) . ";\n";
        $dir = dirname($this->configFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return file_put_contents($this->configFile, $content, LOCK_EX) !== false;
    }

    public function index()
    {
        $configRaw = $this->readConfig();
        $configList = [];
        foreach ($configRaw as $key => $item) {
            $item['name'] = $key;
            $configList[] = $item;
        }
        View::assign('configList', $configList);
        return View::fetch('addons_config');
    }

    public function save(): Response
    {
        $post = request()->post();
        $originConfig = $this->readConfig();
        if (empty($originConfig)) {
            return json(['code' => 0, 'msg' => '配置文件不存在']);
        }

        // array、multi_array 完全排除，不走ThinkPHP验证器，规避复合数组指针错乱BUG
        $validateRule = [];
        foreach ($originConfig as $name => $item) {
            if (!empty($item['rule']) && !in_array($item['type'], ['array', 'multi_array'])) {
                $validateRule[$name] = $item['rule'];
            }
        }

        $validate = Validate::rule($validateRule);
        if (!$validate->check($post)) {
            return json(['code' => 0, 'msg' => $validate->getError()]);
        }

        foreach ($originConfig as $name => &$item) {
            $type = $item['type'];

            if (!isset($post[$name])) {
                if ($type === 'checkbox') {
                    $item['value'] = [];
                } elseif ($type === 'bool') {
                    $item['value'] = false;
                } elseif ($type === 'array') {
                    $item['value'] = [];
                }
                continue;
            }

            switch ($type) {
                case 'checkbox':
                    $item['value'] = is_array($post[$name]) ? $post[$name] : [];
                    break;
                case 'bool':
                    $item['value'] = ($post[$name] === '1');
                    break;

                case 'array':
                    $keys  = $post[$name]['key'] ?? [];
                    $vals  = $post[$name]['val'] ?? [];
                    $temp = [];
                    foreach ($keys as $idx => $k) {
                        $k = trim($k);
                        if ($k === '') continue;
                        $temp[$k] = $vals[$idx] ?? '';
                    }
                    $item['value'] = $temp;
                    break;

                case 'multi_array':
                    $jsonStr = $post[$name];
                    $parseData = json_decode($jsonStr, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return json([
                            'code' => 0,
                            'msg' => "【{$item['title']}】JSON格式错误：" . json_last_error_msg()
                        ]);
                    }
                    $item['value'] = $parseData;
                    break;

                case 'image':
                case 'file':
                case 'text':
                case 'number':
                case 'areatext':
                case 'date':
                case 'radio':
                case 'select':
                default:
                    $item['value'] = $post[$name];
                    break;
            }
        }
        unset($item);

        if ($this->writeConfig($originConfig)) {
            return json(['code' => 1, 'msg' => '保存成功']);
        } else {
            return json(['code' => 0, 'msg' => '写入失败，请检查addons目录读写权限']);
        }
    }
}
