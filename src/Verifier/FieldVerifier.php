<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Verifier;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Interface\EntityInterface;
use DualMedia\DoctrineEventConverterBundle\Interface\VerifierInterface;
use DualMedia\DoctrineEventConverterBundle\Model\SubEvent;
use DualMedia\DoctrineEventConverterBundle\Util\ObjectUtil;

class FieldVerifier implements VerifierInterface
{
    #[\Override]
    public function verify(
        EntityInterface $entity,
        SubEvent $subEvent,
        array $changes,
        string $eventType
    ): bool {
        if (!in_array($eventType, [Events::postUpdate, Events::preUpdate], true)) {
            return true;
        }

        if ($subEvent->allMode && count(array_diff_key($subEvent->fields, $changes))) { // Event contains keys that haven't changed
            return false;
        } elseif (!$subEvent->allMode && !count(array_intersect_key($changes, $subEvent->fields))) { // Event doesn't contain any of the required keys
            return false;
        }

        $validFields = [];

        foreach ($changes as $field => $fields) {
            if (!array_key_exists($field, $subEvent->fields)) {
                continue;
            }

            $validFields[$field] = null === ($modelWantedState = $subEvent->fields[$field])
                || $this->validateField($fields, $modelWantedState);
        }

        $reduced = array_reduce($validFields, fn ($carry, $data) => $carry + ((int)$data));

        return !$subEvent->allMode ? $reduced > 0 : $reduced === count($subEvent->fields);
    }

    /**
     * @param array{0: mixed, 1: mixed} $changes
     * @param array{0?: mixed, 1?: mixed} $wantedState
     */
    public function validateField(
        array $changes,
        array $wantedState,
    ): bool {
        $count = count($wantedState);

        if (1 === $count) {
            $existingCounter = array_key_exists(0, $wantedState) ? 0 : 1;

            return ObjectUtil::equals($changes[$existingCounter], $wantedState[$existingCounter]); // @phpstan-ignore-line
        } elseif (2 === $count) {
            /** @var array{0: mixed, 1: mixed} $wantedState */
            return ObjectUtil::equals($changes[0], $wantedState[0]) && ObjectUtil::equals($changes[1], $wantedState[1]);
        }

        return false;
    }
}
