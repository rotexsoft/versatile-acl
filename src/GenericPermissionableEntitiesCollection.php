<?php
declare(strict_types=1);

namespace SimpleAcl;

use SimpleAcl\Interfaces\PermissionableEntitiesCollectionInterface;
use SimpleAcl\Interfaces\PermissionableEntityInterface;

class GenericPermissionableEntitiesCollection extends GenericBaseCollection implements PermissionableEntitiesCollectionInterface
{
    /**
     * Checks whether or not an entity exists in the current instance.
     *
     * `$entity` is present in the current instance if there is another entity `$x`
     * in the current instance where $x->isEqualTo($entity) === true.
     *
     * @param PermissionableEntityInterface $entity
     * @return bool true if there is another entity `$x` in the current instance where $x->isEqualTo($entity) === true, otherwise return false
     */
    public function hasEntity(PermissionableEntityInterface $entity): bool
    {
        // TODO: Implement hasEntity() method.
    }

    /**
     *
     * @return bool true if $item is of the expected type, else false
     *
     */
    public function checkType($item): bool
    {
        return ($item instanceof GenericPermissionableEntity);
    }

    /**
     *
     * @return string|array a string or array of strings of type name(s) for items acceptable in a collection
     *
     */
    public function getType()
    {
        return GenericPermissionableEntity::class;
    }
}
