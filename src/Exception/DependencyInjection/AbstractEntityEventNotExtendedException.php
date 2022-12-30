<?php

namespace DualMedia\DoctrineEventDistributorBundle\Exception\DependencyInjection;

use DualMedia\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;

final class AbstractEntityEventNotExtendedException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Class %s must extend %s at some point';
}
