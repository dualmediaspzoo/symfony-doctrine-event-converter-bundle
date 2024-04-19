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
     * @param array<string, array<int, mixed>> $changes
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
     * @param array<string, array<int, mixed>> $changes
     */
    public function validateFields(
        array $changes,
        SubEvent $model,
        string $type
    ): bool {
        if (!in_array($type, [Events::postUpdate, Events::preUpdate], true)) {
            return false;
        }

        if ($model->allMode && count(array_diff_key($model->fieldList, $changes))) { // Event contains keys that haven't changed
            return false;
        } elseif (!$model->allMode && !count(array_intersect_key($changes, $model->fieldList))) { // Event doesn't contain any of the required keys
            return false;
        }

        $validFields = [];

        foreach ($changes as $field => $fields) {
            if (!array_key_exists($field, $model->fieldList)) {
                continue;
            } elseif (null === ($modelWantedState = $model->fieldList[$field])) {
                // if you set null instead of setting null for key 0 you're dumb and #wontfix
                $validFields[$field] = true;
                continue;
            }

            $count = count($modelWantedState);

            if (1 === $count) {
                $existingCounter = array_key_exists(0, $modelWantedState) ? 0 : 1;
                $validFields[$field] = $this->equals($fields[$existingCounter], $modelWantedState[$existingCounter]); // @phpstan-ignore-line
            } elseif (2 === $count) {
                /** @var array{0: mixed, 1: mixed} $modelWantedState */
                $validFields[$field] = $this->equals($fields[0], $modelWantedState[0]) && $this->equals($fields[1], $modelWantedState[1]);
            }
        }

        $reduced = array_reduce($validFields, fn ($carry, $data) => $carry + ((int)$data));

        return !$model->allMode ? $reduced > 0 : $reduced === count($model->fieldList);
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
