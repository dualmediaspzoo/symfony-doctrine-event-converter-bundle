<?php

namespace DM\DoctrineEventDistributorBundle\Exception\DependencyInjection;

use DM\DoctrineEventDistributorBundle\Exception\AbstractEventDistributorException;

final class SubEventRequiredFieldsException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'SubEvent with label % in class %s does not contain either of fields or requirements, you must set either of those fields';
}
