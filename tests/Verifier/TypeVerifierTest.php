<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Tests\Verifier;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Interface\EntityInterface;
use DualMedia\DoctrineEventConverterBundle\Model\SubEvent;
use DualMedia\DoctrineEventConverterBundle\Verifier\TypeVerifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('verifier')]
#[CoversClass(TypeVerifier::class)]
class TypeVerifierTest extends TestCase
{
    /**
     * @param list<string> $types
     */
    #[TestWith([true, Events::postUpdate, [Events::postUpdate]])]
    #[TestWith([true, Events::postUpdate, [Events::preUpdate, Events::postUpdate, Events::preRemove]])]
    #[TestWith([false, Events::preRemove, []])]
    #[TestWith([false, Events::postUpdate, [Events::preUpdate]])]
    public function test(
        bool $expected,
        string $type,
        array $types = []
    ): void {
        $event = new SubEvent('', true, [], [], $types, true); // @phpstan-ignore-line

        static::assertEquals(
            $expected,
            (new TypeVerifier())->verify(
                $this->createMock(EntityInterface::class),
                $event,
                [],
                $type
            )
        );
    }
}
