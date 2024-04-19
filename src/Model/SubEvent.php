<?php

namespace DualMedia\DoctrineEventConverterBundle\Model;

use JetBrains\PhpStorm\Immutable;

/**
 * This class contains the basic fields required for sub events to work properly.
 *
 * @psalm-immutable
 *
 * @interal
 */
#[Immutable]
class SubEvent
{
    /**
     * @param bool $allMode If all the fields must be meeting the requirements of the event
     * @param array<string, null|array{0?: mixed, 1?: mixed}> $fields The fields that must be changed, null means that any change is required, 0 and 1 indexes match before/after
     * @param array<string, mixed> $requirements Required field states for this event to fire
     * @param list<string> $types Event types in which this event may be triggered
     */
    public function __construct(
        public readonly bool $allMode,
        public readonly array $fields,
        public readonly array $requirements,
        public readonly array $types,
        public readonly bool $afterFlush,
    ) {
    }
}
