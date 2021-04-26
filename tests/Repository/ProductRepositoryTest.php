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

        $this->productRepository = $kernel->getContainer()
            ->get(ProductRepository::class);
    }

    public function testList()
    {
        $list = $this->productRepository->getList();
        $this->assertIsArray($list);

        $list = $this->productRepository->getList([
            'stock' => true,
        ]);
        $this->assertIsArray($list);

        $list = $this->productRepository->getList([
            'stock' => false,
        ]);
        $this->assertIsArray($list);
    }


    public function testSaveEmpty()
    {
        $product = new Product();
        $result = $this->productRepository->save($product);
        $this->assertSame(true, $result['error']);
    }

    public function testSave()
    {
        $list = $this->productRepository->getList([
            'stock' => true,
        ]);
        $amountTrue = count($list);

        $list = $this->productRepository->getList([
            'stock' => false,
        ]);
        $amountFalse = count($list);

        $list = $this->productRepository->getList();
        $amount5 = count($list);

        $product = new Product();

        $product->setName('name');
        $product->setAmount(1);
        $result = $this->productRepository->save($product);
        $this->assertSame(false, $result['error']);
        $amountTrue++;

        // amount > 0
        $list = $this->productRepository->getList([
            'stock' => true,
        ]);
        $this->assertSame($amountTrue, count($list));

        $product = clone $product;
        $product->setAmount(0);
        $result = $this->productRepository->save($product);
        $this->assertSame(false, $result['error']);
        $amountFalse++;

        // amount = 0
        $list = $this->productRepository->getList([
            'stock' => false,
        ]);
        $this->assertSame($amountFalse, count($list));

        $product = clone $product;
        $product->setAmount(6);
        $result = $this->productRepository->save($product);
        $this->assertSame(false, $result['error']);
        $amountTrue++;
        $amount5++;

        // amount > 5
        $list = $this->productRepository->getList();
        $this->assertSame($amount5, count($list));

        // amount > 0
        $list = $this->productRepository->getList([
            'stock' => true,
        ]);
        $this->assertSame($amountTrue, count($list));

        $product = clone $product;
        $product->setAmount(5);
        $result = $this->productRepository->save($product);
        $this->assertSame(false, $result['error']);
        $amountTrue++;

        // amount > 5
        $list = $this->productRepository->getList();
        $this->assertSame($amount5, count($list));

        // amount > 0
        $list = $this->productRepository->getList([
            'stock' => true,
        ]);
        $this->assertSame($amountTrue, count($list));

        // amount = 0
        $list = $this->productRepository->getList([
            'stock' => false,
        ]);
        $this->assertSame($amountFalse, count($list));
    }

    public function testSaveAndRemove()
    {
        $list = $this->productRepository->getList([
            'stock' => true,
        ]);
        $amountTrue = count($list);

        $product = new Product();

        $product->setName('name');
        $product->setAmount(1);
        $result = $this->productRepository->save($product);
        $this->assertSame(false, $result['error']);
        $amountTrue++;
        $productId = $product->getId();

        $list = $this->productRepository->getList([
            'stock' => true,
        ]);
        $this->assertSame($amountTrue, count($list));

        $newProduct = $this->productRepository->find($productId);
        $this->assertEquals($product, $newProduct);

        $result = $this->productRepository->remove($product);
        $this->assertSame(false, $result['error']);
        $amountTrue--;

        $list = $this->productRepository->getList([
            'stock' => true,
        ]);
        $this->assertSame($amountTrue, count($list));

        $newProduct = $this->productRepository->find($productId);
        $this->assertEquals(null, $newProduct);
    }
}
