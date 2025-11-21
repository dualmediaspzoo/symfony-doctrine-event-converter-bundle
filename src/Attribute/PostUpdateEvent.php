<?php

namespace DualMedia\DoctrineEventConverterBundle\Attribute;

use Doctrine\ORM\Events;

/**
 * This attribute will cause a class to have an appropriate type event created of it and later dispatched if needed.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class PostUpdateEvent extends Event
{
    public const string EVENT_TYPE = Events::postUpdate;
}
