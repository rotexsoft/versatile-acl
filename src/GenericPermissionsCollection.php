<?php
declare(strict_types=1);

namespace SimpleAcl;

use SimpleAcl\Interfaces\PermissionInterface;
use SimpleAcl\Interfaces\PermissionsCollectionInterface;

class GenericPermissionsCollection extends GenericBaseCollection implements PermissionsCollectionInterface
{
    /**
     * Checks whether or not a permission exists in the current instance.
     *
     * `$perm` is present in the current instance if there is another permission `$x`
     * in the current instance where $x->isEqualTo($perm) === true.
     *
     * @param PermissionInterface $perm
     * @return bool true if there is another permission `$x` in the current instance where $x->isEqualTo($perm) === true, otherwise return false
     */
    public function hasPermission(PermissionInterface $perm): bool
    {
        // TODO: Implement hasPermission() method.
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
        // TODO: Implement isActionAllowedOnResource() method.
    }

    /**
     *
     * @return bool true if $item is of the expected type, else false
     *
     */
    public function checkType($item): bool
    {
        return ($item instanceof GenericPermission);
    }

    /**
     *
     * @return string|array a string or array of strings of type name(s) for items acceptable in a collection
     *
     */
    public function getType()
    {
        return GenericPermission::class;
    }
}