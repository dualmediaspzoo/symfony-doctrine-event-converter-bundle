<?php

namespace DualMedia\DoctrineEventConverterBundle\Attributes;

use Doctrine\ORM\Events;
use DualMedia\DoctrineEventConverterBundle\EventSubscriber\DispatchingSubscriber;
use DualMedia\DoctrineEventConverterBundle\Model\Change;

/**
 * Responsible for registering sub events for {@link DispatchingSubscriber} with appropriate options
 *
 * <span style="color: yellow">WARNING:</span> You must specify the {@link SubEvent::$label} and one of {@link SubEvent::$changes} or {@link SubEvent::$requirements
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class SubEvent
{
    /**
     * Label for the SubEvent, this will be placed in the middle of the short class name
     *
     * If this annotation is placed on an event class called "FooBarEvent" and the label is "StatusChanged" then
     * the final short name of the class will be "FooBarStatusChangedEvent"
     */
    public readonly string $label;

    /**
     * @var non-empty-list<class-string>|null
     */
    public readonly array|null $entity;

    /**
     * List of fields to be used for searching for changes.
     *
     * <span style="color: yellow">WARNING:</span> Either this field or {@link SubEvent::$requirements} is required!
     *
     * Either pass the name, names of fields, or fields with required values:
     *
     * For example:
     *
     * `fields="price"`
     *
     * `fields={"price"}`
     *
     * are the same internally and mean "When field 'price' changes"
     *
     * `fields={"price"={0.15}}`
     *
     * means "When field 'price' changes to 0.15"
     *
     * `fields={"price"={0.15, 0.30}}`
     *
     * means "When field 'price' changes from 0.15 to 0.30"
     *
     * @var string|array<array-key, string|array{0: mixed, 1?: mixed}|null>
     * @deprecated use {@link SubEvent::$changes} instead
     */
    public readonly string|array $fields;

    /**
     * If all fields are required to fire event
     *
     * <span style="color: yellow">WARNING:</span> This setting is ignored if {@link SubEvent::$types} does not include either of {@link Events::postUpdate} or {@link Events::preUpdate}
     * or if the current event type is not one of the ones specified above!
     */
    public readonly bool $allMode;

    /**
     * Additional requirements for the object to have for the event to pass.
     *
     * Accessed via property accessor in event object.
     *
     * <span style="color: yellow">WARNING:</span> Either this field or {@link SubEvent::$fields} is required!
     *
     * @var array<string, mixed>
     */
    public readonly array $requirements;

    /**
     * Types of event that will cause this event to run, before even running the other checks.
     *
     * @var list<string>
     *
     * @see Events
     */
    public readonly array $types;

    /**
     * This event's priority, if higher the faster it will fire.
     *
     * Suggested values between 2048 and -2048
     */
    public readonly int $priority;

    public readonly bool $afterFlush;
    
    /**
     * @param string $label
     * @param string|array<array-key, string|array{0: mixed, 1?: mixed}|null> $fields
     * @param non-empty-list<class-string>|null $entity
     * @param bool $allMode
     * @param array<string, mixed> $requirements
     * @param list<string> $types
     * @param int $priority
     * @param list<Change> $changes list of field changes
     * @param bool $afterFlush
     */
    public function __construct(
        string $label,
        string|array $fields = [],
        array|null $entity = null,
        bool $allMode = true,
        array $requirements = [],
        array $types = [],
        int $priority = 0,
        public readonly array $changes = [],
        bool $afterFlush = false,
    ) {
        $this->label = $label;
        $this->entity = $entity;
        $this->fields = $fields;
        $this->allMode = $allMode;
        $this->requirements = $requirements;
        $this->types = $types;
        $this->priority = $priority;
        $this->afterFlush = $afterFlush;
    }
}
