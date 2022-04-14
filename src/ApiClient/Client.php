<?php

declare(strict_types=1);

namespace NetJan\ProductClientBundle\ApiClient;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use NetJan\ProductClientBundle\Exception\ApiClientException;
use NetJan\ProductClientBundle\Exception\ExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Exception\JsonException;

class Client implements ClientInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const CONNECT_TIMEOUT = 5;

    private GuzzleClientInterface $client;

    public function __construct(GuzzleClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @throws ExceptionInterface
     */
    public function find(string $entity, int $id): array
    {
        if (1 > $id) {
            return [];
        }

        return $this->request('GET', $entity.'/'.$id);
    }

    /**
     * @throws ExceptionInterface
     */
    public function list(string $entity, array $filter = []): array
    {
        return $this->request('GET', $entity, [
            'query' => $filter,
        ]);
    }

    /**
     * @throws ExceptionInterface
     */
    public function post(string $entity, array $data): array
    {
        return $this->request('POST', $entity, [
            'json' => $data,
        ]);
    }

    /**
     * @throws ExceptionInterface
     */
    public function put(string $entity, int $id, array $data): array
    {
        return $this->request('PUT', $entity.'/'.$id, [
            'json' => $data,
        ]);
    }

    /**
     * @throws ExceptionInterface
     */
    public function patch(string $entity, int $id, array $data): array
    {
        return $this->request('PATCH', $entity.'/'.$id, [
            'json' => $data,
        ]);
    }

    /**
     * @throws ExceptionInterface
     */
    public function delete(string $entity, int $id): void
    {
        $this->request('DELETE', $entity.'/'.$id);
    }

    private function request(string $method, string $uri, array $options = []): array
    {
        $options = array_merge($options, [
            'connect_timeout' => self::CONNECT_TIMEOUT,
        ]);

        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (ClientException $e) {
            return [];
        } catch (ClientExceptionInterface $e) {
            $this->throwException($e);
        }

        if ('DELETE' === $method) {
            return [];
        }

        return $this->jsonToArray($response->getBody()->getContents());
    }

    /**
     * @throws JsonException When the content cannot be decoded to an array
     */
    private function jsonToArray(string $content): array
    {
        if ('' === $content) {
            throw new JsonException('Request body is empty.');
        }

        try {
            $content = json_decode($content, true, 512, \JSON_BIGINT_AS_STRING | (\PHP_VERSION_ID >= 70300 ? \JSON_THROW_ON_ERROR : 0));
        } catch (\JsonException $e) {
            throw new JsonException('Could not decode request body.', $e->getCode(), $e);
        }

        if (\PHP_VERSION_ID < 70300 && \JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonException('Could not decode request body: '.json_last_error_msg(), json_last_error());
        }

        if (!\is_array($content)) {
            throw new JsonException(sprintf('JSON content was expected to decode to an array, "%s" returned.', get_debug_type($content)));
        }

        return $content;
    }

    /**
     * @param GuzzleClientException|\JsonException $e
     *
     * @throws ApiClientException
     */
    private function throwException($e): never
    {
        $this->logger->error($e->getMessage());
        throw new ApiClientException($e);
    }
}
