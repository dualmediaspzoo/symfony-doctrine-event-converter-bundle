# Doctrine Event Dispatcher Bundle

![Code Coverage](https://camo.githubusercontent.com/8c984abde97874f33a346711794b5da4c2af2e195655e4d6b2816f06c9f2a496/68747470733a2f2f696d672e736869656c64732e696f2f62616467652f436f6465253230436f7665726167652d36392532352d79656c6c6f773f7374796c653d666c6174)
[![Packagist Downloads](https://img.shields.io/packagist/dt/dualmedia/symfony-doctrine-event-converter-bundle)](https://packagist.org/packages/dualmedia/symfony-doctrine-event-converter-bundle)

This bundle is meant to convert between doctrine and symfony events seamlessly, as well as allow for creation of sub-events with their own requirements and checks

It allows you to streamline doctrine actions using symfony directly, without need of implementing doctrine's listeners and event logic.

All of the hard work is already done, just declare your entities, implement `EntityInterface` on them, and create an abstract event class.

## Installation

Simply `composer require dualmedia/symfony-doctrine-event-converter-bundle`

Then add the bundle to your `config/bundles.php` file like so

```php
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    // other bundles ...
    DualMedia\DoctrineEventDistributorBundle\DoctrineEventConverterBundle::class => ['all' => true],
];
```

## Usage

### Entity
Make a Doctrine-managed entity, that also implements the `DualMedia\DoctrineEventDistributorBundle\Interfaces\EntityInterface`

```php
use Doctrine\ORM\Mapping as ORM;
use DualMedia\DoctrineEventDistributorBundle\Interfaces\EntityInterface;

 #[ORM\Entity]
class Item implements EntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'smallint')]
    private ?int $status = null;

    public function getId()
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(
        int $status
    ): self {
        $this->status = $status;

        return $this;
    }
}
```

### Event
Create an event class (not final), and then at some point extend `DualMedia\DoctrineEventDistributorBundle\Event\AbstractEntityEvent`, 
mark this class with your appropriate event annotation, either one of the base ones or SubEvent

```php
use DualMedia\DoctrineEventDistributorBundle\Attributes\PrePersistEvent;
use DualMedia\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;

#[PrePersistEvent]
abstract class ItemEvent extends AbstractEntityEvent
{
    public static function getEntityClass(): ?string
    {
        return Item::class;
    }
}
```

The bundle will then automatically generate proxy classes for appropriate events.

Each proxy class starts with [the proxy namespace visible here](src/Proxy/Generator.php) under the `PROXY_NS` constant value.

The following class name will always contain the full namespace of the parent event. This namespace is loaded via the autoloader in the bundle and should not be interacted with in ways other than subscribers and general use.

### SubEvent

Let's assume the following scenario: You wish to have an event fired when the status of the `Item` changes from `pending` to `complete`,
in this case you'd add the following attribute on your `ItemEvent` (above).

SubEvents can take form of checks which apply to the previous and current state of variable, or only one (from OR to).

### Important

Because of how Doctrine passes changes unfortunately changes to collections are not known at this time.

#### From and To

The following will generate an `ItemPendingToCompleteEvent` class (under the default proxy namespace).

```php
use \DualMedia\DoctrineEventConverterBundle\Attributes\SubEvent;
use \DualMedia\DoctrineEventConverterBundle\Model\Change;

#[SubEvent("PendingToComplete", changes: [new Change('status', ItemStatusEnum::Pending, ItemStatusEnum::Complete)])]
```

Assuming of course the existence of an enum or other value which can be passed to the `Change` model.

More than one change can be required at a time, or _any_ change, depending on `SubEvent::$allMode`.

#### From

The following will generate an `ItemFromPendingEvent`.

```php
#[SubEvent("FromPending", changes: [new Change('status', ItemStatusEnum::Pending)])]
```

#### To

The following will generate an `ItemCompleteEvent`.

```php
#[SubEvent("Complete", changes: [new Change('status', to: ItemStatusEnum::Complete)])]
```

### PHPStan and Psalm issue ignoring

Ready-to-use templates are available below to make your lives a bit easier.

Plugins may be provided at a later time, but it's not certain.

#### PHPStan

> Note: in future a phpstan.neon file will be provided to ignore these issues, for the time being simply add the following lines into your file.

```
parameters:
  ignoreErrors:
    - '#Class DualMedia\\DoctrineEventConverterProxy\\[a-zA-Z0-9\\_]+ not found#'
    - '#Parameter \$[a-zA-Z0-9\\_]+ of method [a-zA-Z0-9\\_]+::[a-zA-Z0-9\\_]+\(\) has invalid type DualMedia\\DoctrineEventConverterProxy\\[a-zA-Z0-9\\_]+#'
    - '#Instantiated class DualMedia\\DoctrineEventConverterProxy\\[a-zA-Z0-9\\_]+ not found.#'
    - '#Call to method [a-zA-Z0-9\\_]+\(\) on an unknown class DualMedia\\DoctrineEventConverterProxy\\[a-zA-Z0-9\\_]+.#'
```

I also suggest disabling `reportUnmatchedIgnoredErrors` in your config, but it's not strictly necessary.

#### Psalm

This configuration needs to be copied over as psalm does not allow including files.

```xml
<issueHandlers>
  <UndefinedClass>
    <errorLevel type="suppress">
      <referencedClass name="DualMedia\DoctrineEventConverterProxy\*"/>
    </errorLevel>
  </UndefinedClass>

  <UndefinedDocblockClass>
    <errorLevel type="suppress">
      <referencedClass name="DualMedia\DoctrineEventConverterProxy\*"/>
    </errorLevel>
  </UndefinedDocblockClass>
</issueHandlers>
```
