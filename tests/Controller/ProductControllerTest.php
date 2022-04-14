<?php

namespace NetJan\ProductClientBundle\Tests\Tests\Controller;

use NetJan\ProductClientBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductControllerTest extends WebTestCase
{
    protected $client;

    protected static function getKernelClass(): string
    {
        require_once __DIR__.'/../Fixtures/TestApp/src/Kernel.php';

        return 'TestApp\Kernel';
    }

    protected function setUp(): void
    {
        $this->client = self::createClient();

        parent::setUp();
    }

    public function getUrls(): ?\Generator
    {
        // product_index
        yield ['GET', '/client/product/', Response::HTTP_OK];
        // product_new
        yield ['GET', '/client/product/new', Response::HTTP_OK];
        yield ['POST', '/client/product/new', Response::HTTP_OK];
        // product_show
        yield ['GET', '/client/product/0', Response::HTTP_NOT_FOUND];
        // product_edit
        yield ['POST', '/client/product/0', Response::HTTP_NOT_FOUND];
        yield ['GET', '/client/product/0/edit', Response::HTTP_NOT_FOUND];
        // product_delete
        yield ['POST', '/client/product/0/edit', Response::HTTP_NOT_FOUND];
    }

    /**
     * @dataProvider getUrls
     */
    public function testUrls(string $method, string $uri, int $statusCode): void
    {
        $this->client->request($method, $uri);

        $this->assertResponseStatusCodeSame($statusCode, sprintf('The %s URL loads correctly.', $uri));
    }

    public function testGet404()
    {
        $this->client->request('GET', '/client/product/non-existant');
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    public function testIndex()
    {
        $this->client->request('GET', '/client/product');
        $this->assertSame(301, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/client/product/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Lita produktów');

        $this->client->clickLink('Znajdują się na składzie');
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', '/client/product/?stock=true');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Lita produktów');

        $this->client->request('GET', '/client/product/?stock=false');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Lita produktów');
    }

    public function testNew()
    {
        $this->client->request('GET', '/client/product/new');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Zapisz');
        $this->assertResponseIsUnprocessable();
        $this->assertSelectorTextContains('li', 'This value should not be blank.');

        $this->client->submitForm('Zapisz', [
            'product[name]' => 'Fabien',
            'product[amount]' => '',
        ]);
        $this->assertResponseIsUnprocessable();
        $this->assertSelectorTextContains('li', 'This value should not be blank.');

        $this->client->submitForm('Zapisz', [
            'product[name]' => '',
            'product[amount]' => 6,
        ]);
        $this->assertResponseIsUnprocessable();
        $this->assertSelectorTextContains('li', 'This value should not be blank.');

        $crawler = $this->client->submitForm('Zapisz', [
            'product[name]' => 'Fabien',
            'product[amount]' => 'asdd',
        ]);
        $this->assertResponseIsUnprocessable();
        $this->assertSelectorTextContains('li', 'This value is not valid.');

        $this->client->submitForm('Zapisz', [
            'product[name]' => 'Fabien',
            'product[amount]' => -1,
        ]);
        $this->assertResponseIsUnprocessable();
        $this->assertSelectorTextContains('li', 'This value should be greater than or equal to 0.');

        $this->client->submitForm('Zapisz', [
            'product[name]' => 'Fabien',
            'product[amount]' => 2,
        ]);
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testShow()
    {
        $this->client->request('GET', '/client/product/new');
        $this->client->submitForm('Zapisz', [
            'product[name]' => 'Fabien',
            'product[amount]' => 6,
        ]);

        $crawler = $this->client->request('GET', '/client/product/');
        $this->assertResponseIsSuccessful();

        $link = $crawler->selectLink('Podgląd')->link();
        $this->client->click($link);
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Delete');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->client->click($link);
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    public function testEdit()
    {
        $this->client->request('GET', '/client/product/new');
        $this->client->submitForm('Zapisz', [
            'product[name]' => 'Fabien',
            'product[amount]' => 6,
        ]);

        $crawler = $this->client->request('GET', '/client/product/');
        $this->assertResponseIsSuccessful();

        $link = $crawler->selectLink('Edycja')->link();
        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Aktualizuj')->form();
        $values = $form->getValues();

        $formData = [
            'product[name]' => 'Fabien',
            'product[amount]' => 100,
        ];
        $this->client->submit($form, $formData);
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $newValues = $crawler->selectButton('Aktualizuj')->form()->getValues();

        foreach ($formData as $key => $value) {
            $this->assertEquals($newValues[$key], $value);
        }

        $this->client->submit($form, [
            'product[name]' => $values['product[name]'],
            'product[amount]' => $values['product[amount]'],
        ]);
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testThrowNotFoundHttpException()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Produkt "1" nie znaleziono.');

        $this->client->catchExceptions(false);
        $this->client->request('GET', '/client/product/1');
        $this->assertResponseIsSuccessful();
    }

    public function testThrowNotFoundHttpExceptionWhenNoRouteFound()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectErrorMessageMatches('/No route found for "(.*)client\/product\/no-route-found"/');

        $this->client->catchExceptions(false);
        $this->client->request('GET', '/client/product/no-route-found');
        $this->assertResponseIsSuccessful();
    }
}
