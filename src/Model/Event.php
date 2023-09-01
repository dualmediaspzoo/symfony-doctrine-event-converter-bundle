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
class Event
{
    public function __construct(
        public readonly string $eventClass,
        public readonly bool $afterFlush = false
    ) {
    }
}
