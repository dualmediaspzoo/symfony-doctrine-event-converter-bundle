<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Verifier;

use DualMedia\Common\Interface\IdentifiableInterface;
use DualMedia\DoctrineEventConverterBundle\Interface\VerifierInterface;
use DualMedia\DoctrineEventConverterBundle\Model\SubEvent;

class TypeVerifier implements VerifierInterface
{
    #[\Override]
    public function verify(
        IdentifiableInterface $entity,
        SubEvent $subEvent,
        array $changes,
        string $eventType
    ): bool {
        return in_array($eventType, $subEvent->types, true);
    }
}
