<?php

namespace DualMedia\DoctrineEventDistributorBundle\Exception\Proxy;

use DualMedia\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;

/**
 * Thrown if a proxy directory is not writable
 */
final class DirectoryNotWritable extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Directory %s is not writable';
}
