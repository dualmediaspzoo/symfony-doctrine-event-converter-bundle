<?php

namespace DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Doctrine;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\ComplexEntity;
use DualMedia\DoctrineEventDistributorBundle\Tests\Fixtures\Entity\Item;

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
