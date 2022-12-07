<?php

namespace DM\DoctrineEventDistributorBundle\Exception\Proxy;

use DM\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;

/**
 * Thrown if an event class does not end with "Event" which is required for proper name generation
 */
final class TargetClassNamingSchemeInvalidException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Target class %s does not end with "Event"';
}
