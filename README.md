# Doctrine Event Dispatcher Bundle

![Code Coverage](https://camo.githubusercontent.com/76e1ee9ebc5150b0fb1e9c152f56616e9b2eadd4b57ecf0f9d83895c06fb3b8c/68747470733a2f2f696d672e736869656c64732e696f2f62616467652f436f6465253230436f7665726167652d37322532352d79656c6c6f773f7374796c653d666c6174)
![Packagist Downloads](https://img.shields.io/packagist/dt/dualmedia/symfony-doctrine-event-converter-bundle)

This bundle is meant to convert between doctrine and symfony events seamlessly, as well as allow for creation of sub-events with their own requirements and checks

It allows you to streamline doctrine actions using symfony directly, without need of implementing doctrine's listeners and event logic.

All of the hard work is already done, just declare your entities, implement `EntityInterface` on them, and create an abstract event class.

## Installation

Simply `composer require dualmedia/symfony-doctrine-event-converter-bundle`

## Usage

1. Make a Doctrine-managed entity, that also implements the `DualMedia\DoctrineEventDistributorBundle\Interfaces\EntityInterface`

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

2. Create an event class (not final), and then at some point extend `DualMedia\DoctrineEventDistributorBundle\Event\AbstractEntityEvent`, 
mark this class with your appropriate event annotation, either one of the base ones or SubEvent

```php
use DualMedia\DoctrineEventDistributorBundle\Attributes\PrePersistEvent;
use DualMedia\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;

#[PrePersistEvent]
abstract class ItemEvent extends AbstractEntityEvent
{
    /**
     * @psalm-pure
     */
    public static function getEntityClass(): ?string
    {
        return Item::class;
    }
}
```

3. Rebuild cache, enjoy.
