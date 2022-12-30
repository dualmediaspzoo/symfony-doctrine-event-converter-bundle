<?php

namespace DualMedia\DoctrineEventDistributorBundle\Exception\DependencyInjection;

use DualMedia\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;
use DualMedia\DoctrineEventDistributorBundle\Interfaces\EntityInterface;

/**
 * Thrown during compiler pass processing if a class does not implement {@link EntityInterface}
 */
final class EntityInterfaceMissingException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Class %s does not implement interface %s (inferred from event class %s) which is required for events';
}
