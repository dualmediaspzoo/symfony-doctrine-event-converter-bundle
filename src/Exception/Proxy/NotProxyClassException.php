<?php

namespace DualMedia\DoctrineEventConverterBundle\Exception\Proxy;

use DualMedia\DoctrineEventConverterBundle\Exception\AbstractEventDistributorException;

/**
 * Thrown when trying to get a path to a non-proxy class.
 */
final class NotProxyClassException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Class %s is not a proxy';
}
