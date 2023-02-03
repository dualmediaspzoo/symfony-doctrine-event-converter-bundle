<?php

namespace DualMedia\DoctrineEventConverterBundle\DependencyInjection\Model;

use DualMedia\DoctrineEventConverterBundle\Interfaces\EntityInterface;

/**
 * @interal
 */
abstract class AbstractEventConfiguration
{
    /**
     * @var non-empty-list<class-string<EntityInterface>>
     */
    private array $entities;

    /**
     * @param non-empty-list<class-string<EntityInterface>> $entities
     */
    public function setEntities(
        array $entities
    ): static {
        $this->entities = $entities;

        return $this;
    }

    /**
     * @return non-empty-list<class-string<EntityInterface>>
     */
    public function getEntities(): array
    {
        return $this->entities;
    }
}
