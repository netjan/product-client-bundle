<?php

namespace NetJan\ProductClientBundle\Tests\Repository;

use GuzzleHttp\Client;
use NetJan\ProductClientBundle\Entity\Product;
use NetJan\ProductClientBundle\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validation;

class ProductRepositoryTest extends KernelTestCase
{
    private $productRepository;
    private $validator;

    protected static function getKernelClass(): string
    {
        require_once __DIR__ . '/../Fixtures/App/src/Kernel.php';

        return 'App\Kernel';
    }

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $client = $this->entityManager = $kernel->getContainer()
            ->get('eight_points_guzzle.client.netjan_product');
        $validator = Validation::createValidator();

        $this->productRepository = new ProductRepository($client, $validator);
    }

    public function testList()
    {
        $list = $this->productRepository->getList();

        $this->assertSame(true, is_array($list));

        $list = $this->productRepository->getList([
            'stock' => true,
        ]);

        // $this->assertSame([], $list);

        $list = $this->productRepository->getList([
            'stock' => false,
        ]);

        // $this->assertSame([], $list);
    }

    public function testSaveEmpty()
    {
        $product = new Product();
        $result = $this->productRepository->save($product);
        $this->assertSame(true, $result['error']);
    }

    public function testSave()
    {
        $product = new Product();

        $product->setName('name');
        $product->setAmount(1);
        $result = $this->productRepository->save($product);
        $this->assertSame(false, $result['error']);

        // amount > 0
        $list = $this->productRepository->getList([
            'stock' => true,
        ]);
        // $this->assertSame(1, count($list));

        $product = clone $product;
        $product->setAmount(0);
        $result = $this->productRepository->save($product);
        $this->assertSame(false, $result['error']);

        // amount = 0
        $list = $this->productRepository->getList([
            'stock' => false,
        ]);
        // $this->assertSame(1, count($list));

        $product = clone $product;
        $product->setAmount(6);
        $result = $this->productRepository->save($product);
        $this->assertSame(false, $result['error']);

        // amount > 5
        $list = $this->productRepository->getList();
        // $this->assertSame(1, count($list));

        // amount > 0
        $list = $this->productRepository->getList([
            'stock' => true,
        ]);
        // $this->assertSame(2, count($list));

        $product = clone $product;
        $product->setAmount(5);
        $result = $this->productRepository->save($product);
        $this->assertSame(false, $result['error']);

        // amount > 5
        $list = $this->productRepository->getList();
        // $this->assertSame(1, count($list));

        // amount > 0
        $list = $this->productRepository->getList([
            'stock' => true,
        ]);
        // $this->assertSame(3, count($list));

        // amount = 0
        $list = $this->productRepository->getList([
            'stock' => false,
        ]);
        // $this->assertSame(1, count($list));
    }

    public function testSaveAndRemove()
    {
        $product = new Product();

        $product->setName('name');
        $product->setAmount(1);
        $result = $this->productRepository->save($product);
        $this->assertSame(false, $result['error']);

        $list = $this->productRepository->getList([
            'stock' => true,
        ]);
        // $this->assertSame(1, count($list));

        $result = $this->productRepository->remove($product);
        // $this->assertSame(false, $result['error']);

        $list = $this->productRepository->getList([
            'stock' => true,
        ]);
        // $this->assertSame(0, count($list));
    }
}
