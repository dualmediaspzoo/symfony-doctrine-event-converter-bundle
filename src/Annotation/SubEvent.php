<?php

namespace DM\DoctrineEventDistributorBundle\Annotation;

use DM\DoctrineEventDistributorBundle\EventSubscriber\DispatchingSubscriber;
use Doctrine\ORM\Events;

/**
 * Responsible for registering sub events for {@link DispatchingSubscriber} with appropriate options
 *
 * <span style="color: yellow">WARNING:</span> You must specify the {@link SubEvent::$label} and one of {@link SubEvent::$fields} or {@link SubEvent::$requirements
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class SubEvent
{
    /**
     * Label for the SubEvent, this will be placed in the middle of the short class name
     *
     * If this annotation is placed on an event class called "FooBarEvent" and the label is "StatusChanged" then
     * the final short name of the class will be "FooBarStatusChangedEvent"
     *
     * @var string|null
     */
    public ?string $label;
    
    /**
     * @var string|string[]
     */
    public $entity;

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
     * @var mixed (string|array)
     * @psalm-var string|array<array-key, string|array{0: mixed, 1?: mixed}|null>
     */
    public $fields;

    /**
     * If all fields are required to fire event
     *
     * <span style="color: yellow">WARNING:</span> This setting is ignored if {@link SubEvent::$types} does not include either of {@link Events::postUpdate} or {@link Events::preUpdate}
     * or if the current event type is not one of the ones specified above!
     */
    public bool $allMode = true;

    /**
     * Additional requirements for the object to have for the event to pass.
     *
     * Accessed via property accessor in event object.
     *
     * <span style="color: yellow">WARNING:</span> Either this field or {@link SubEvent::$fields} is required!
     */
    public array $requirements = [];

    /**
     * Types of event that will cause this event to run, before even running the other checks.
     *
     * @var string[]
     * @psalm-var list<Events::prePersist|Events::postPersist|Events::preUpdate|Events::postUpdate|Events::preRemove|Events::postRemove>
     */
    public array $types = [];

    /**
     * This event's priority, if higher the faster it will fire.
     *
     * Suggested values between 2048 and -2048
     */
    public int $priority = 0;

    public function __construct(
        array $values
    ) {
        $this->label = $values['value'] ?? $values['label'] ?? null;
        $this->entity = $values['entity'] ?? null;
        $this->fields = $values['fields'] ?? null;
        $this->allMode = $values['allMode'] ?? true;
        $this->requirements = $values['requirements'] ?? [];
        $this->types = $values['types'] ?? [];
        $this->priority = $values['priority'] ?? 0;
    }
}
