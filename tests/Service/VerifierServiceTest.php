<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Service;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Service\VerifierService;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Enum\BackedIntEnum;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

class VerifierServiceTest extends TestCase
{
    use ServiceMockHelperTrait;

    private VerifierService&MockObject $service;

    protected function setUp(): void
    {
        $this->service = $this->createRealPartialMockedServiceInstance(VerifierService::class, []);
    }

    #[TestWith([true, Events::prePersist, [Events::prePersist]])]
    #[TestWith([true, Events::prePersist, [Events::prePersist, Events::preUpdate]])]
    #[TestWith([false, Events::prePersist, [Events::preUpdate]])]
    public function testValidateType(
        bool $result,
        string $type,
        array $types,
    ): void {
        $this->assertEquals(
            $result,
            $this->service->validateType($type, $types)
        );
    }

    #[TestWith([true, 10, 10])]
    #[TestWith([false, 5, 10])]
    #[TestWith([true, 5, BackedIntEnum::Is5])]
    public function testEquals(
        bool $result,
        mixed $known,
        mixed $expected,
    ): void {
        $this->assertEquals(
            $result,
            $this->service->equals($known, $expected)
        );
    }
}
