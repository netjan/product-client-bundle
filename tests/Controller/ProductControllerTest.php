<?php

namespace NetJan\ProductClientBundle\Tests\Tests\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Nelmio\Alice\Loader\NativeLoader;
use NetJan\ProductClientBundle\Entity\Product;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    protected $client;

    protected static function getKernelClass(): string
    {
        require_once __DIR__ . '/../Fixtures/App/src/Kernel.php';

        return 'App\Kernel';
    }

    protected function setUp(): void
    {
        $this->client = self::createClient();

        parent::setUp();
    }

    public function testGet404()
    {
        $this->client->request('GET', '/client/product/non-existant');
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    public function testList()
    {
        $this->client->request('GET', '/client/product');
        $this->assertSame(301, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/client/product/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Lita produktów');
    }
}
