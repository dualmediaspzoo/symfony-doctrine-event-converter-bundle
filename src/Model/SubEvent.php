<?php

namespace DualMedia\DoctrineEventDistributorBundle\Model;

use Doctrine\ORM\Events;
use JetBrains\PhpStorm\Immutable;

/**
 * This class contains the basic fields required for sub events to work properly
 *
 * @psalm-immutable
 */
#[Immutable]
class SubEvent
{
    /**
     * If all the fields must be meeting the requirements of the event
     *
     * @var bool
     */
    private bool $allMode;

    /**
     * The fields that must be changed, this structure is not valid during construct and gets optimized and fixed after first use
     *
     * @var array<string, null|array{0: mixed, 1?: mixed}>
     */
    private array $fieldList;

    /**
     * Required field states for this event to fire
     *
     * @var array<string, mixed>
     */
    private array $requirements;

    /**
     * Event types in which this event may be triggered
     *
     * @var list<string>
     *
     * @see Events
     */
    private array $types;

    /**
     * @param bool $allMode
     * @param array<string, null|array{0: mixed, 1?: mixed}> $fieldList
     * @param array<string, mixed> $requirements
     * @param list<string> $types
     */
    public function __construct(
        bool $allMode,
        array $fieldList,
        array $requirements,
        array $types
    ) {
        $this->allMode = $allMode;
        $this->requirements = $requirements;
        $this->types = $types;
        $this->fieldList = $fieldList;
    }

    public function isAllMode(): bool
    {
        return $this->allMode;
    }

    /**
     * @return array<string, null|array{0: mixed, 1?: mixed}>
     */
    public function getFieldList(): array
    {
        return $this->fieldList;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRequirements(): array
    {
        return $this->requirements;
    }

    /**
     * @return list<string>
     */
    public function getTypes(): array
    {
        return $this->types;
    }
}
