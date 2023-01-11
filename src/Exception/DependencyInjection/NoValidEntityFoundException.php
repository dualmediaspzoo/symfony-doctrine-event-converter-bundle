<?php

namespace DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection;

use DualMedia\DoctrineEventConverterBundle\Exception\AbstractEventDistributorException;

final class NoValidEntityFoundException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'No valid entity class was found for event declared on class %s';
}
