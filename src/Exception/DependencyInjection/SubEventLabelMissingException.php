<?php

namespace DM\DoctrineEventDistributorBundle\Exception\DependencyInjection;

use DM\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;

final class SubEventLabelMissingException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'Label for SubEvent on class %s for entities %s must be specified';
}
