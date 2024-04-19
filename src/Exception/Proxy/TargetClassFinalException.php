<?php

namespace DualMedia\DoctrineEventConverterBundle\Exception\Proxy;

use DualMedia\DoctrineEventConverterBundle\Exception\AbstractEventDistributorException;
use DualMedia\DoctrineEventConverterBundle\Proxy\Generator;

/**
 * Thrown if a class passed to {@link Generator} is final.
 */
final class TargetClassFinalException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Target class %s cannot be proxied because it\'s final';
}
