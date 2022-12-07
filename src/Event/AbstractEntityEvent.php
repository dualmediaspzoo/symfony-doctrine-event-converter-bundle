<?php

namespace DM\DoctrineEventDistributorBundle\Event;

use DM\DoctrineEventDistributorBundle\Interfaces\EntityInterface;
use Doctrine\ORM\Events;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Use this class as a base to creating EntityAware events
 *
 * @template T of EntityInterface
 */
abstract class AbstractEntityEvent extends Event
{
    /**
     * @var string|int|null
     */
    protected $id = null;

    /**
     * @var EntityInterface
     * @psalm-var T
     */
    protected EntityInterface $entity;
    protected array $changes = [];
    protected string $eventType;

    /**
     * Override if you don't want to specify entities inside of annotations each time for an event
     * This will be used only if your annotations don't specify an entity or entity list outright
     *
     * @return string|null
     * @psalm-return class-string<T>|null
     * @psalm-pure
     */
    public static function getEntityClass(): ?string
    {
        return null;
    }

    /**
     * @psalm-param T $entity
     * @return static
     */
    public function setEntity(
        EntityInterface $entity
    ): self {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return EntityInterface
     * @psalm-return T
     */
    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }

    /**
     * @return static
     */
    public function setChanges(
        array $fields
    ): self {
        $this->changes = $fields;

        return $this;
    }

    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * @return static
     */
    public function setEventType(
        string $enum
    ): self {
        $this->eventType = $enum;

        return $this;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * @param int|string|null $id
     *
     * @return static
     */
    public function setDeletedId(
        $id
    ): self {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|string|null
     */
    public function getDeletedId()
    {
        return $this->id;
    }

    /**
     * @return int|null|string
     */
    public function getEntityId()
    {
        if (Events::postRemove === $this->getEventType()) {
            return $this->getDeletedId();
        }

        return $this->getEntity()->getId();
    }
}
