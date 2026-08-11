<?php
declare(strict_types=1);

namespace taoser\addons\trait;

use think\facade\View;

trait AddonViewTrait
{
    protected $view;
    protected $addon_path;

    protected function initializeView(): void
    {
        $this->view = clone View::engine('Think');
        $this->view->config([
            'strip_space' => true,
            'view_path' => $this->addon_path . 'view' . DIRECTORY_SEPARATOR
        ]);
    }

    protected function fetch(string $template = '', array $vars = []): string
    {
        return $this->view->fetch($template, $vars);
    }

    protected function display(string $content = '', array $vars = []): string
    {
        return $this->view->display($content, $vars);
    }

    protected function assign($name, $value = '')
    {
        if (is_array($name)) {
            $this->view->assign($name);
        } else {
            $this->view->assign([$name => $value]);
        }

        return $this;
    }

    protected function engine($engine)
    {
        $this->view->engine($engine);

        return $this;
    }
}