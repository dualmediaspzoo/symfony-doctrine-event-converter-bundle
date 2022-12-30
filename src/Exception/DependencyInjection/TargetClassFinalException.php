<?php

namespace DualMedia\DoctrineEventDistributorBundle\Exception\DependencyInjection;

use DualMedia\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;

final class TargetClassFinalException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Class %s cannot be final';
}
