<?php

namespace DualMedia\DoctrineEventConverterBundle\Model;

use JetBrains\PhpStorm\Immutable;

/**
 * This object represents a database change.
 *
 * If no exact changes are specified then it is assumed that _any_ change should trigger the event
 */
#[Immutable]
class Change
{
    public function __construct(
        public readonly string $name,
        public readonly mixed $from = new Undefined(),
        public readonly mixed $to = new Undefined()
    ) {
    }
}
