<?php

namespace DualMedia\DoctrineEventDistributorBundle\Exception\DependencyInjection;

use DualMedia\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;

final class SubEventNameCollisionException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Two or more of events for class %s contain the same SubEvent label (%s)';
}
