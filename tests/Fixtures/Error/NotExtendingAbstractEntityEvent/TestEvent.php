<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Error\NotExtendingAbstractEntityEvent;

use DualMedia\DoctrineEventConverterBundle\Attributes\PostPersistEvent;

#[PostPersistEvent]
class TestEvent
{
}
