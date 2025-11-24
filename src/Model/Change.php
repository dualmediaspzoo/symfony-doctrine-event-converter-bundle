<?php

namespace DualMedia\DoctrineEventConverterBundle\Model;

/**
 * This object represents a database change.
 *
 * If no exact changes are specified then it is assumed that _any_ change should trigger the event
 */
readonly class Change
{
    public function __construct(
        public string $name,
        public mixed $from = new Undefined(),
        public mixed $to = new Undefined(),
    ) {
    }
}
