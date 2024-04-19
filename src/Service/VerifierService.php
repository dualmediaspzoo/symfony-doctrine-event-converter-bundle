<?php

namespace DualMedia\DoctrineEventConverterBundle\Service;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Interfaces\EntityInterface;
use DualMedia\DoctrineEventConverterBundle\Model\SubEvent;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class VerifierService
{
    public function __construct(
        private readonly PropertyAccessor $propertyAccess = new PropertyAccessor()
    ) {
    }

    /**
     * @param array<string, array{0: mixed, 1: mixed}> $changes
     */
    public function validate(
        array $changes,
        SubEvent $model,
        EntityInterface $entity,
        string $event
    ): bool {
        return $this->validateType($event, $model->types)
            && $this->validateRequirements($model->requirements, $entity)
            && $this->validateFields($changes, $model, $event);
    }

    /**
     * @param array<string, mixed> $requirements
     */
    public function validateRequirements(
        array $requirements,
        EntityInterface $entity
    ): bool {
        foreach ($requirements as $fieldName => $value) {
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

    /**
     * @param array<string, array{0: mixed, 1: mixed}> $changes
     */
    public function validateFields(
        array $changes,
        SubEvent $model,
        string $type
    ): bool {
        if (!in_array($type, [Events::postUpdate, Events::preUpdate], true)) {
            return true;
        }

        if ($model->allMode && count(array_diff_key($model->fields, $changes))) { // Event contains keys that haven't changed
            return false;
        } elseif (!$model->allMode && !count(array_intersect_key($changes, $model->fields))) { // Event doesn't contain any of the required keys
            return false;
        }

        $validFields = [];

        foreach ($changes as $field => $fields) {
            if (!array_key_exists($field, $model->fields)) {
                continue;
            }

            $validFields[$field] = null === ($modelWantedState = $model->fields[$field])
                || $this->validateField($fields, $modelWantedState);
        }

        $reduced = array_reduce($validFields, fn ($carry, $data) => $carry + ((int)$data));

        return !$model->allMode ? $reduced > 0 : $reduced === count($model->fields);
    }

    /**
     * @param array{0: mixed, 1: mixed} $changes
     * @param array{0?: mixed, 1?: mixed} $wantedState
     */
    public function validateField(
        array $changes,
        array $wantedState
    ): bool {
        $count = count($wantedState);

        if (1 === $count) {
            $existingCounter = array_key_exists(0, $wantedState) ? 0 : 1;
            return $this->equals($changes[$existingCounter], $wantedState[$existingCounter]); // @phpstan-ignore-line
        } elseif (2 === $count) {
            /** @var array{0: mixed, 1: mixed} $wantedState */
            return $this->equals($changes[0], $wantedState[0]) && $this->equals($changes[1], $wantedState[1]);
        }

        return false;
    }

    /**
     * @param list<string> $types
     */
    public function validateType(
        string $type,
        array $types
    ): bool {
        return in_array($type, $types, true);
    }

    public function equals(
        mixed $known,
        mixed $expected
    ): bool {
        if ($known === $expected) {
            return true;
        }

        if (!($known instanceof \BackedEnum) && ($expected instanceof \BackedEnum)) {
            return $known === $expected->value;
        }

        return false;
    }
}
