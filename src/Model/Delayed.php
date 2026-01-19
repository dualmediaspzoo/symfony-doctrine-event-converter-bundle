<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Model;

use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Interface\EntityInterface;

readonly class Delayed
{
    /**
     * @param AbstractEntityEvent<EntityInterface> $event
     * @param class-string<EntityInterface> $class
     */
    public function __construct(
        public AbstractEntityEvent $event,
        public string $class,
        public string $objectSplHash,
        public int|string|null $id
    ) {
    }
}
