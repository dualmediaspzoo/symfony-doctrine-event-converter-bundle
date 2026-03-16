<?php

namespace PHPSTORM_META;

override(
    \DualMedia\DoctrineEventConverterBundle\Event\AbstractEntityEvent::getManager(),
    map([
        '' => '\Doctrine\ORM\EntityManagerInterface|\Doctrine\Persistence\ObjectManager'
    ])
);