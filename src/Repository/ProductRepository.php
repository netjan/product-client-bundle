<?php

namespace NetJan\ProductClientBundle\Repository;

use NetJan\ProductClientBundle\Entity\Product;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $client;
    private $validator;

    public function __construct(ClientInterface $netjanProductClient, ValidatorInterface $validator)
    {
        $this->client = $netjanProductClient;
        $this->validator = $validator;
    }

    public function find($id)
    {
        $id = (int) $id;
        if (1 > $id) {
            return;
        }
        try {
            $response = $this->client->request('GET', 'products/' . $id);
        } catch (ClientException $e) {
            return;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return 'Connection error';
        }
        $item = \json_decode((string) $response->getBody(), true);
        if (empty($item) || !is_array($item)) {
            return;
        }

        return $this->setProduct($item);
    }

    public function getList(?array $filters = [])
    {
        try {
            if (!isset($filters['stock']) || null === $filters['stock']) {
                // amount > 5
                $response = $this->client->request('GET', 'products');
            } elseif ($filters['stock']) {
                // amount > 0
                $response = $this->client->request('GET', 'products', [
                    'query' => [
                        'stock' => 'true'
                    ],
                ]);
            } else {
                // amount = 0
                $response = $this->client->request('GET', 'products', [
                    'query' => [
                        'stock' => 'false'
                    ],
                ]);
            }
        } catch (ClientException $e) {
            return;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return 'Connection error';
        }

        $items = \json_decode((string) $response->getBody(), true);
        if (empty($items) || !is_array($items)) {
            return;
        }

        $products = [];
        foreach ($items as $item) {
            $product = new Product($item['id']);
            $product->setName($item['name']);
            $product->setAmount($item['amount']);
            $errors = $this->validator->validate($product);
            if (count($errors) > 0) {
                $this->logger->error((string) $errors);
                continue;
            }
            $products[] = $product;
        }
        //  = $body;
        return $products;
    }

    public function save(Product &$product, ?Product $orginalProduct = null)
    {
        $result = [
            'error' => false,
            'messages' => [],
        ];
        $response = null;

        try {
            if (null === $orginalProduct) {
                $response = $this->client->request('POST', 'products', [
                    'json' => [
                        'name' => $product->getName(),
                        'amount' => $product->getAmount(),
                    ],
                ]);
            } elseif (
                $product->getName() != $orginalProduct->getName()
                && $product->getAmount() != $orginalProduct->getAmount()
            ) {
                $response = $this->client->request('PUT', 'products/' . $product->getId(), [
                    'json' => [
                        'name' => $product->getName(),
                        'amount' => $product->getAmount(),
                    ],
                ]);
            } else {
                $data = [];
                if ($product->getName() != $orginalProduct->getName()) {
                    $data = [
                        'name' => $product->getName(),
                    ];
                } else {
                    $data = [
                        'amount' => $product->getAmount(),
                    ];
                }
                $response = $this->client->request('PATCH', 'products/' . $product->getId(), [
                    'json' => $data,
                ]);
            }
        } catch (\Exception $e) {
            $result['error'] = true;
            $result['messages'][] = 'Data saving error!';
            $this->logger->error($e->getMessage());
        }

        if (null !== $response) {
            $item = \json_decode((string) $response->getBody(), true);
            if (empty($item) || !is_array($item)) {
                $result['error'] = true;
                $errorMsg = 'Read saved data error!';
                $result['messages'][] = $errorMsg;
                $this->logger->error($errorMsg);
            } elseif (null === ($product = $this->setProduct($item))) {
                $result['error'] = true;
                $errorMsg = 'Read saved data error!';
                $result['messages'][] = $errorMsg;
                $this->logger->error($errorMsg, $item);
            }
        }

        return $result;
    }

    public function remove(Product $product)
    {
        $result = [
            'error' => false,
            'messages' => [],
        ];

        try {
            $response = $this->client->request('DELETE', 'products/' . $product->getId());
        } catch (\Exception $e) {
            $result['error'] = true;
            $result['messages'][] = 'Data saving error!';
            $this->logger->error($e->getMessage());
        }

        return $result;
    }

    private function setProduct($item)
    {
        if (!is_array($item)) {
            return;
        }
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $id = (int) $propertyAccessor->getValue($item, '[id]');
        if (!$id) {
            return;
        }

        $product = new Product($id);
        $product->setName($propertyAccessor->getValue($item, '[name]'));
        $product->setAmount($propertyAccessor->getValue($item, '[amount]'));
        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            $this->logger->error((string) $errors);
            return;
        }

        return $product;
    }
}
