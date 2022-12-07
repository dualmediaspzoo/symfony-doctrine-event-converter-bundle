<?php

namespace DM\DoctrineEventDistributorBundle\Annotation;

use Doctrine\ORM\Events;

/**
 * This annotation will cause a class to have an appropriate type event created of it and later dispatched if needed
 *
 * @Annotation
 * @Target({"CLASS"})
 */
final class PreUpdateEvent extends Event
{
    protected string $type = Events::preUpdate;
}
