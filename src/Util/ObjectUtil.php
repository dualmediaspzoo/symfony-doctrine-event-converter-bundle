<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Util;

class ObjectUtil
{
    private function __construct()
    {
    }

    public static function equals(
        mixed $known,
        mixed $expected,
    ): bool {
        if ($known === $expected) {
            return true;
        }

        if (!($known instanceof \BackedEnum) && ($expected instanceof \BackedEnum)) {
            return $known === $expected->value;
        }

        return false;
    }
}
