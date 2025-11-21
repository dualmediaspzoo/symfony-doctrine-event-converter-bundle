<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use DualMedia\DoctrineEventConverterBundle\Interface\EntityInterface;

#[ORM\Entity]
class ComplexEntity implements EntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int|null $id = null;

    #[ORM\Column(type: 'smallint')]
    private int|null $status = null;

    #[ORM\Column(type: 'string', length: 64)]
    private string|null $name = null;

    #[ORM\Column(type: 'string', length: 64)]
    private string|null $unimportant = null;

    public function getId()
    {
        return $this->id;
    }

    public function getStatus(): int|null
    {
        return $this->status;
    }

    public function setStatus(
        int $status,
    ): self {
        $this->status = $status;

        return $this;
    }

    public function getName(): string|null
    {
        return $this->name;
    }

    public function setName(
        string $name,
    ): self {
        $this->name = $name;

        return $this;
    }

    public function getUnimportant(): string|null
    {
        return $this->unimportant;
    }

    public function setUnimportant(
        string|null $unimportant,
    ): self {
        $this->unimportant = $unimportant;

        return $this;
    }
}
