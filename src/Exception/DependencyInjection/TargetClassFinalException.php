<?php

namespace DM\DoctrineEventDistributorBundle\Exception\DependencyInjection;

use DM\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;

final class TargetClassFinalException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Class %s cannot be final';
}
