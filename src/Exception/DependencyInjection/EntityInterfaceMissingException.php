<?php

namespace DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection;

use DualMedia\DoctrineEventConverterBundle\Exception\AbstractEventDistributorException;
use DualMedia\Common\Interface\IdentifiableInterface;

/**
 * Thrown during compiler pass processing if a class does not implement {@link IdentifiableInterface}.
 */
final class EntityInterfaceMissingException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Class %s does not implement interface %s (inferred from event class %s) which is required for events';
}
