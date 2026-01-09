<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Tests\Util;

use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Enum\BackedIntEnum;
use DualMedia\DoctrineEventConverterBundle\Util\ObjectUtil;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('util')]
#[CoversClass(ObjectUtil::class)]
class ObjectUtilTest extends TestCase
{
    #[TestWith([true, 10, 10])]
    #[TestWith([false, 5, 10])]
    #[TestWith([true, 5, BackedIntEnum::Is5])]
    public function testEquals(
        bool $result,
        mixed $known,
        mixed $expected,
    ): void {
        static::assertEquals(
            $result,
            ObjectUtil::equals($known, $expected)
        );
    }
}
