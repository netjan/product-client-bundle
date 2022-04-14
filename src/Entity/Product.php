<?php

declare(strict_types=1);

namespace NetJan\ProductClientBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Product
{
    private $id;

    /**
     * @Assert\NotBlank
     * @Assert\Type("string")
     * @Assert\Length(max=255)
     */
    private $name;

    /**
     * @Assert\NotBlank
     * @Assert\Type("integer")
     * @Assert\GreaterThanOrEqual(0)
     */
    private $amount;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'amount' => $this->amount,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(?int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
