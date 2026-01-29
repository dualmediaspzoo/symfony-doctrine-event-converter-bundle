<?php

namespace DualMedia\DoctrineEventConverterBundle;

use DualMedia\Common\Interface\IdentifiableInterface;

class ObjectIdCache
{
    /**
     * @var array<string, string|int>
     */
    private array $cache = [];

    public function set(
        object $object
    ): void {
        if (!$object instanceof IdentifiableInterface) {
            return;
        }

        if (null === ($id = $object->getId())) {
            return;
        }

        $this->cache[spl_object_hash($object)] = $id;
    }

    /**
     * @param object|string $object saved spl_object_hash or object itself
     */
    public function get(
        object|string $object
    ): string|int|null {
        if (!is_string($object)) {
            $object = spl_object_hash($object);
        }

        return $this->cache[$object] ?? null;
    }
}
