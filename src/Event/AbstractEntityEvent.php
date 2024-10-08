<?php

namespace DualMedia\DoctrineEventConverterBundle\Event;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Interfaces\EntityInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Use this class as a base to creating EntityAware events.
 *
 * @template T of EntityInterface
 */
abstract class AbstractEntityEvent extends Event
{
    protected string|int|null $id = null;

    /**
     * @var T
     *
     * @noinspection PhpDocFieldTypeMismatchInspection
     */
    protected EntityInterface $entity;

    /**
     * @var array<string, mixed>
     */
    protected array $changes = [];
    protected string $eventType;

    /**
     * Override if you don't want to specify entities inside of annotations each time for an event
     * This will be used only if your annotations don't specify an entity or entity list outright.
     *
     * @return class-string<T>|null
     */
    public static function getEntityClass(): string|null
    {
        return null;
    }

    /**
     * @param T $entity
     *
     * @return static
     *
     * @noinspection PhpDocSignatureInspection
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
     * @param array<string, mixed> $fields
     */
    public function setChanges(
        array $fields,
    ): static {
        $this->changes = $fields;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getChanges(): array
    {
        return $this->changes;
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
