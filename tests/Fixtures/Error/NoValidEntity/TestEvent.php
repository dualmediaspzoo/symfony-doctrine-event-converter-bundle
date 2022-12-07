<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\NoValidEntity;

use DM\DoctrineEventDistributorBundle\Annotation\SubEvent;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;

/**
 * @SubEvent("SomeName", fields="someField")
 */
class TestEvent extends AbstractEntityEvent
{
}
