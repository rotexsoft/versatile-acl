<?php
declare(strict_types=1);

namespace SimpleAcl;

use Traversable;
use SimpleAcl\Exceptions\InvalidItemTypeException;
use VersatileCollections\SpecificObjectsCollection;
use SimpleAcl\Interfaces\PermissionableEntityInterface;
use VersatileCollections\Exceptions\InvalidItemException;
use SimpleAcl\Interfaces\PermissionableEntitiesCollectionInterface;

class GenericPermissionableEntitiesCollection extends GenericBaseCollection implements PermissionableEntitiesCollectionInterface
{
    /**
     * CollectionInterface constructor.
     * @param mixed ...$items the items to be contained in an instance of this interface. Implementers must enforce that items are of the same type.
     *
     * @throws \SimpleAcl\Exceptions\InvalidItemTypeException if items are not all of the same type
     */
    public function __construct(...$items)
    {
        try {
            $this->storage = SpecificObjectsCollection::makeNewForSpecifiedClassName(
                PermissionableEntityInterface::class, $items, true
            );

        } catch (InvalidItemException $e) {

            throw new InvalidItemTypeException($e->getMessage(), $e->getCode(), $e);
        }
    }

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
        foreach ($this->storage as $other_entity) {
            if( $entity->isEqualTo($other_entity) ) {
                return true;
            }
        }
        return false;
    }
}
