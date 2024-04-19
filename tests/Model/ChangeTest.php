<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Model;

use DualMedia\DoctrineEventConverterBundle\Model\Change;
use PHPUnit\Framework\TestCase;

class ChangeTest extends TestCase
{
    public function test(): void
    {
        $change = new Change('status', 1, 2);

        $this->assertSame('status', $change->name);
        $this->assertSame(1, $change->from);
        $this->assertSame(2, $change->to);
    }
}
