<?php

namespace DualMedia\DoctrineEventConverterBundle\Attributes;

use Doctrine\ORM\Events;

/**
 * This attribute will cause a class to have an appropriate type event created of it and later dispatched if needed.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class PostRemoveEvent extends Event
{
    protected string $type = Events::postRemove;
}
