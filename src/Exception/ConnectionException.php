<?php

declare(strict_types=1);

namespace NetJan\ProductClientBundle\Exception;

class ConnectionException extends AbstractException implements ExceptionInterface
{
    protected function getReason(): string
    {
        return 'Connection error';
    }
}
