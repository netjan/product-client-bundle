<?php

namespace NetJan\ProductClientBundle\Tests\ApiClient;

use NetJan\ProductClientBundle\Exception\ApiClientException;
use NetJan\ProductClientBundle\Tests\Fixtures\Helper;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Exception\JsonException;

class ClientTest extends TestCase
{
    private const ENTITY_NANE = 'products';

    public function productData(): array
    {
        return [
            [
                0,
                [],
            ],
            [
                1,
                [
                    'id' => 1,
                ],
            ],
        ];
    }

    /**
     * @dataProvider productData
     */
    public function testFind(int $id, array $expected)
    {
        $client = Helper::getClient(200, \json_encode($expected));
        $actual = $client->find(self::ENTITY_NANE, $id);
        $this->assertEquals($expected, $actual);
    }

    public function testList()
    {
        $client = Helper::getClient(200, \json_encode([]));
        $actual = $client->list(self::ENTITY_NANE);
        $this->assertEquals([], $actual);
    }

    public function testPost()
    {
        $client = Helper::getClient(200, \json_encode([]));
        $actual = $client->post(self::ENTITY_NANE, []);
        $this->assertEquals([], $actual);
    }

    public function testPut()
    {
        $client = Helper::getClient(200, \json_encode([]));
        $actual = $client->put(self::ENTITY_NANE, 1, []);
        $this->assertEquals([], $actual);
    }

    public function testPatch()
    {
        $client = Helper::getClient(200, \json_encode([]));
        $actual = $client->patch(self::ENTITY_NANE, 1, []);
        $this->assertEquals([], $actual);
    }

    public function testDelete()
    {
        $client = Helper::getClient(200, '');
        $actual = $client->delete(self::ENTITY_NANE, 1);
        $this->assertEquals(null, $actual);
    }

    public function jsonExceptionData(): array
    {
        return [
            ['', 'Request body is empty.'],
            ['invalid json', 'Could not decode request body.'],
            [\json_encode(''), 'JSON content was expected to decode to an array, "string" returned.'],
        ];
    }

    /**
     * @dataProvider jsonExceptionData
     */
    public function testThrowJsonException(string $body, string $exceptionMessage)
    {
        $client = Helper::getClient(200, $body);

        $this->expectException(JsonException::class);
        $this->expectExceptionMessage($exceptionMessage);
        $actual = $client->find(self::ENTITY_NANE, 1);
    }

    public function testResponse404()
    {
        $client = Helper::getClient(404, '');
        $actual = $client->find(self::ENTITY_NANE, 1);
        $this->assertEquals([], $actual);
    }

    public function testThrowException()
    {
        $client = Helper::getClient(500, '');

        $this->expectException(ApiClientException::class);
        $this->expectExceptionMessage('API error');
        $actual = $client->find(self::ENTITY_NANE, 1);
    }
}
