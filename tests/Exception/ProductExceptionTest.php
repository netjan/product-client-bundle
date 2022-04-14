<?php

namespace NetJan\ProductClientBundle\Tests\Exception;

use NetJan\ProductClientBundle\Exception\ApiClientException;
use NetJan\ProductClientBundle\Exception\ConnectionException;
use NetJan\ProductClientBundle\Exception\ExceptionInterface;
use PHPUnit\Framework\TestCase;

class ProductExceptionTest extends TestCase
{
    public function exceptionDataProvider(): \Generator
    {
        yield [
            new ApiClientException(),
            'API error',
        ];
        yield [
            new ConnectionException(),
            'Connection error',
        ];
    }

    public function classesDataProvider(): array
    {
        return [
            [ApiClientException::class],
            [ConnectionException::class],
        ];
    }

    /**
     * @dataProvider exceptionDataProvider
     */
    public function testGetMessage(ExceptionInterface $exception, string $message): void
    {
        self::assertSame($message, $exception->getMessage());
    }

    /**
     * @dataProvider exceptionDataProvider
     */
    public function testImplementsExceptionInterface(ExceptionInterface $exception): void
    {
        self::assertInstanceOf(ExceptionInterface::class, $exception);
    }

    /**
     * @dataProvider classesDataProvider
     */
    public function testThrow(string $exception)
    {
        $this->expectException($exception);

        throw new $exception();
    }
}
