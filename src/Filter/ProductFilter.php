<?php

declare(strict_types=1);

namespace NetJan\ProductClientBundle\Filter;

class ProductFilter
{
    public ?bool $stock = null;

    public function toArray(): array
    {
        if (null === $this->stock) {
            return [];
        }

        return [
            'stock' => $this->stock,
        ];
    }
}
