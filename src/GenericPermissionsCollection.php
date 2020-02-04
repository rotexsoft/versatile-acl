<?php
declare(strict_types=1);

namespace SimpleAcl;

use SimpleAcl\Interfaces\PermissionInterface;
use SimpleAcl\Interfaces\PermissionsCollectionInterface;

class GenericPermissionsCollection extends GenericBaseCollection implements PermissionsCollectionInterface
{
    /**
     * CollectionInterface constructor.
     * @param mixed ...$items the items to be contained in an instance of this interface. Implementers must enforce that items are of the same type.
     *
     */
    public function __construct(PermissionInterface ...$permissions) {
        
        $this->storage = $permissions;
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
    public function hasPermission(PermissionInterface $permission): bool {
        
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
    public function isAllowed(string $action, string $resource, callable $additionalAssertions = null, ...$argsForCallback): bool {
        
        foreach ($this->storage as $permission) {
            
            if(
                $permission->getAction() === Utils::strtolower($action)
                && $permission->getResource() === Utils::strtolower($resource)
            ) {
                return $permission->isAllowed($action, $resource, $additionalAssertions, ...$argsForCallback);
            }
        }
        return false;
    }

    public function add(PermissionInterface $permission): PermissionsCollectionInterface {
        
        $this->storage[] = $permission;
        
        return $this;
    }

    public function getKey(PermissionInterface $permission) {
        
        foreach ($this->storage as $key => $other_permission) {
            if( $permission->isEqualTo($other_permission) ) {
                return $key;
            }
        }
        return null;
    }

    public function remove(PermissionInterface $permission): PermissionsCollectionInterface {
        
        $key = $this->getKey($permission);
        
        if($key !== null) {
            $this->removeByKey($key);
        }
        return $this;
    }
}
