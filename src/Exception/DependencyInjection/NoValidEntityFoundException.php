<?php

namespace DualMedia\DoctrineEventDistributorBundle\Exception\DependencyInjection;

use DualMedia\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;

final class NoValidEntityFoundException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'No valid entity class was found for event declared on class %s';
}
