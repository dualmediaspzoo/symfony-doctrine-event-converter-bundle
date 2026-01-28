<?php

declare(strict_types=1);

namespace DualMedia\DoctrineEventConverterBundle\Tests\Verifier;

use Doctrine\ORM\Events;
use DualMedia\Common\Interface\IdentifiableInterface;
use DualMedia\DoctrineEventConverterBundle\Interface\VerifierInterface;
use DualMedia\DoctrineEventConverterBundle\Model\SubEvent;
use DualMedia\DoctrineEventConverterBundle\Verifier\SubEventVerifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('verifier')]
#[CoversClass(SubEventVerifier::class)]
class SubEventVerifierTest extends TestCase
{
    /**
     * @param list<bool> $verifiers
     */
    #[TestWith([true, []])]
    #[TestWith([true, [true]])]
    #[TestWith([true, [true, true]])]
    #[TestWith([false, [false]])]
    #[TestWith([false, [true, false]])]
    #[TestWith([false, [false, true, true]])]
    public function test(
        bool $expected,
        array $verifiers
    ): void {
        $entity = $this->createMock(IdentifiableInterface::class);
        $event = $this->createMock(SubEvent::class);
        $changes = ['stuff' => 'here'];
        $types = [
            Events::postUpdate,
            Events::preUpdate,
            Events::postRemove,
            Events::preRemove,
            Events::postPersist,
            Events::prePersist,
        ];

        $type = $types[array_rand($types)];

        $mocks = [];

        foreach ($verifiers as $result) {
            $mocks[] = $mock = $this->createMock(VerifierInterface::class);
            $mock->expects(static::atMost(1))
                ->method('verify')
                ->with($entity, $event, $changes, $type)
                ->willReturn($result);
        }

        static::assertEquals(
            $expected,
            (new SubEventVerifier($mocks))->verify($entity, $event, $changes, $type)
        );
    }
}
