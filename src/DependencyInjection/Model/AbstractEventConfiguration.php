<?php

namespace DualMedia\DoctrineEventConverterBundle\DependencyInjection\Model;

use DualMedia\Common\Interface\IdentifiableInterface;

/**
 * @interal
 */
abstract class AbstractEventConfiguration
{
    /**
     * @var non-empty-list<class-string<IdentifiableInterface>>
     */
    private array $entities;

    /**
     * @param non-empty-list<class-string<IdentifiableInterface>> $entities
     */
    public function setEntities(
        array $entities,
    ): static {
        $this->entities = $entities;

        return $this;
    }

    /**
     * @return non-empty-list<class-string<IdentifiableInterface>>
     */
    public function getEntities(): array
    {
        return $this->entities;
    }
}
