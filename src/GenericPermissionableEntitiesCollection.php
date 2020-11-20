<?php
declare(strict_types=1);

namespace SimpleAcl;

use SimpleAcl\Interfaces\PermissionableEntityInterface;
use SimpleAcl\Interfaces\PermissionableEntitiesCollectionInterface;

class GenericPermissionableEntitiesCollection extends GenericBaseCollection implements PermissionableEntitiesCollectionInterface {
    
    /**
     * Constructor.
     * 
     * @param PermissionableEntityInterface ...$permissionEntities zero or more instances of PermissionableEntityInterface to be added to this collection
     *
     */
    public function __construct(PermissionableEntityInterface ...$permissionEntities) {
        
        $this->storage = $permissionEntities;
    }

    /**
     * Checks whether or not an entity exists in the current instance.
     *
     * `$entity` is present in the current instance if there is another entity `$x`
     * in the current instance where $x->isEqualTo($entity) === true.
     *
     * @param PermissionableEntityInterface $entity
     * 
     * @return bool true if there is another entity `$x` in the current instance where $x->isEqualTo($entity) === true, otherwise return false
     */
    public function hasEntity(PermissionableEntityInterface $entity): bool {
        
        foreach ($this->storage as $other_entity) {
            if( $entity->isEqualTo($other_entity) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Adds an instance of PermissionableEntityInterface to an instance of this class
     * 
     * @param \SimpleAcl\Interfaces\PermissionableEntityInterface $permissionEntity
     * 
     * @return $this
     */
    public function add(PermissionableEntityInterface $permissionEntity): PermissionableEntitiesCollectionInterface {
        
        if( !$this->hasEntity($permissionEntity) ) {
        
            $this->storage[] = $permissionEntity;
            
        } else {
            
            // update the existing entity
            $key = $this->getKey($permissionEntity);
            $key !== null && $this->put($permissionEntity, ''.$key);
        }
        
        return $this;
    }

    /**
     * Retrieves the key in the collection associated with the specified object.
     * If the object is not present in the collection, NULL should be returned
     * 
     * @param \SimpleAcl\Interfaces\PermissionableEntityInterface $entity
     * 
     * @return string|int|null
     */
    public function getKey(PermissionableEntityInterface $entity) {
        
        foreach ($this->storage as $key => $other_entity) {
            if( $entity->isEqualTo($other_entity) ) {
                return $key;
            }
        }
        return null;
    }
    
    /**
     * Removes an instance of PermissionableEntityInterface from an instance of this class.
     * 
     * @param \SimpleAcl\Interfaces\PermissionableEntityInterface $permissionEntity
     * 
     * @return $this
     */
    public function remove(PermissionableEntityInterface $permissionEntity): PermissionableEntitiesCollectionInterface {
        
        $key = $this->getKey($permissionEntity);
        
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
    public function removeAll(): PermissionableEntitiesCollectionInterface {
        
        $this->storage = [];
        
        return $this;
    }

    /**
     * Adds an instance of PermissionableEntityInterface to an instance of this class with the specified key.
     * 
     * @param \SimpleAcl\Interfaces\PermissionableEntityInterface $permissionEntity
     * @param string $key specified key for $permissionEntity in the collection
     * 
     * @return $this
     */
    public function put(PermissionableEntityInterface $permissionEntity, string $key): PermissionableEntitiesCollectionInterface {
        
        $this->storage[$key] = $permissionEntity;
        
        return $this;
    }

    /**
     * Retrieves the entity in the collection associated with the specified key.
     * If the key is not present in the collection, NULL should be returned
     * 
     * @param string $key
     * 
     * @return \SimpleAcl\Interfaces\PermissionableEntityInterface|null
     */
    public function get(string $key): ?PermissionableEntityInterface {
        
        return array_key_exists($key, $this->storage) ? $this->storage[$key] : null;
    }

    /**
     * Sort the collection.
     * If specified, use the callback to compare items in the collection when sorting or
     * sort according to some default criteria (up to the implementer of this method to
     * specify what that criteria is).
     *
     * If $comparator is null, this implementation would sort based on ascending order
     * of PermissionableEntityInterface::getId() of each entity in the collection.
     *
     * @param callable|null $comparator has the following signature:
     *                  function( PermissionableEntityInterface $a, PermissionableEntityInterface $b ) : int
     *                      The comparison function must return an integer less than,
     *                      equal to, or greater than zero if the first argument is
     *                      considered to be respectively less than, equal to,
     *                      or greater than the second.
     *
     * @return $this
     */
    public function sort(callable $comparator = null): PermissionableEntitiesCollectionInterface {
        
        if( $comparator === null ) {
            
            $comparator = function( PermissionableEntityInterface $a, PermissionableEntityInterface $b ) : int {
                
                if( $a->getId() < $b->getId() ) {
                    
                    return -1;
                    
                } else if( $a->getId() === $b->getId() ) {
                    
                    return 0;
                }
                
                return 1;
            };
        }
        
        uasort($this->storage, $comparator);
        
        return $this;
    }
}
