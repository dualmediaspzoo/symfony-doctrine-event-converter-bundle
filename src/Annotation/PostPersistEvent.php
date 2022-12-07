<?php

namespace DM\DoctrineEventDistributorBundle\Annotation;

/**
 * This annotation will cause a class to have an appropriate type event created of it and later dispatched if needed
 *
 * @Annotation
 * @Target({"CLASS"})
 */
final class PostPersistEvent extends Event
{
}
