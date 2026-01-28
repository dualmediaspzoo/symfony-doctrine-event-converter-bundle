<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Model;

use DualMedia\Common\Interface\IdentifiableInterface;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;

readonly class Delayed
{
    /**
     * @param AbstractEntityEvent<IdentifiableInterface> $event
     * @param class-string<IdentifiableInterface> $class
     */
    public function __construct(
        public AbstractEntityEvent $event,
        public string $class,
        public string $objectSplHash,
        public int|string|null $id
    ) {
    }
}
