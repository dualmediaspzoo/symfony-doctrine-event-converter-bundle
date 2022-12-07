<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Error\NotExtendingAbstractEntityEvent;

use DM\DoctrineEventDistributorBundle\Annotation\PostPersistEvent;

/**
 * @PostPersistEvent()
 */
class TestEvent
{
}
