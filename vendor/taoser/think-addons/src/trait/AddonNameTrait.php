<?php
declare(strict_types=1);

namespace taoser\addons\trait;

use think\App;

trait AddonNameTrait
{
    protected $request;

    final protected function getName(): string
    {
        $class = get_class($this);
        [, $name, ] = explode('\\', $class);
        $this->request->addon = $name;

        return $name;
    }
}