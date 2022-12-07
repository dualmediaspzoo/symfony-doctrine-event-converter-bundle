<?php

namespace DM\DoctrineEventDistributorBundle\Exception\Proxy;

use DM\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;

/**
 * Thrown if a proxy directory is not writable
 */
final class DirectoryNotWritable extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Directory %s is not writable';
}
