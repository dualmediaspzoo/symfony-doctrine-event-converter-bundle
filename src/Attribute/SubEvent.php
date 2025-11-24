<?php

namespace DualMedia\DoctrineEventConverterBundle\Attribute;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\EventSubscriber\DispatchingSubscriber;
use DualMedia\DoctrineEventConverterBundle\Model\Change;

/**
 * Responsible for registering sub events for {@link DispatchingSubscriber} with appropriate options.
 *
 * <span style="color: yellow">WARNING:</span> You must specify the {@link SubEvent::$label} and one of {@link SubEvent::$changes} or {@link SubEvent::$requirements
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
readonly class SubEvent
{
    /**
     * Label for the SubEvent, this will be placed in the middle of the short class name.
     *
     * If this annotation is placed on an event class called "FooBarEvent" and the label is "StatusChanged" then
     * the final short name of the class will be "FooBarStatusChangedEvent"
     */
    public string $label;

    /**
     * If all fields are required to fire event.
     *
     * <span style="color: yellow">WARNING:</span> This setting is ignored if {@link SubEvent::$types} does not include either of {@link Events::postUpdate} or {@link Events::preUpdate}
     * or if the current event type is not one of the ones specified above!
     */
    public bool $allMode;

    /**
     * Additional requirements for the object to have for the event to pass.
     *
     * Accessed via property accessor in event object.
     *
     * <span style="color: yellow">WARNING:</span> Either this field or {@link SubEvent::$fields} is required!
     *
     * @var array<string, mixed>
     */
    public array $requirements;

    /**
     * Types of event that will cause this event to run, before even running the other checks.
     *
     * @var list<string>
     *
     * @see Events
     */
    public array $types;

    /**
     * This event's priority, if higher the faster it will fire.
     *
     * Suggested values between 2048 and -2048
     */
    public int $priority;

    /**
     * @param array<string, mixed> $requirements
     * @param list<string> $types
     * @param list<Change> $changes list of field changes
     */
    public function __construct(
        string $label,
        bool $allMode = true,
        array $requirements = [],
        array $types = [],
        int $priority = 0,
        public array $changes = [],
        public bool $afterFlush = false,
    ) {
        $this->label = $label;
        $this->allMode = $allMode;
        $this->requirements = $requirements;
        $this->types = $types;
        $this->priority = $priority;
    }
}
