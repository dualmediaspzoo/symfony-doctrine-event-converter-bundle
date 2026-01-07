<?php

namespace DualMedia\DoctrineEventConverterBundle\DependencyInjection\Model;

use Doctrine\ORM\Events;

/**
 * @interal
 */
final class EventConfiguration extends AbstractEventConfiguration
{
    private string $type = Events::postPersist;

    private bool $afterFlush = false;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(
        string $type,
    ): static {
        $this->type = $type;

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

    public function getDefinitionKey(): string
    {
        return implode(
            '.',
            [
                $this->type,
                (int)$this->afterFlush,
            ]
        );
    }
}
