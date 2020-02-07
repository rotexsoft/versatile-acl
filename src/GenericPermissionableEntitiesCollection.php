<?php
declare(strict_types=1);

namespace SimpleAcl;

use SimpleAcl\Interfaces\PermissionableEntityInterface;
use SimpleAcl\Interfaces\PermissionableEntitiesCollectionInterface;

class GenericPermissionableEntitiesCollection extends GenericBaseCollection implements PermissionableEntitiesCollectionInterface {
    
    /**
     * CollectionInterface constructor.
     * 
     * @param PermissionableEntityInterface ...$permissionEntities instances of PermissionableEntityInterface to be added to this collection
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

    public function add(PermissionableEntityInterface $permissionEntity): PermissionableEntitiesCollectionInterface {
        
        $this->storage[] = $permissionEntity;
        
        return $this;
    }

    public function getKey(PermissionableEntityInterface $permissionEntity) {
        
        foreach ($this->storage as $key => $other_entity) {
            if( $permissionEntity->isEqualTo($other_entity) ) {
                
                return $key;
            }
        }
        return null;
    }

    public function remove(PermissionableEntityInterface $permissionEntity): PermissionableEntitiesCollectionInterface {
        
        $key = $this->getKey($permissionEntity);
        
        if($key !== null) {
            $this->removeByKey($key);
        }
        
        return $this;
    }
    
    public function removeAll(): PermissionableEntitiesCollectionInterface {
        
        $this->storage = [];
        
        return $this;
    }
}
