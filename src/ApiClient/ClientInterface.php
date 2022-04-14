<?php

declare(strict_types=1);

namespace NetJan\ProductClientBundle\ApiClient;

use NetJan\ProductClientBundle\Exception\ExceptionInterface;

interface ClientInterface
{
    /**
     * @throws ExceptionInterface
     */
    public function find(string $entity, int $id): array;

    /**
     * @throws ExceptionInterface
     */
    public function list(string $entity, array $filter = []): array;

    /**
     * @throws ExceptionInterface
     */
    public function post(string $entity, array $data): array;

    /**
     * @throws ExceptionInterface
     */
    public function put(string $entity, int $id, array $data): array;

    /**
     * @throws ExceptionInterface
     */
    public function patch(string $entity, int $id, array $data): array;

    /**
     * @throws ExceptionInterface
     */
    public function delete(string $entity, int $id): void;
}
