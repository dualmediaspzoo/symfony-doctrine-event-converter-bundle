<?php

namespace DM\DoctrineEventDistributorBundle\Exception\Proxy;

use DM\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;

/**
 * Thrown when trying to get a path to a non-proxy class
 */
final class NotProxyClassException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Class %s is not a proxy';
}
