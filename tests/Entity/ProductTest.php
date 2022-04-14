<?php

namespace NetJan\ProductClientBundle\Tests\Entity;

use NetJan\ProductClientBundle\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private Product $entityTest;

    public function setUp(): void
    {
        $this->entityTest = new Product();
    }

    public function testCreateEmptyProduct(): void
    {
        $this->assertInstanceOf(Product::class, $this->entityTest);
        $this->assertNull($this->entityTest->getId());
        $this->assertNull($this->entityTest->getName());
        $this->assertNull($this->entityTest->getAmount());

        $data = $this->entityTest->toArray();
        foreach ($data as $item) {
            $this->assertNull($item);
        }

        $product = new Product(1);
        $this->assertEquals(1, $product->getId());
    }

    public function propertyGetSet(): \Generator
    {
        yield ['name', 'StringValue'];
        yield ['amount', 1];
    }

    /**
     * @dataProvider propertyGetSet
     */
    public function testGetSet(string $propertyName, $expectedValue): void
    {
        $setMethod = 'set'.\ucfirst($propertyName);
        $this->entityTest->$setMethod($expectedValue);
        $getMethod = 'get'.\ucfirst($propertyName);
        $actual = $this->entityTest->$getMethod();
        $this->assertSame($expectedValue, $actual);
        $this->assertEquals($expectedValue, $actual);

        $data = $this->entityTest->toArray();
        foreach ($data as $key => $value) {
            if ($key === $propertyName) {
                $this->assertSame($expectedValue, $value);
                $this->assertEquals($expectedValue, $value);
            }
        }
    }

    public function propertyAssertProvider(): array
    {
        return [
            ['name', '@Assert\NotBlank'],
            ['name', '@Assert\Type("string")'],
            ['name', '@Assert\Length(max=255)'],
            ['amount', '@Assert\NotBlank'],
            ['amount', '@Assert\Type("integer")'],
            ['amount', '@Assert\GreaterThanOrEqual(0)'],
        ];
    }

    /**
     * @dataProvider propertyAssertProvider
     */
    public function testAssertAnnotationSetOnProperty(string $propertyName, string $expectedAnnotation): void
    {
        $property = new \ReflectionProperty(Product::class, $propertyName);
        $result = $property->getDocComment();

        self::assertStringContainsString(
            $expectedAnnotation,
            $result,
            sprintf('%s::%s does not contain "%s" in the docBlock.', Product::class, $propertyName, $expectedAnnotation)
        );
    }
}
