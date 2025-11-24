<?php

namespace DualMedia\DoctrineEventConverterBundle\Model;

/**
 * This class contains the basic fields required for sub events to work properly.
 *
 * @interal
 */
readonly class Event
{
    public function __construct(
        public string $eventClass,
        public bool $afterFlush = false,
    ) {
    }
}
