<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use DualMedia\DoctrineEventConverterBundle\Interfaces\EntityInterface;

#[ORM\Entity]
class Item implements EntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $status = null;

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
}
