<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\NotExtendingAbstractEntityEvent;

use DualMedia\DoctrineEventConverterBundle\Attribute\PostPersistEvent;

#[PostPersistEvent]
class TestEvent
{
}
