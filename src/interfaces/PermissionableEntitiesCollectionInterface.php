<?php
declare(strict_types=1);

namespace SimpleAcl\Interfaces;

interface PermissionableEntitiesCollectionInterface extends CollectionInterface
{
    public function __construct(PermissionableEntityInterface ...$permissionEntities);
    
    /**
     * Adds an instance of PermissionableEntityInterface to an instance of this interface
     * 
     * @param \SimpleAcl\Interfaces\PermissionableEntityInterface $permissionEntity
     * 
     * @return $this
     */
    public function add(PermissionableEntityInterface $permissionEntity): self;
    
    /**
     * Removes an instance of PermissionableEntityInterface from an instance of this interface.
     * 
     * @param \SimpleAcl\Interfaces\PermissionableEntityInterface $permissionEntity
     * @return $this
     */
    public function remove(PermissionableEntityInterface $permissionEntity): self;
    
    /**
     * Retrieves the key in the collection associated with the specified object.
     * If the object is not present in the collection, NULL should be returned
     * 
     * @param \SimpleAcl\Interfaces\PermissionableEntityInterface $permissionEntity
     * 
     * @return string|int
     */
    public function getKey(PermissionableEntityInterface $permissionEntity);
    
    /**
     * Checks whether or not an entity exists in the current instance.
     *
     * `$entity` is present in the current instance if there is another entity `$x`
     * in the current instance where $x->isEqualTo($entity) === true.
     *
     * @param PermissionableEntityInterface $entity
     * @return bool true if there is another entity `$x` in the current instance where $x->isEqualTo($entity) === true, otherwise return false
     */
    public function hasEntity(PermissionableEntityInterface $entity): bool;
}
