<?php

namespace DualMedia\DoctrineEventDistributorBundle\Exception\Proxy;

use DualMedia\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;
use DualMedia\DoctrineEventDistributorBundle\Proxy\Generator;

/**
 * Thrown if a class passed to {@link Generator} is final
 */
final class TargetClassFinalException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Target class %s cannot be proxied because it\'s final';
}
