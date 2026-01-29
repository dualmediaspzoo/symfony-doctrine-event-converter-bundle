<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Verifier;

use DualMedia\Common\Interface\IdentifiableInterface;
use DualMedia\DoctrineEventConverterBundle\Interface\VerifierInterface;
use DualMedia\DoctrineEventConverterBundle\Model\SubEvent;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class RequirementVerifier implements VerifierInterface
{
    public function __construct(
        private readonly PropertyAccessor $propertyAccess = new PropertyAccessor()
    ) {
    }

    #[\Override]
    public function verify(
        IdentifiableInterface $entity,
        SubEvent $subEvent,
        array $changes,
        string $eventType
    ): bool {
        foreach ($subEvent->requirements as $fieldName => $value) {
            try {
                if ($this->propertyAccess->getValue($entity, $fieldName) !== $value) {
                    return false;
                }
            } catch (\Throwable) {
                return false;
            }
        }

        return true;
    }
}
