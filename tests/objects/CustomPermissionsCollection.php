<?php /** @noinspection PhpIllegalPsrClassPathInspection */

use SimpleAcl\Utils;
use SimpleAcl\GenericBaseCollection;
use SimpleAcl\Interfaces\PermissionInterface;
use SimpleAcl\Interfaces\PermissionsCollectionInterface;

/**
 * Description of CustomPermissionableEntitiesCollection
 *
 * @author rotimi
 */
class CustomPermissionsCollection extends GenericBaseCollection implements PermissionsCollectionInterface {
    /**
     * Constructor.
     * 
     * @param mixed ...$permissions zero or more instances of PermissionInterface to be added to this collection
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
     * @param PermissionInterface $permission
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

            /** @var PermissionInterface $permissionClass */
            $permissionClass = get_class($permission); // for late static binding
            
            if(
                (
                    Utils::strToLower($permission->getAction()) === Utils::strToLower($action)
                    ||
                    Utils::strToLower($permission->getAction()) === Utils::strToLower($permissionClass::getAllActionsIdentifier())
                )
                &&
                (
                    Utils::strToLower($permission->getResource()) === Utils::strToLower($resource)
                    ||
                    Utils::strToLower($permission->getResource()) === Utils::strToLower($permissionClass::getAllResourcesIdentifier())
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
     * @param PermissionInterface $permission
     * 
     * @return $this
     */
    public function add(PermissionInterface $permission): PermissionsCollectionInterface {
                
        if( !$this->hasPermission($permission) ) {
        
            $this->storage[] = $permission;
            
        } else {
            
            // update the existing permission
            $key = $this->getKey($permission);
            $key !== null && $this->put($permission, ''.$key);
        }
        
        return $this;
    }

    /**
     * Retrieves the key in the collection associated with the specified permission object.
     * If the object is not present in the collection, NULL should be returned
     * 
     * @param PermissionInterface $permission
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
     * @param PermissionInterface $permission
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

    /**
     * Adds an instance of PermissionInterface to an instance of this class with the specified key.
     * 
     * @param PermissionInterface $permission
     * @param string $key specified key for $permission in the collection
     * 
     * @return $this
     */
    public function put(PermissionInterface $permission, string $key): PermissionsCollectionInterface {
        
        $this->storage[$key] = $permission;
        
        return $this;
    }
    
    /**
     * Retrieves the permission in the collection associated with the specified key.
     * If the key is not present in the collection, NULL should be returned
     * 
     * @param string $key
     * 
     * @return PermissionInterface|null
     */
    public function get(string $key): ?PermissionInterface {
        
        return array_key_exists($key, $this->storage) ? $this->storage[$key] : null;
    }

    /**
     * Sort the collection.
     * If specified, use the callback to compare items in the collection when sorting or
     * sort according to some default criteria (up to the implementer of this method to
     * specify what that criteria is).
     *
     * If $comparator is null, this implementation would sort based on ascending order
     * of PermissionInterface::getResource() followed by
     * PermissionInterface::getAction() and followed by
     * PermissionInterface::getAllowActionOnResource()
     * of each permission in the collection.
     *
     * @param callable|null $comparator has the following signature:
     *                  function( PermissionInterface $a, PermissionInterface $b ) : int
     *                      The comparison function must return an integer less than,
     *                      equal to, or greater than zero if the first argument is
     *                      considered to be respectively less than, equal to,
     *                      or greater than the second.
     *
     * @return $this
     */
    public function sort(callable $comparator = null): PermissionsCollectionInterface {
        
        if( $comparator === null ) {
            
            $comparator = function(PermissionInterface $a, PermissionInterface $b ) : int {
                
                if( $a->getResource() < $b->getResource() ) {
                    
                    return -1;
                    
                } else if( $a->getResource() === $b->getResource() ) {
                    
                    if( $a->getAction() < $b->getAction() ) {
                        
                        return -1;
                        
                    } else if ( $a->getAction() === $b->getAction() ) {
                        
                        if( $a->getAllowActionOnResource() < $b->getAllowActionOnResource() ) {
                            
                            return -1;
                            
                        } elseif( $a->getAllowActionOnResource() === $b->getAllowActionOnResource() ) {
                            
                            return 0;
                        } // if( $a->getAllowActionOnResource() < $b->getAllowActionOnResource() ) ... elseif( $a->getAllowActionOnResource() === $b->getAllowActionOnResource() )
                    } // if( $a->getAction() < $b->getAction() ) ... else if ( $a->getAction() === $b->getAction() )
                } // if( $a->getResource() < $b->getResource() ) ... else if( $a->getResource() === $b->getResource() )
                
                return 1;
            };
        }
        
        uasort($this->storage, $comparator);
        
        return $this;
    }
    
    /**
     * Find and return the first permission in the collection that matches the specified $action and / or $resource.
     * NULL should be returned if there was no match.
     * The match should be case-insensitive
     * If $action has an empty string value, do not use it for the match
     * If $resource has an empty string value, do not use it for the match
     * If both $action & $resource have empty string values, return NULL
     * 
     * @param string $action
     * @param string $resource
     * 
     * @return PermissionInterface|null
     */
    public function findOne(string $action='', string $resource=''): ?PermissionInterface {
        
        if( !($action === '' && $resource === '') ) {
            
             /** @var PermissionInterface $permission */
            foreach ($this->storage as $permission) {

                if( 
                    (
                        // match by $action and $resource
                        $action !== ''
                        && $resource !== ''
                        && Utils::strToLower($permission->getAction()) === Utils::strToLower($action)
                        && Utils::strToLower($permission->getResource()) === Utils::strToLower($resource)
                    )
                    ||
                    (
                        // only match by $resource
                        $action === ''
                        && $resource !== ''
                        && Utils::strToLower($permission->getResource()) === Utils::strToLower($resource)
                    )
                    ||
                    (
                        // only match by $action
                        $action !== ''
                        && $resource === ''
                        && Utils::strToLower($permission->getAction()) === Utils::strToLower($action)
                    )
                ) {
                    return $permission;
                }
            } // foreach ($this->storage as $permission)
        } // if( !($action === '' && $resource === '') )
        
        return null;
    }
    
    /**
     * Find and return the all permissions in the collection that match the specified $action and / or $resource.
     * An empty collection should be returned if there was no match.
     * The match should be case-insensitive
     * If $action has an empty string value, do not use it for the match
     * If $resource has an empty string value, do not use it for the match
     * If both $action & $resource have empty string values, return an empty collection
     * 
     * @param string $action
     * @param string $resource
     * 
     * @return PermissionsCollectionInterface
     */
    public function findAll(string $action='', string $resource=''): PermissionsCollectionInterface {
        
        $permissionsCollection = new static();
        
        if( !($action === '' && $resource === '') ) {
                        
             /** @var PermissionInterface $permission */
            foreach ($this->storage as $permission) {

                if( 
                    (
                        // match by $action and $resource
                        $action !== ''
                        && $resource !== ''
                        && Utils::strToLower($permission->getAction()) === Utils::strToLower($action)
                        && Utils::strToLower($permission->getResource()) === Utils::strToLower($resource)
                    )
                    ||
                    (
                        // only match by $resource
                        $action === ''
                        && $resource !== ''
                        && Utils::strToLower($permission->getResource()) === Utils::strToLower($resource)
                    )
                    ||
                    (
                        // only match by $action
                        $action !== ''
                        && $resource === ''
                        && Utils::strToLower($permission->getAction()) === Utils::strToLower($action)
                    )
                ) {
                    $permissionsCollection->add($permission);
                }
            } // foreach ($this->storage as $permission)
        } // if( !($action === '' && $resource === '') )
        
        return $permissionsCollection;
    }
}
