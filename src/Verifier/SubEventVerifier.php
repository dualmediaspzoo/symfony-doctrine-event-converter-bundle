<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Verifier;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\DoctrineEventConverterBundle;
use DualMedia\DoctrineEventConverterBundle\Interface\EntityInterface;
use DualMedia\DoctrineEventConverterBundle\Interface\VerifierInterface;
use DualMedia\DoctrineEventConverterBundle\Model\SubEvent;

/**
 * @phpstan-import-type DoctrineChangeArray from DoctrineEventConverterBundle
 */
class SubEventVerifier
{
    /**
     * @param iterable<VerifierInterface> $verifiers
     */
    public function __construct(
        private readonly iterable $verifiers
    ) {
    }

    /**
     * @param DoctrineChangeArray $changes
     * @param string $eventType One of {@link Events}
     */
    public function verify(
        EntityInterface $entity,
        SubEvent $subEvent,
        array $changes,
        string $eventType
    ): bool {
        foreach ($this->verifiers as $verifier) {
            if (!$verifier->verify($entity, $subEvent, $changes, $eventType)) {
                return false;
            }
        }

        return true;
    }
}
