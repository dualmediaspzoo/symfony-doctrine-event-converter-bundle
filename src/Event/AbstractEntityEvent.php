<?php

namespace DualMedia\DoctrineEventConverterBundle\Event;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\DoctrineEventConverterBundle;
use DualMedia\DoctrineEventConverterBundle\Interface\EntityInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Use this class as a base to creating EntityAware events.
 *
 * @template T of EntityInterface
 *
 * @phpstan-import-type DoctrineChangeArray from DoctrineEventConverterBundle
 */
abstract class AbstractEntityEvent extends Event
{
    protected string|int|null $id = null;

    /**
     * @var T
     */
    protected EntityInterface $entity;

    /**
     * @var DoctrineChangeArray
     */
    protected array $changes = [];

    /**
     * One of {@link Events}.
     */
    protected string $eventType;

    /**
     * @param T $entity
     *
     * @return static
     */
    public function setEntity(
        EntityInterface $entity,
    ): self {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return T
     */
    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }

    /**
     * @param DoctrineChangeArray $fields
     */
    public function setChanges(
        array $fields,
    ): static {
        $this->changes = $fields;

        return $this;
    }

    /**
     * @return DoctrineChangeArray
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    public function hasChanged(
        string $field
    ): bool {
        return array_key_exists($field, $this->changes);
    }

    public function setEventType(
        string $enum,
    ): static {
        $this->eventType = $enum;

        return $this;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function setDeletedId(
        int|string|null $id,
    ): static {
        $this->id = $id;

        return $this;
    }

    public function getDeletedId(): int|string|null
    {
        return $this->id;
    }

    public function getEntityId(): int|string|null
    {
        if (Events::postRemove === $this->getEventType()) {
            return $this->getDeletedId();
        }

        return $this->getEntity()->getId();
    }
}
