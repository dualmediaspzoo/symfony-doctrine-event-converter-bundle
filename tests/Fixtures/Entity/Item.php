<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use DualMedia\Common\Interface\IdentifiableInterface;

#[ORM\Entity]
class Item implements IdentifiableInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int|null $id = null;

    #[ORM\Column(type: 'smallint')]
    private int|null $status = null;

    #[\Override]
    public function getId(): int|null
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
}
