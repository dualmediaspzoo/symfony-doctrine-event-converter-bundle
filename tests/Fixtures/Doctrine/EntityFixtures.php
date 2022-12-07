<?php

namespace DM\DoctrineEventDistributorBundle\Tests\Fixtures\Doctrine;

use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\ComplexEntity;
use DM\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EntityFixtures extends Fixture
{
    public function load(
        ObjectManager $manager
    ) {
        $item = new Item();
        $item->setStatus(1);

        $manager->persist($item);
        $manager->flush();

        $entity = new ComplexEntity();
        $entity->setStatus(1)
            ->setName('MyName')
            ->setUnimportant("old");

        $manager->persist($entity);
        $manager->flush();
    }
}
