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
        
        $this->storage[] = $permissionEntity;
        
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
}
