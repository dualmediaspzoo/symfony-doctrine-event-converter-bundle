<?php

namespace DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection;

use DualMedia\DoctrineEventConverterBundle\Exception\AbstractEventDistributorException;

final class AbstractEntityEventNotExtendedException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Class %s must extend %s at some point';
}
