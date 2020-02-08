<?php
declare(strict_types=1);

namespace SimpleAcl\Interfaces;

interface PermissionableEntityInterface {
    
    /**
     * PermissionableEntityInterface constructor.
     * 
     * @param string $id a case-insensitive unique string identifier for each new instance of PermissionableEntityInterface. Must be a non-empty string (cannot contain only these characters (" \t\n\r\0\x0B") alone).
     * @param PermissionsCollectionInterface|null $perms optional permissions for the new instance of PermissionableEntityInterface
     * @param PermissionableEntitiesCollectionInterface|null $parentEntities optional parent entities for the new instance of PermissionableEntityInterface
     * 
     * @throws \SimpleAcl\Exceptions\EmptyEntityIdException if $id is an empty string after these characters (" \t\n\r\0\x0B") are stripped from the front and back of the string 
     */
    public function __construct(string $id, PermissionsCollectionInterface $perms=null, PermissionableEntitiesCollectionInterface $parentEntities=null);

    /**
     * Create a new and empty collection that is meant to house one or more instances of PermissionableEntityInterface
     *
     * @return PermissionableEntitiesCollectionInterface a new and empty collection that is meant to house one or more instances of PermissionableEntityInterface
     */
    public static function createCollection(): PermissionableEntitiesCollectionInterface;

    /**
     * ======================================================================================================
     * ======================================================================================================
     *  Methods related to this instance and its parents
     * =======================================================================================================
     * =======================================================================================================
     */

    /**
     * Add another instance of PermissionableEntityInterface as a parent to the current instance.
     *
     * If the parent already exists (i.e. an entity `$x` exists in this instance's parents list
     * where $x->isEqualTo($entity) === true), nothing should happen and this method should just
     * return the current instance (a.k.a $this).
     *
     * A \SimpleAcl\Exceptions\ParentCannotBeChildException must be thrown if the current instance is already a parent to $entity.
     * An instance `A` of PermissionableEntityInterface is a parent to another instance `B` of PermissionableEntityInterface
     * if the list of instance B's parents contains an object `C` where C->isEqualTo(A) === true
     *
     * @param PermissionableEntityInterface $entity
     * 
     * @return $this
     * 
     * @throws \SimpleAcl\Exceptions\ParentCannotBeChildException
     */
    public function addParentEntity(PermissionableEntityInterface $entity): self;

    /**
     * Add one or more instances of PermissionableEntityInterface as parent(s) to the current instance.
     *
     * If a parent already exists (i.e. an entity `$x` exists in this instance's parents list
     * where $x->isEqualTo($y) === true and $y is one of the entities in $entities), nothing should happen;
     * this method should either try to add the next parent entity (if any) or just return the
     * current instance (a.k.a $this).
     *
     * A \SimpleAcl\Exceptions\ParentCannotBeChildException must be thrown if the current instance is already a parent to at least
     * one entity in $entities. Parent(s) added before the exception was thrown are still valid and should not be removed.
     * An instance `A` of PermissionableEntityInterface is a parent to another instance `B` of PermissionableEntityInterface
     * if the list of instance B's parents contains an object `C` where C->isEqualTo(A) === true
     *
     * @param PermissionableEntitiesCollectionInterface $entities
     * 
     * @return $this
     * 
     * @throws \SimpleAcl\Exceptions\ParentCannotBeChildException
     */
    public function addParentEntities(PermissionableEntitiesCollectionInterface $entities): self;

    /**
     * Checks whether or not the current instance has the specified entity `$entity` as one of its parents.
     *
     * `$entity` is a parent to the current instance if an entity `X` in the current instance's parents list has
     * X->isEqualTo($entity) === true
     *
     * @param PermissionableEntityInterface $entity
     * 
     * @return bool true if the current instance has an entity `X` in its parents list where X->isEqualTo($entity) === true, or false otherwise
     */
    public function isChildOf(PermissionableEntityInterface $entity): bool;

    /**
     * Checks whether or not the current instance has any parent with the specified Id `$entityId`.
     *
     * @param string $entityId
     * 
     * @return bool true if the current instance has any parent with the specified Id `$entityId`, or false otherwise
     */
    public function isChildOfEntityWithId(string $entityId): bool;

    /**
     * Get the unique string identifier for an instance of PermissionableEntityInterface
     *
     * @return string the unique identifier for an instance of PermissionableEntityInterface
     */
    public function getId(): string;

    /**
     * Return a list (instance of PermissionableEntitiesCollectionInterface) of all parent entities 
     * added (via addParentEntity and / or addParentEntities) to the current instance. 
     * This does not include parents of those parents and so on.
     *
     * @return PermissionableEntitiesCollectionInterface a list of all parent entities added to the current instance. It excludes parents' parents and so on
     */
    public function getDirectParentEntities(): PermissionableEntitiesCollectionInterface;

