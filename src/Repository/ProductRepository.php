<?php

declare(strict_types=1);

namespace NetJan\ProductClientBundle\Repository;

use NetJan\ProductClientBundle\Entity\Product;
use NetJan\ProductClientBundle\Filter\ProductFilter;
use Symfony\Component\PropertyAccess\PropertyAccess;
use NetJan\ProductClientBundle\ApiClient\ClientInterface;
use NetJan\ProductClientBundle\Exception\ExceptionInterface;
use NetJan\ProductClientBundle\Exception\ConnectionException;

class ProductRepository
{
    private const ENTITY_NAME = 'products';

    private ClientInterface $client;

    private ?Product $originalProduct = null;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws ConnectionException
     */
    public function find(int $id): ?Product
    {
        try {
            $data = $this->client->find(self::ENTITY_NAME, $id);
        } catch (ExceptionInterface $e) {
            $this->throwException($e);
        }

        $product = $this->setProduct($data);
        if (null !== $product) {
            $this->originalProduct = clone $product;
        }

        return $product;
    }

    /**
     * @throws ConnectionException
     */
    public function list(ProductFilter $filter): array
    {
        try {
            $items = $this->client->list(self::ENTITY_NAME, $filter->toArray());
        } catch (ExceptionInterface $e) {
            $this->throwException($e);
        }

        $products = [];
        foreach ($items as $item) {
            $products[] = $this->setProduct($item);
        }

        return $products;
    }

    /**
     * @throws ConnectionException
     */
    public function save(Product $product): void
    {
        try {
            if (null === $this->originalProduct) {
                $item = $this->client->post(self::ENTITY_NAME, $product->toArray());
            } elseif ($product == $this->originalProduct) {
                return;
            } else {
                $data = $product->toArray();
                $originalData = $this->originalProduct->toArray();
                foreach ($data as $key => $value) {
                    if ($data[$key] === $originalData[$key]) {
                        unset($data[$key]);
                    }
                }
                if (count($data) === count($originalData)) {
                    $item = $this->client->put(self::ENTITY_NAME, $product->getId(), $data);
                } else {
                    $item = $this->client->patch(self::ENTITY_NAME, $product->getId(), $data);
                }
            }
            $product = $this->setProduct($item);
        } catch (ExceptionInterface $e) {
            $this->throwException($e);
        }
    }

    /**
     * @throws ConnectionException
     */
    public function remove(Product $product): void
    {
        try {
            $this->client->delete(self::ENTITY_NAME, $product->getId());
        } catch (ExceptionInterface $e) {
            $this->throwException($e);
        }
    }

    private function setProduct(array $item): ?Product
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $id = (int) $propertyAccessor->getValue($item, '[id]');
        if (1 > $id) {
            return null;
        }

        $product = new Product($id);
        $product->setName($propertyAccessor->getValue($item, '[name]'));
        $product->setAmount($propertyAccessor->getValue($item, '[amount]'));

        return $product;
    }

    /**
     * @throws ConnectionException
     */
    private function throwException(ExceptionInterface $e): never
    {
        throw new ConnectionException($e);
    }
}
