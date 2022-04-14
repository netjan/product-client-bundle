<?php

namespace NetJan\ProductClientBundle\Tests\Fixtures;

use Psr\Log\NullLogger;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Client as GuzzleClient;
use NetJan\ProductClientBundle\ApiClient\Client;

class Helper
{
    public static function getClient(int $status, $body): Client
    {
        $mock = new MockHandler([
            new Response($status, [], $body),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

        $client = new Client($guzzleClient);
        $client->setLogger(new NullLogger());

        return $client;
    }
}