    /**
     * Return a list (instance of PermissionableEntitiesCollectionInterface) of all parent entities 
     * and their parents' parents and so on for an instance of this interface.
     *
     * @return PermissionableEntitiesCollectionInterface a list of all parent entities and their parents' parents and so on for an instance of this interface
     */
    public function getAllParentEntities(): PermissionableEntitiesCollectionInterface;
    
    /**
     * Checks whether the specified entity object has an equal value to the current instance.
     *
     * It is up to the implementer of this method to define what criteria makes two entity objects equal.
     *
     * @param PermissionableEntityInterface $entity
     * 
     * @return bool
     */
    public function isEqualTo(PermissionableEntityInterface $entity): bool;
    
    /**
     * Remove the specified entity $entity if it exists in the list of the current instance's parent entities.
     *
     * $entity exists in the list of the current instance's parent entities if there is an entity `X` in the
     * list where X->isEqualTo($entity) === true
     *
     * @param PermissionableEntityInterface $entity
     * 
     * @return $this
     */
    public function removeParentIfExists(PermissionableEntityInterface $entity): self;

    /**
     * Remove entities from the list of the current instance's parent entities which exists in $entities.
     *
     * An entity `$x` in `$entities` also exists in the list of the current instance's parent entities if
     * there is an entity `$y` in the parent list where $x->isEqualTo($y) === true
     *
     * @param PermissionableEntitiesCollectionInterface $entities
     * 
     * @return $this
     */
    public function removeParentsThatExist(PermissionableEntitiesCollectionInterface $entities): self;

    /**
     * ======================================================================================================
     * ======================================================================================================
     *  Permissions related methods
     * =======================================================================================================
     * =======================================================================================================
     */

    /**
     * Add an instance of PermissionInterface to the list of the current instance's permissions if the permission is not already present in the list.
     *
     * `$perm` is present in the current instance's list of permissions if there is another permission `$x`
     * in the current instance's list of permissions where  $x->isEqualTo($perm) === true. In this case,
     * nothing should happen, this method should just return the current instance (a.k.a $this).
     *
     * @param PermissionInterface $perm
     * 
     * @return $this
     */
    public function addPermission(PermissionInterface $perm): self;

    /**
     * Add one or more instances of PermissionInterface to the list of the current instance's permissions.
     *
     * If a permission `$x` is present in $perms and if there is another permission `$y`
     * in the current instance's list of permissions where $x->isEqualTo($y) === true, the
     * permission $x does not need to be added to the current instance's list of permissions,
     * this method should either try to add the next permission in $perms (if any) or just return
     * the current instance (a.k.a $this).
     *
     * @param PermissionsCollectionInterface $perms
     * 
     * @return $this
     */
    public function addPermissions(PermissionsCollectionInterface $perms): self;

    /**
     * Get a list (an instance of PermissionsCollectionInterface) of only the permissions 
     * added to this instance's list of permissions via addPermission and addPermissions.
     *
     * @return PermissionsCollectionInterface
     */
    public function getDirectPermissions(): PermissionsCollectionInterface;

    /**
     * Get a list (an instance of PermissionsCollectionInterface) of the permissions returned when 
     * getDirectPermissions() is invoked on each of this instance's parents and their parents, 
     * parents' parents and so on.
     * 
     * @param PermissionsCollectionInterface $inheritedPerms an optional collection that will contain the inherited permissions.  If null, a new collection that will contain the inherited permissions will automatically be created by this method.
     *
     * @return PermissionsCollectionInterface
     */
    public function getInheritedPermissions(PermissionsCollectionInterface $inheritedPerms=null): PermissionsCollectionInterface;
    
    /**
     * Get a list (an instance of PermissionsCollectionInterface) of all permissions returned by $this->getDirectPermissions() and $this->getInheritedPermissions() 
     * 
     * @param bool $directPermissionsFirst true to place the permissions from $this->getDirectPermissions() in the beginning of the returned collection
     *                                     or false to place the permissions from $this->getInheritedPermissions() in the beginning of the returned collection
     * 
     * @param PermissionsCollectionInterface $allPerms an optional collection that all the permissions to be returned will be added to. If null, a new collection that will contain the all permissions will automatically be created by this method.
     * 
     * @return \SimpleAcl\Interfaces\PermissionsCollectionInterface
     */
    public function getAllPermissions(bool $directPermissionsFirst=true, PermissionsCollectionInterface $allPerms=null): PermissionsCollectionInterface;

    /**
     * Remove the permission `$perm` from the current instance's list of permission if the permission exists in the list.
     *
     * @param PermissionInterface $perm permission to be removed
     * 
     * @return $this
     */
    public function removePermissionIfExists(PermissionInterface $perm): self;

    /**
     * For each permission `$x` in $perms, remove `$x` from the current instance's list of permission if it `$x` exists in the list.
     *
     * @param PermissionsCollectionInterface $perms
     * 
     * @return $this
     */
    public function removePermissionsThatExist(PermissionsCollectionInterface $perms): self;
}
