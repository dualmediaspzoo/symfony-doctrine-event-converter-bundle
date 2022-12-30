<?php

namespace DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Error\NotExtendingAbstractEntityEvent;

use DualMedia\DoctrineEventDistributorBundle\Attributes\PostPersistEvent;

#[PostPersistEvent]
class TestEvent
{
}
