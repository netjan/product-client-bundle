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
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('li', 'This value should not be blank.');

        $this->client->submitForm('Zapisz', [
            'product[name]' => 'Fabien',
            'product[amount]' => '',
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('li', 'This value should not be blank.');

        $this->client->submitForm('Zapisz', [
            'product[name]' => '',
            'product[amount]' => 6,
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('li', 'This value should not be blank.');

        $crawler = $this->client->submitForm('Zapisz', [
            'product[name]' => 'Fabien',
            'product[amount]' => 'asdd',
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('li', 'This value is not valid.');

        $this->client->submitForm('Zapisz', [
            'product[name]' => 'Fabien',
            'product[amount]' => -1,
        ]);
        $this->assertResponseIsSuccessful();
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
        $crawler = $this->client->request('GET', '/client/product/');
        $this->assertResponseIsSuccessful();

        $link = $crawler->selectLink('Edycja')->link();
        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Aktualizuj')->form();
        $values = $form->getValues();

        $form_data = [
            'product[name]' => 'Fabien',
            'product[amount]' => 100,
        ];
        $this->client->submit($form, $form_data);
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $newValues = $crawler->selectButton('Aktualizuj')->form()->getValues();

        foreach ($form_data as $key => $value) {
            $this->assertEquals($newValues[$key], $value);
        }

        $this->client->submit($form, [
            'product[name]' => $values['product[name]'],
            'product[amount]' => $values['product[amount]'],
        ]);
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();


    }
}
