<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Interface;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\DoctrineEventConverterBundle;
use DualMedia\DoctrineEventConverterBundle\Model\SubEvent;

/**
 * @phpstan-import-type DoctrineChangeArray from DoctrineEventConverterBundle
 */
interface VerifierInterface
{
    /**
     * @param DoctrineChangeArray $changes
     * @param string $eventType One of {@link Events}
     */
    public function verify(
        EntityInterface $entity,
        SubEvent $subEvent,
        array $changes,
        string $eventType
    ): bool;
}
