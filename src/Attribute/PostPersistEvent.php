<?php

namespace DualMedia\DoctrineEventConverterBundle\Attribute;

/**
 * This attribute will cause a class to have an appropriate type event created of it and later dispatched if needed.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class PostPersistEvent extends Event
{
}
