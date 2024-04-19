<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\NoValidEntity;

use DualMedia\DoctrineEventConverterBundle\Attributes\SubEvent;
use DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent;

#[SubEvent('SomeName', fields: 'someField')]
class TestEvent extends AbstractEntityEvent
{
}
