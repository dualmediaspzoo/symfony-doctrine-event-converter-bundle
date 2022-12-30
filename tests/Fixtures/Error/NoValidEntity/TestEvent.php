<?php

namespace DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Error\NoValidEntity;

use DualMedia\DoctrineEventDistributorBundle\Attributes\SubEvent;
use DualMedia\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;

#[SubEvent("SomeName", fields: "someField")]
class TestEvent extends AbstractEntityEvent
{
}
