<?php

namespace DualMedia\DoctrineEventConverterBundle\Event;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use DualMedia\Common\Interface\IdentifiableInterface;
use DualMedia\DoctrineEventConverterBundle\DoctrineEventConverterBundle;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Use this class as a base to creating EntityAware events.
 *
 * @template TEntity of IdentifiableInterface
 * @template TManager of ObjectManager = EntityManagerInterface
 *
 * @phpstan-import-type DoctrineChangeArray from DoctrineEventConverterBundle
 */
abstract class AbstractEntityEvent extends Event
{
    protected string|int|null $id = null;

    /**
     * @var TEntity
     */
    protected IdentifiableInterface $entity;

    /**
     * @var DoctrineChangeArray
     */
    protected array $changes = [];

    /**
     * One of {@link Events}.
     */
    protected string $eventType;

    /**
     * @var TManager
     */
    protected ObjectManager $manager;

    /**
     * @param TEntity $entity
     */
    public function setEntity(
        IdentifiableInterface $entity,
    ): static {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return TEntity
     */
    public function getEntity(): IdentifiableInterface
    {
        return $this->entity;
    }

    /**
     * @param TManager $manager
     */
    public function setManager(
        ObjectManager $manager
    ): static {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return TManager
     */
    public function getManager(): ObjectManager
    {
        return $this->manager;
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
