<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\NoValidEntity;

use DualMedia\DoctrineEventConverterBundle\Attribute\SubEvent;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;
use DualMedia\DoctrineEventConverterBundle\Model\Change;

#[SubEvent('SomeName', changes: [new Change('someField')])]
class TestEvent extends AbstractEntityEvent
{
}
