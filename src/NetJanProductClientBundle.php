<?php

namespace NetJan\ProductClientBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetJanProductClientBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
