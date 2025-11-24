<?php

namespace DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Doctrine;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\ComplexEntity;
use DualMedia\DoctrineEventConverterBundle\Tests\Fixtures\Entity\Item;

class EntityFixtures extends Fixture
{
    public function load(
        ObjectManager $manager,
    ): void {
        $item = new Item();
        $item->setStatus(1);

        $manager->persist($item);
        $manager->flush();

        $entity = new ComplexEntity();
        $entity->setStatus(1)
            ->setName('MyName')
            ->setUnimportant('old');

        $manager->persist($entity);
        $manager->flush();
    }
}
