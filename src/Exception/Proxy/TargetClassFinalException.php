<?php

namespace DM\DoctrineEventDistributorBundle\Exception\Proxy;

use DM\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;
use DM\DoctrineEventDistributorBundle\Proxy\Generator;

/**
 * Thrown if a class passed to {@link Generator} is final
 */
final class TargetClassFinalException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Target class %s cannot be proxied because it\'s final';
}
