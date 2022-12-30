<?php

namespace DualMedia\DoctrineEventDistributorBundle\Interfaces;

use DualMedia\DoctrineEventDistributorBundle\Exception\DependencyInjection\EntityInterfaceMissingException;

/**
 * Simple interface to enforce an id getter
 *
 * Must be implemented on any entities that will use events, a runtime error will be thrown on compiler pass processing if this condition is not met
 *
 * @see EntityInterfaceMissingException
 */
interface EntityInterface
{
    /**
     * @return string|int|null
     */
    public function getId();
}
