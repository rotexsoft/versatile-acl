<?php
declare(strict_types=1);

namespace SimpleAcl\Interfaces;

interface PermissionableEntitiesCollectionInterface extends CollectionInterface {
    
    /**
     * Constructor.
     * 
     * @param PermissionableEntityInterface ...$permissionEntities zero or more instances of PermissionableEntityInterface to be added to this collection
     *
     */
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
     * Adds an instance of PermissionableEntityInterface to an instance of this interface with the specified key.
     * 
     * @param \SimpleAcl\Interfaces\PermissionableEntityInterface $permissionEntity
     * @param string $key specified key for $permissionEntity in the collection
     * 
     * @return $this
     */
    public function put(PermissionableEntityInterface $permissionEntity, string $key): self;
    
    /**
     * Removes an instance of PermissionableEntityInterface from an instance of this interface.
     * 
     * @param \SimpleAcl\Interfaces\PermissionableEntityInterface $permissionEntity
     * 
     * @return $this
     */
    public function remove(PermissionableEntityInterface $permissionEntity): self;
    
    /**
     * Remove all items in the collection and return $this
     * 
     * @return $this
     */
    public function removeAll(): self;
    
    /**
     * Retrieves the entity in the collection associated with the specified key.
     * If the key is not present in the collection, NULL should be returned
     * 
     * @param string $key
     * 
     * @return \SimpleAcl\Interfaces\PermissionableEntityInterface|null
     */
    public function get(string $key): ?PermissionableEntityInterface;
    
    /**
     * Retrieves the key in the collection associated with the specified object.
     * If the object is not present in the collection, NULL should be returned
     * 
     * @param \SimpleAcl\Interfaces\PermissionableEntityInterface $entity
     * 
     * @return string|int|null
     */
    public function getKey(PermissionableEntityInterface $entity);
    
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
    public function hasEntity(PermissionableEntityInterface $entity): bool;
}
