# Doctrine Event Dispatcher Bundle

This bundle is meant to convert between doctrine and symfony events seamlessly, as well as allow for creation of sub-events with their own requirements and checks

It allows you to streamline doctrine actions using symfony directly, without need of implementing doctrine's listeners and event logic.

All of the hard work is already done, just declare your entities, implement `EntityInterface` on them, and create an abstract event class.

## Usage

1. Make a Doctrine-managed entity, that also implements the `DM\DoctrineEventDistributorBundle\Interfaces\EntityInterface`

```php
use Doctrine\ORM\Mapping as ORM;
use DM\DoctrineEventDistributorBundle\Interfaces\EntityInterface;

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

2. Create an event class (not final), and then at some point extend `DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent`, 
mark this class with your appropriate event annotation, either one of the base ones or SubEvent

```php
use DM\DoctrineEventDistributorBundle\Attributes\PrePersistEvent;
use DM\DoctrineEventDistributorBundle\Event\AbstractEntityEvent;

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