<?php

namespace DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection;

use DualMedia\DoctrineEventConverterBundle\Exception\AbstractEventDistributorException;

final class UnknownEventTypeException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Unknown values (%s) found in SubEvent types in class %s';
}
