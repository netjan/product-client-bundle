<?php

namespace NetJan\ProductClientBundle\Tests\Repository;

use NetJan\ProductClientBundle\Entity\Product;
use NetJan\ProductClientBundle\Filter\ProductFilter;
use NetJan\ProductClientBundle\Repository\ProductRepository;
use NetJan\ProductClientBundle\Tests\Fixtures\Helper;
use PHPUnit\Framework\TestCase;

class ProductRepositoryTest extends TestCase
{
    public function testCreate(): void
    {
        $client = Helper::getClient(200, '');
        $testRepository = new ProductRepository($client);
        $this->assertInstanceOf(ProductRepository::class, $testRepository);
    }

    public function findData(): array
    {
        $id = 1;
        $name = 'Nazwa';
        $amount = 5;
        $product = new Product($id);
        $product->setName($name);
        $product->setAmount($amount);

        return [
            [
                [],
                1,
                null,
            ],
            [
                ['id' => $id, 'name' => $name, 'amount' => $amount],
                $id,
                $product,
            ],
        ];
    }

    /**
     * @dataProvider findData
     */
    public function testFind(array $body, int $id, $expectedProduct)
    {
        $client = Helper::getClient(200, \json_encode($body));
        $testRepository = new ProductRepository($client);
        $product = $testRepository->find($id);
        $this->assertEquals($expectedProduct, $product);
    }

    public function listData(): array
    {
        $items = [
            [
                'id' => 1, 'name' => 'Nazwa', 'amount' => 1,
            ],
            [
                'id' => 2, 'name' => 'Nazwa', 'amount' => 1,
            ],
        ];

        return [
            [
                $items,
                null,
            ],
            [
                $items,
                true,
            ],
            [
                $items,
                false,
            ],
        ];
    }

    /**
     * @dataProvider listData
     */
    public function testList(array $body, ?bool $stock)
    {
        $client = Helper::getClient(200, \json_encode($body));
        $testRepository = new ProductRepository($client);
        $filter = new ProductFilter();
        $filter->stock = $stock;
        $items = $testRepository->list($filter);
        $this->assertIsArray($items);
        $this->assertSame(2, count($items));
    }

    public function saveData(): \Generator
    {
        $id = 1;
        $name = 'Nazwa';
        $amount = 5;
        $product = new Product($id);
        $product->setName($name);
        $product->setAmount($amount);
        yield [
            ['id' => $id, 'name' => $name, 'amount' => $amount],
            $product,
        ];
    }

    /**
     * @dataProvider saveData
     */
    public function testSave(array $body, Product $product)
    {
        $client = Helper::getClient(200, \json_encode($body));
        $testRepository = new ProductRepository($client);
        $expectedProduct = clone $product;
        $product->setAmount(10);
        $testRepository->save($product);
        $expectedProduct->setAmount(10);
        $this->assertEquals($expectedProduct, $product);
    }
}
