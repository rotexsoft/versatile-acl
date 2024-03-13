<?php
declare(strict_types=1);

namespace VersatileAcl;

use VersatileAcl\Interfaces\PermissionInterface;
use VersatileAcl\Interfaces\PermissionsCollectionInterface;
use function array_key_exists;
use function uasort;

/**
 * @property PermissionInterface[] $storage
 */
class GenericPermissionsCollection extends GenericBaseCollection implements PermissionsCollectionInterface {
    
    /**
     * Constructor.
     * 
     * @param PermissionInterface ...$permissions zero or more instances of PermissionInterface to be added to this collection
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
     *
     * @return bool true if there is another permission `$x` in the current instance where $x->isEqualTo($perm) === true, otherwise return false
     */
    public function hasPermission(PermissionInterface $permission): bool {
        
        /** @var PermissionInterface $other_permission **/
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
    public function isAllowed(string $action, string $resource, callable $additionalAssertions = null, mixed ...$argsForCallback): bool {
        
        /** @var PermissionInterface $permission */
        foreach ($this->storage as $permission) {

            /** @var PermissionInterface $permissionClass */
            $permissionClass = $permission::class; // for late static binding
            
            if(
                (
                    Utils::strSameIgnoreCase($permission->getAction(), $action)
                    ||
                    Utils::strSameIgnoreCase($permission->getAction(), $permissionClass::getAllActionsIdentifier())
                )
                &&
                (
                    Utils::strSameIgnoreCase($permission->getResource(), $resource)
                    ||
                    Utils::strSameIgnoreCase($permission->getResource(), $permissionClass::getAllResourcesIdentifier())
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
     *
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
     *
     * @return string|int|null
     */
    public function getKey(PermissionInterface $permission) {
        
        /** @var PermissionInterface $other_permission */
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
     *
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
     * @param string $key specified key for $permission in the collection
     *
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
     *
     */
    public function get(string $key): ?PermissionInterface {
        
        /** @var PermissionInterface $this->storage[$key] **/
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
     * 
     * @return $this
     * @noinspection PhpDocSignatureInspection
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
        /** @var array<string, PermissionInterface> $this->storage **/
        /** @var callable(mixed, mixed):int $comparator **/
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
     *
     * @noinspection PhpFullyQualifiedNameUsageInspection
     * @noinspection PhpRedundantOptionalArgumentInspection
     * @throws \Exception
     */
    public function findOne(string $action='', string $resource=''): ?PermissionInterface {
        
        
        
        /** @var array<int|string, PermissionInterface> $firstMatch **/
        $firstMatch = 
            \iterator_to_array(
                $this->findFirstN($action, $resource, 1)
                     ->getIterator()
            );
        
        /** @noinspection PhpFullyQualifiedNameUsageInspection */
        return (\count($firstMatch) > 0) ? \array_shift($firstMatch) : null;
    }
    
    /**
     * Find and return the all permissions in the collection that match the specified $action and / or $resource.
     * An empty collection should be returned if there was no match.
     * The match should be case-insensitive
     * If $action has an empty string value, do not use it for the match
     * If $resource has an empty string value, do not use it for the match
     * If both $action & $resource have empty string values, return an empty collection
     * 
     * 
     * @noinspection DuplicatedCode
     */
    public function findAll(string $action='', string $resource=''): PermissionsCollectionInterface {

        // can find at most $this->count() permissions matching the parameters
        return $this->findFirstN($action, $resource, $this->count());
    }
    
    /**
     * Find and return the all permissions in the collection that match the specified $action and / or $resource.
     * An empty collection should be returned if there was no match. The match is always case-insensitive
     *
     * If $action has an empty string value, do not use it for the match
     *
     * If $resource has an empty string value, do not use it for the match
     *
     * If both $action & $resource have empty string values, return an empty collection
     *
     * If $n < 1, this method internally bumps it up to 1 and returns either 
     * a collection contain only 1 matching permission object or an empty collection
     *
     * @param int $n number of matching permission objects to be returned
     *
     *
     * @noinspection DuplicatedCode
     * @psalm-suppress RedundantCondition
     * @psalm-suppress UnsafeInstantiation
     * 
     */
    protected function findFirstN(string $action='', string $resource='', int $n=1): PermissionsCollectionInterface {
        
        $permissionsCollection = new static();
        
        if($n < 1) { // we must try to find at least 1 permission
            
            $n = 1;
        }
        
        if(($action !== '' || $resource !== '') && $this->count() > 0 ) {

            $counter = 1;

            /** @var PermissionInterface $permission */
            foreach ($this->storage as $permission) {

                if( 
                    (
                        // match by $action and $resource
                        $action !== ''
                        && $resource !== ''
                        && Utils::strSameIgnoreCase($permission->getAction(), $action)
                        && Utils::strSameIgnoreCase($permission->getResource(), $resource)
                    )
                    ||
                    (
                        // only match by $resource
                        $action === ''
                        && $resource !== ''
                        && Utils::strSameIgnoreCase($permission->getResource(), $resource)
                    )
                    ||
                    (
                        // only match by $action
                        $action !== ''
                        && $resource === ''
                        && Utils::strSameIgnoreCase($permission->getAction(), $action)
                    )
                ) {
                    $permissionsCollection->add($permission);
                    $counter++;
                }

                if($counter > $n) { break; } // found first N permissions

            } // foreach ($this->storage as $permission)
        } // if( !($action === '' && $resource === '') )
        
        return $permissionsCollection;
    }
}
