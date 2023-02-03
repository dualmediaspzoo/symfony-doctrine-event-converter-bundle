<?php

namespace DualMedia\DoctrineEventConverterBundle\DependencyInjection\Model;

use Doctrine\ORM\Events;

/**
 * @interal
 */
final class EventConfiguration extends AbstractEventConfiguration
{
    private string $type = Events::postPersist;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(
        string $type
    ): static {
        $this->type = $type;

        return $this;
    }
}
