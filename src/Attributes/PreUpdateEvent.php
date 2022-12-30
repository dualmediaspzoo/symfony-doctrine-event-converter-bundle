<?php

namespace DualMedia\DoctrineEventDistributorBundle\Attributes;

use Doctrine\ORM\Events;

/**
 * This attribute will cause a class to have an appropriate type event created of it and later dispatched if needed
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class PreUpdateEvent extends Event
{
    protected string $type = Events::preUpdate;
}
