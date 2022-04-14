<?php

namespace NetJan\ProductClientBundle\Tests;

use NetJan\ProductClientBundle\NetJanProductClientBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetJanProductClientBundleTest extends TestCase
{
    public function testInstance()
    {
        $this->assertInstanceOf(Bundle::class, new NetJanProductClientBundle());
    }

    public function testGetPath()
    {
        $bundle = new NetJanProductClientBundle();
        $expected = \dirname(__DIR__);
        $this->assertSame($expected, $bundle->getPath());
    }
}
