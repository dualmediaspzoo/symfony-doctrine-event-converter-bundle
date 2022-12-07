<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity;

use DM\DoctrineEventDistributorBundle\Interfaces\EntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ComplexEntity implements EntityInterface
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=64)
     */
    private $unimportant;

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
