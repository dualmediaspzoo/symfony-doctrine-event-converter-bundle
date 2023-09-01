<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Model;

use DualMedia\DoctrineEventConverterBundle\Model\Change;
use DualMedia\DoctrineEventConverterBundle\Tests\KernelTestCase;

class ValidCompileTest extends KernelTestCase
{
    public function testGeneration()
    {
        $change = new Change('status', 1, 2);

        $this->assertSame('status', $change->name);
        $this->assertSame(1, $change->from);
        $this->assertSame(2, $change->to);
    }

}
