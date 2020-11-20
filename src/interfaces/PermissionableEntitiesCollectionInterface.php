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
     * Adds an instance of PermissionableEntityInterface to an instance of this interface. 
     * Duplicate PermissionableEntityInterface instances should not be allowed in the same instance of this interface.
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

    /**
     * Sort the collection.
     * If specified, use the callback to compare items in the collection when sorting or
     * sort according to some default criteria (up to the implementer of this method to
     * specify what that criteria is).
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
    public function sort(callable $comparator=null): self;
}
