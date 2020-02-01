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

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing Iterator or Traversable
     */
    public function getIterator()
    {
        $this->storage->getIterator();
    }

    /**
     * Whether a offset exists
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset An offset to check for.
     *
     * @return bool true on success or false on failure.
     *
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->storage->offsetExists($offset);
    }

    /**
     * Offset to retrieve
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->storage->offsetGet($offset);
    }

    /**
     * Offset to set
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->storage->offsetSet($offset, $value);
    }

    /**
     * Offset to unset
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->storage->offsetUnset($offset);
    }

    /**
     * Count elements of an object
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *
     * The return value is cast to an integer.
     */
    public function count()
    {
        return $this->storage->count();
    }
}
