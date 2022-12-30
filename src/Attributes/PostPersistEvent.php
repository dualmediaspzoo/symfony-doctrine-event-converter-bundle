<?php

namespace DualMedia\DoctrineEventDistributorBundle\Attributes;

/**
 * This attribute will cause a class to have an appropriate type event created of it and later dispatched if needed
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class PostPersistEvent extends Event
{
}
