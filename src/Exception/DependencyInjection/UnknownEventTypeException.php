<?php

namespace DM\DoctrineEventDistributorBundle\Exception\DependencyInjection;

use DM\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;

final class UnknownEventTypeException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Unknown values (%s) found in SubEvent types in class %s';
}
