<?php

namespace think\tests\dumper;

use PHPUnit\Framework\TestCase;

class DumperTest extends TestCase
{
    public function testDump()
    {
        dump(['aa' => 'bbb', 'cc' => 'dd']);
        dump($this);
    }
}
