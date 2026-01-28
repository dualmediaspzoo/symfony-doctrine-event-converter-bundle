<?php

namespace DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection;

use DualMedia\Common\Interface\IdentifiableInterface;
use DualMedia\DoctrineEventConverterBundle\Exception\AbstractEventDistributorException;

/**
 * Thrown during compiler pass processing if a class does not implement {@link IdentifiableInterface}.
 */
final class EntityInterfaceMissingException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Class %s does not implement interface %s (inferred from event class %s) which is required for events';
}
