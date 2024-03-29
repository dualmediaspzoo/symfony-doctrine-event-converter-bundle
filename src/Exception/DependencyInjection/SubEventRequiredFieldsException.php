<?php

namespace DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection;

use DualMedia\DoctrineEventConverterBundle\Exception\AbstractEventDistributorException;

final class SubEventRequiredFieldsException extends AbstractEventDistributorException
{
    protected const MESSAGE_TEMPLATE = 'SubEvent with label %s in class %s does not contain either of fields or requirements, you must set either of those fields';
}
