<?php

namespace DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use DualMedia\DoctrineEventDistributorBundle\Interfaces\EntityInterface;

#[ORM\Entity]
class ComplexEntity implements EntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $status = null;

    #[ORM\Column(type: 'string', length: 64)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 64)]
    private ?string $unimportant = null;

    public function getId()
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(
        int $status
    ): self {
        $this->status = $status;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(
        string $name
    ): self {
        $this->name = $name;

        return $this;
    }

    public function getUnimportant(): ?string
    {
        return $this->unimportant;
    }

    public function setUnimportant(
        ?string $unimportant
    ): self {
        $this->unimportant = $unimportant;

        return $this;
    }
}
