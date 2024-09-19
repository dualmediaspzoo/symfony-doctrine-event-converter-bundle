<?php

namespace DualMedia\DoctrineEventConverterBundle\DependencyInjection\Model;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\Exception\DependencyInjection\SubEventRequiredFieldsException;

/**
 * @internal
 */
final class SubEventConfiguration extends AbstractEventConfiguration
{
    /**
     * @var non-empty-list<string>
     *
     * @see Events
     */
    private array $events;

    /**
     * @var array<string, null|array{0?: mixed, 1?: mixed}>
     */
    private array $changes = [];

    private string $label;

    /**
     * @var array<string, mixed>
     */
    private array $requirements = [];

    private int $priority = 0;
    private bool $allMode = true;

    private bool $afterFlush = false;

    /**
     * @return non-empty-list<string>
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @param non-empty-list<string> $events
     */
    public function setEvents(
        array $events,
    ): static {
        $this->events = $events;

        return $this;
    }

    /**
     * @return array<string, null|array{0?: mixed, 1?: mixed}>
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * @param array<string, null|array{0?: mixed, 1?: mixed}> $changes
     */
    public function setChanges(
        array $changes,
    ): static {
        $this->changes = $changes;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(
        string $label,
    ): static {
        $this->label = $label;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRequirements(): array
    {
        return $this->requirements;
    }

    /**
     * @param array<string, mixed> $requirements
     */
    public function setRequirements(
        array $requirements,
    ): static {
        $this->requirements = $requirements;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(
        int $priority,
    ): static {
        $this->priority = $priority;

        return $this;
    }

    public function isAllMode(): bool
    {
        return $this->allMode;
    }

    public function setAllMode(
        bool $allMode,
    ): static {
        $this->allMode = $allMode;

        return $this;
    }

    public function isAfterFlush(): bool
    {
        return $this->afterFlush;
    }

    public function setAfterFlush(
        bool $afterFlush,
    ): static {
        $this->afterFlush = $afterFlush;

        return $this;
    }

    /**
     * @throws SubEventRequiredFieldsException
     */
    public function validate(
        string $class,
    ): static {
        if (empty($this->getChanges()) && empty($this->getRequirements())) {
            throw SubEventRequiredFieldsException::new([
                $this->getLabel(),
                $class,
            ]);
        }

        return $this;
    }
}
