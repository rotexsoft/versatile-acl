<?php
declare(strict_types=1);

namespace SimpleAcl;

use SimpleAcl\Exceptions\InvalidItemTypeException;
use SimpleAcl\Interfaces\PermissionInterface;
use SimpleAcl\Interfaces\PermissionsCollectionInterface;
use VersatileCollections\Exceptions\InvalidItemException;

class GenericPermissionsCollection extends GenericBaseCollection implements PermissionsCollectionInterface
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
                PermissionInterface::class, $items, true
            );

        } catch (InvalidItemException $e) {

            throw new InvalidItemTypeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Checks whether or not a permission exists in the current instance.
     *
     * `$permission` is present in the current instance if there is another permission `$x`
     * in the current instance where $x->isEqualTo($permission) === true.
     *
     * @param PermissionInterface $permission
     * @return bool true if there is another permission `$x` in the current instance where $x->isEqualTo($permission) === true, otherwise return false
     */
    public function hasPermission(PermissionInterface $permission): bool
    {
        foreach ($this->storage as $other_permission) {
            if( $permission->isEqualTo($other_permission) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Calculates and returns a boolean value indicating whether or not one or more items in an instance of this interface
     * signifies that a specified action can be performed on a specified resource.
     *
     * This method should return true (signifying that the specified action is regarded as performable on the specified resource) only if:
     *  - an item `$x` exists in an instance of this interface
     *  - and $x->getAction() === $action
     *  - and $x->getResource() === $resource
     *  - and $x->getAllowActionOnResource() === true
     *  - and if supplied, executing $additionalAssertions with $argsForCallback as arguments must also return true
     *
     * @param string $action an action to check whether or not it can be performed on the specified resource
     * @param string $resource a resource we are testing whether an action can be performed on
     * @param callable|null $additionalAssertions an optional callback function with additional tests to check whether the specified action can be performed on the specified resource.
     *                                            The callback must return true if the specified action can be performed on the specified resource.
     * @param mixed ...$argsForCallback optional arguments that may be required by the $additionalAssertions callback
     * @return bool return true if one or more items in an instance of this interface signifies that a specified action can be performed on a specified resource, or false otherwise
     */
    public function isActionAllowedOnResource(string $action, string $resource, callable $additionalAssertions = null, ...$argsForCallback): bool
    {
        foreach ($this->storage as $permission) {
            if( $permission->isActionAllowedOnResource($action, $resource, $additionalAssertions, ...$argsForCallback) === true ) {
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
     * Whether an offset exists
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
