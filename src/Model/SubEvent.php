<?php

namespace DM\DoctrineEventDistributorBundle\Model;

/**
 * This class contains the basic fields required for sub events to work properly
 *
 * @psalm-immutable
 */
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
     * @var array
     * @psalm-var array<string, null|array{0: mixed, 1?: mixed}>
     */
    private array $fieldList;

    /**
     * Required field states for this event to fire
     *
     * @var array
     * @psalm-var array<string, mixed>
     */
    private array $requirements;

    /**
     * Event types in which this event may be triggered
     *
     * @var string[]
     */
    private array $types;

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

    public function getFieldList(): array
    {
        return $this->fieldList;
    }

    public function getRequirements(): array
    {
        return $this->requirements;
    }

    public function getTypes(): array
    {
        return $this->types;
    }
}
