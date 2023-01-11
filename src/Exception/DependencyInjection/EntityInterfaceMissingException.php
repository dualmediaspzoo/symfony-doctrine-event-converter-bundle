<?php

namespace DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection;

use DualMedia\DoctrineEventConverterBundle\Exception\AbstractEventDistributorException;
use DualMedia\DoctrineEventConverterBundle\Interfaces\EntityInterface;

/**
 * Thrown during compiler pass processing if a class does not implement {@link EntityInterface}
 */
final class EntityInterfaceMissingException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Class %s does not implement interface %s (inferred from event class %s) which is required for events';
}
