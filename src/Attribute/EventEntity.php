<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Attribute;

/**
 * Use to mark your event class with the information as to which Doctrine entity it should fire on.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
readonly class EventEntity
{
    /**
     * @param class-string $class
     */
    public function __construct(
        public string $class
    ) {
    }
}
