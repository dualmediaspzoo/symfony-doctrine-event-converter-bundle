<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\NotExtendingAbstractEntityEvent;

use DM\DoctrineEventDistributorBundle\Attributes\PostPersistEvent;

#[PostPersistEvent]
class TestEvent
{
}
