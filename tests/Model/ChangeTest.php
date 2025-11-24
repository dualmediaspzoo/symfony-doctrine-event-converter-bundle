<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Model;

use DualMedia\DoctrineEventConverterBundle\Model\Change;
use PHPUnit\Framework\TestCase;

class ChangeTest extends TestCase
{
    public function test(): void
    {
        $change = new Change('status', 1, 2);

        static::assertSame('status', $change->name);
        static::assertSame(1, $change->from);
        static::assertSame(2, $change->to);
    }
}
