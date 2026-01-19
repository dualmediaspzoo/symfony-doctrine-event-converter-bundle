<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection;

use DualMedia\DoctrineEventConverterBundle\Exception\AbstractEventDistributorException;

class SubEventRemoveEventAfterFlushException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'You cannot register preRemove/postRemove events for afterFlush for class %s';
}
