<?php
declare(strict_types=1);

namespace SimpleAcl;

use SimpleAcl\Interfaces\PermissionInterface;
use SimpleAcl\Interfaces\PermissionsCollectionInterface;

class GenericPermissionsCollection extends GenericBaseCollection implements PermissionsCollectionInterface {
    
    /**
     * Constructor.
     * 
     * @param mixed ...$items zero or more instances of PermissionInterface to be added to this collection
     *
     */
    public function __construct(PermissionInterface ...$permissions) {
        
        $this->storage = $permissions;
    }

    /**
     * Checks whether or not a permission exists in the current instance.
     *
     * `$perm` is present in the current instance if there is another permission `$x`
     * in the current instance where $x->isEqualTo($perm) === true.
     *
     * @param PermissionInterface $perm
     * 
     * @return bool true if there is another permission `$x` in the current instance where $x->isEqualTo($perm) === true, otherwise return false
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
     * Calculates and returns a boolean value indicating whether or not one or more items in an instance of this class
     * signifies that a specified action can be performed on a specified resource.
     *
     * This method should return true (signifying that the specified action is regarded as performable on the specified resource) only if:
     *  - an item `$x` exists in an instance of this class
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
     * 
     * @return bool return true if one or more items in an instance of this class signifies that a specified action can be performed on a specified resource, or false otherwise
     */
    public function isAllowed(string $action, string $resource, callable $additionalAssertions = null, ...$argsForCallback): bool {
        
        foreach ($this->storage as $permission) {
            
            $permissionClass = get_class($permission); // for late static binding
            
            if(
                (
                    Utils::strtolower($permission->getAction()) === Utils::strtolower($action)
                    ||
                    Utils::strtolower($permission->getAction()) === Utils::strtolower($permissionClass::getAllActionsIdentifier())
                )
                &&
                (
                    Utils::strtolower($permission->getResource()) === Utils::strtolower($resource)
                    ||
                    Utils::strtolower($permission->getResource()) === Utils::strtolower($permissionClass::getAllResoucesIdentifier())
                )
                
            ) {
                return $permission->isAllowed($action, $resource, $additionalAssertions, ...$argsForCallback);
            }
        }
        return false;
    }

    /**
     * Adds an instance of PermissionInterface to an instance of this class.
     * 
     * @param \SimpleAcl\Interfaces\PermissionInterface $permission
     * 
     * @return $this
     */
    public function add(PermissionInterface $permission): PermissionsCollectionInterface {
        
        $this->storage[] = $permission;
        
        return $this;
    }

    /**
     * Retrieves the key in the collection associated with the specified permission object.
     * If the object is not present in the collection, NULL should be returned
     * 
     * @param \SimpleAcl\Interfaces\PermissionInterface $permission
     * 
     * @return string|int|null
     */
    public function getKey(PermissionInterface $permission) {
        
        foreach ($this->storage as $key => $other_permission) {
            if( $permission->isEqualTo($other_permission) ) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Removes an instance of PermissionInterface from an instance of this class.
     * 
     * @param \SimpleAcl\Interfaces\PermissionInterface $permission
     * 
     * @return $this
     */
    public function remove(PermissionInterface $permission): PermissionsCollectionInterface {
        
        $key = $this->getKey($permission);
        
        if($key !== null) {
            $this->removeByKey($key);
        }
        return $this;
    }
    
    /**
     * Remove all items in the collection and return $this
     * 
     * @return $this
     */
    public function removeAll(): PermissionsCollectionInterface {
        
        $this->storage = [];
        
        return $this;
    }
}
