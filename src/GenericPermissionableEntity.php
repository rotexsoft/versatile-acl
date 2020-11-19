<?php
declare(strict_types=1);

namespace SimpleAcl;

use SimpleAcl\Interfaces\PermissionableEntitiesCollectionInterface;
use SimpleAcl\Interfaces\PermissionableEntityInterface;
use SimpleAcl\Interfaces\PermissionInterface;
use SimpleAcl\Interfaces\PermissionsCollectionInterface;
use SimpleAcl\Exceptions\ParentCannotBeChildException;
use SimpleAcl\Exceptions\EmptyEntityIdException;

class GenericPermissionableEntity implements PermissionableEntityInterface {
    
    /**
     *
     * @var string
     * 
     */
    protected $id;
    
    /**
     *
     * @var \SimpleAcl\Interfaces\PermissionsCollectionInterface
     * 
     */
    protected $permissions;
    
    /**
     *
     * @var \SimpleAcl\Interfaces\PermissionableEntitiesCollectionInterface
     * 
     */
    protected $parentEntities;

    
    /**
     * PermissionableEntityInterface constructor.
     * 
     * @param string $id a case-insensitive unique string identifier for each new instance of PermissionableEntityInterface. Must be a non-empty string (cannot contain only these characters (" \t\n\r\0\x0B") alone).
     * @param PermissionsCollectionInterface|null $perms optional permissions for the new instance of PermissionableEntityInterface
     * @param PermissionableEntitiesCollectionInterface|null $parentEntities optional parent entities for the new instance of PermissionableEntityInterface
     * 
     * @throws \SimpleAcl\Exceptions\EmptyEntityIdException if $id is an empty string after these characters (" \t\n\r\0\x0B") are stripped from the front and back of the string 
     */
    public function __construct(string $id, PermissionsCollectionInterface $perms = null, PermissionableEntitiesCollectionInterface $parentEntities=null) {
        
        $trimmedId = trim($id);
        
        if( $trimmedId === '' ) {
            
            throw new EmptyEntityIdException(
                "An instance of " . get_class($this) 
                . " cannot be created with an empty `Id`." 
            );
        }
        
        $this->id = Utils::strtolower($trimmedId);
        $this->permissions = is_null($perms) ? GenericPermission::createCollection() : $perms;
        $this->parentEntities = is_null($parentEntities) ? static::createCollection() : $parentEntities;
    }

    /**
     * Create a new and empty collection that is meant to house one or more instances of PermissionableEntityInterface
     *
     * @param PermissionableEntityInterface ...$permissionEntities zero or more instances of PermissionableEntityInterface to be added to the new collection
     * 
     * @return PermissionableEntitiesCollectionInterface a new and empty collection that is meant to house one or more instances of PermissionableEntityInterface
     */
    public static function createCollection(PermissionableEntityInterface ...$permissionEntities): PermissionableEntitiesCollectionInterface {
        
        return new GenericPermissionableEntitiesCollection(...$permissionEntities);
    }

    /**
     * Add another instance of PermissionableEntityInterface as a parent to the current instance.
     *
     * If the parent already exists (i.e. an entity `$x` exists in this instance's parents list
     * where $x->isEqualTo($entity) === true), nothing should happen and this method should just
     * return the current instance (a.k.a $this).
     * 
     * This implementation checks all immediate parents and all their ancestors(i.e. parents,
     * parents' parents etc.) to see if $entity matches any of those parents, which will mean 
     * that $entity is already a parent and nothing needs to be done.
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
    public function addParentEntity(PermissionableEntityInterface $entity): PermissionableEntityInterface {
        
        if( !$this->getAllParentEntities()->hasEntity($entity) ) {
            
            if( $entity->isChildOf($this) ) {
                
                // This instance is already a parent to the 
                // entity we are trying to make its parent.
                $message = "Cannot make Entity with id `{$entity->getId()}`"
                         . " a parent to Entity with id `{$this->getId()}`."
                         . " Child cannot be parent.";
                
                throw new ParentCannotBeChildException($message); 
            }
            
            $this->parentEntities->add($entity);
            
        } else {
            // $this->getAllParentEntities()->hasEntity($entity) === true
 
            // Recursively traverse parents to find the right collection
            // of parents in which the entity is to be updated in and then
            // update the entity in that collection
            $putParent = function(PermissionableEntitiesCollectionInterface $parents) use ($entity, &$putParent){
                
                foreach ($parents as $key => $parent) {
                    
                    if($entity->isEqualTo($parent)) {
                        
                        $parents->put($entity, ''.$key);
                        
                    } else {
                        
                        // recurse
                        $putParent($parent->getDirectParentEntities());
                    }
                }
            };
            
            $putParent($this->parentEntities);
        }
        return $this;
    }

    /**
     * Add one or more instances of PermissionableEntityInterface as parent(s) to the current instance.
     *
     * If a parent already exists (i.e. an entity `$x` exists in this instance's parents list
     * where $x->isEqualTo($y) === true and $y is one of the entities in $entities), nothing should happen;
     * this method should either try to add the next parent entity (if any) or just return the
     * current instance (a.k.a $this).
     *
     * This implementation checks all immediate parents and all their ancestors(i.e. parents,
     * parents' parents etc.) to see if entities in $entities matches any of those parents, 
     * which will mean that such an entity is already a parent and doesn't need to be added
     * as a parent.
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
    public function addParentEntities(PermissionableEntitiesCollectionInterface $entities): PermissionableEntityInterface {
        
        foreach ($entities as $entity) {
            
            $this->addParentEntity($entity);
        }
        return $this;
    }

    /**
     * Return a list (instance of PermissionableEntitiesCollectionInterface) of all parent entities 
     * and their parents' parents and so on for an instance of this interface.
     *
     * @return PermissionableEntitiesCollectionInterface a list of all parent entities and their parents' parents and so on for an instance of this interface
     */
    public function getAllParentEntities(): PermissionableEntitiesCollectionInterface {
        
        return $this->doGetAllParentEntities(static::createCollection());
    }
    
    /**
     * Recursively adds all parents to the current instance of this class to $coll
     * 
     * @param PermissionableEntitiesCollectionInterface $coll
     * 
     * @return PermissionableEntitiesCollectionInterface
     */
    protected function doGetAllParentEntities(PermissionableEntitiesCollectionInterface $coll) {
        
        foreach ($this->getDirectParentEntities() as $entity) {
            
            (!$coll->hasEntity($entity)) && $coll->add($entity);
            
            if( $entity->getDirectParentEntities()->count() > 0 ) {
                
                // recurse
                $entity->doGetAllParentEntities($coll);
            }
        }    
        return $coll;
    }
    
    /**
     * Checks whether or not the current instance has the specified entity `$entity` as one of its parents.
     *
     * `$entity` is a parent to the current instance if an entity `X` in the current instance's parents list has
     * X->isEqualTo($entity) === true
     * 
     * This implementation not only checks direct parents (i.e. those added via 
     * $this->addParentEntity(..) and / or $this->addParentEntities(..)), it also 
     * includes all ancestors of each direct parent.
     *
     * @param PermissionableEntityInterface $entity
     * 
     * @return bool true if the current instance has an entity `X` in its parents list where X->isEqualTo($entity) === true, or false otherwise
     */
    public function isChildOf(PermissionableEntityInterface $entity): bool {
        
        return $this->getAllParentEntities()->hasEntity($entity);
    }

    /**
     * Checks whether or not the current instance has any parent with the specified Id `$entityId`.
     * 
     * This implementation not only checks direct parents (i.e. those added via 
     * $this->addParentEntity(..) and / or $this->addParentEntities(..)), it 
     * also includes all ancestors of each direct parent.
     *
     * @param string $entityId
     * 
     * @return bool true if the current instance has any parent with the specified Id `$entityId`, or false otherwise
     */
    public function isChildOfEntityWithId(string $entityId): bool {
        
        foreach ($this->getAllParentEntities() as $parent) {
            
            if( Utils::strtolower($parent->getId()) === Utils::strtolower($entityId) ) {
                
                return true;
            }
        }
        return false;
    }

    /**
     * Get the unique string identifier for an instance of PermissionableEntityInterface
     *
     * @return string the unique identifier for an instance of PermissionableEntityInterface
     */
    public function getId(): string {
        
        return Utils::strtolower($this->id);
    }

    /**
     * Return a list (instance of PermissionableEntitiesCollectionInterface) of all parent entities 
     * added (via addParentEntity and / or addParentEntities) to the current instance. 
     * This does not include parents of those parents and so on.
     *
     * @return PermissionableEntitiesCollectionInterface a list of all parent entities added to the current instance. It excludes parents' parents and so on
     */
    public function getDirectParentEntities(): PermissionableEntitiesCollectionInterface {
        
        return $this->parentEntities;
    }

    /**
     * Checks whether the specified entity object has an equal value to the current instance.
     *
     * This implementation considers 2 entities equal if they have the same Id value when compared case-insensitively
     *
     * @param PermissionableEntityInterface $entity
     * 
     * @return bool
     */
    public function isEqualTo(PermissionableEntityInterface $entity): bool {
        
        return Utils::strtolower($this->getId()) === Utils::strtolower($entity->getId());
    }

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
    public function removeParentIfExists(PermissionableEntityInterface $entity): PermissionableEntityInterface {
        
        $key = $this->parentEntities->getKey($entity);
        
        if( $key !== null ) {
            
            $this->parentEntities->removeByKey($key);
        }
        return $this;
    }

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
    public function removeParentsThatExist(PermissionableEntitiesCollectionInterface $entities): PermissionableEntityInterface {
        
        foreach ($entities as $entity) {
            
            $this->removeParentIfExists($entity);
        }
        return $this;
    }

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
    public function addPermission(PermissionInterface $perm): PermissionableEntityInterface {
        
        if( !$this->permissions->hasPermission($perm) ) {
            
            $this->permissions->add($perm);
            
        } else {
            
            $key = $this->permissions->getKey($perm);
            
            if($key !== null) {
                
                // update the entity in the collection
                $this->permissions->put($perm, ''.$key);
            }
            
        }
        return $this;
    }

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
    public function addPermissions(PermissionsCollectionInterface $perms): PermissionableEntityInterface {
        
        foreach ($perms as $perm) {
            
            $this->addPermission($perm);
        }
        return $this;
    }

    /**
     * Get a list (an instance of PermissionsCollectionInterface) of only the permissions 
     * added to this instance's list of permissions via addPermission and addPermissions.
     *
     * @return PermissionsCollectionInterface
     */
    public function getDirectPermissions(): PermissionsCollectionInterface {
        
        return $this->permissions;
    }

    /**
     * Get a list (an instance of PermissionsCollectionInterface) of the permissions returned when
     * getDirectPermissions() is invoked on each of this instance's parents and their parents,
     * parents' parents and so on.
     *
     * @param PermissionsCollectionInterface|null $inheritedPerms an optional collection that will contain the inherited permissions. If null, a new collection that will contain the inherited permissions will automatically be created by this method.
     *
     * @return PermissionsCollectionInterface
     */
    public function getInheritedPermissions(PermissionsCollectionInterface $inheritedPerms=null): PermissionsCollectionInterface {
        
        $allParentEntities = $this->getAllParentEntities();
        $inheritedPermsToReturn = is_null($inheritedPerms) ? GenericPermission::createCollection() : $inheritedPerms;
        
        foreach ($allParentEntities as $parent_entity) {
            foreach ($parent_entity->getDirectPermissions() as $parent_permission) {
                
                (!$inheritedPermsToReturn->hasPermission($parent_permission)) 
                            && $inheritedPermsToReturn->add($parent_permission);
            }
        }
        return $inheritedPermsToReturn;
    }

    /**
     * Get a list (an instance of PermissionsCollectionInterface) of all permissions returned by $this->getDirectPermissions() and $this->getInheritedPermissions()
     *
     * @param bool $directPermissionsFirst true to place the permissions from $this->getDirectPermissions() in the beginning of the returned collection
     *                                     or false to place the permissions from $this->getInheritedPermissions() in the beginning of the returned collection
     *
     * @param PermissionsCollectionInterface|null $allPerms an optional collection that all the permissions to be returned will be added to. If null, a new collection that will contain the all permissions will automatically be created by this method.
     *
     * @return \SimpleAcl\Interfaces\PermissionsCollectionInterface
     */
    public function getAllPermissions(bool $directPermissionsFirst=true, PermissionsCollectionInterface $allPerms=null): PermissionsCollectionInterface {
        
        $allPermissions = is_null($allPerms)? GenericPermission::createCollection() : $allPerms;
        $directPerms = $this->getDirectPermissions();
        $inheritedPerms = $this->getInheritedPermissions();
        $collection1 = $directPerms;
        $collection2 = $inheritedPerms;
        
        if( $directPermissionsFirst === false ) {
        
            $collection1 = $inheritedPerms;
            $collection2 = $directPerms;
        }
        
        foreach($collection1 as $item) {
            
            $allPermissions->add($item);
        }
        
        foreach($collection2 as $item) {
            
            $allPermissions->add($item);
        }
        return $allPermissions;
    }

    /**
     * Remove the permission `$perm` from the current instance's list of permission if the permission exists in the list.
     *
     * @param PermissionInterface $perm permission to be removed
     * 
     * @return $this
     */
    public function removePermissionIfExists(PermissionInterface $perm): PermissionableEntityInterface {
        
        $key = $this->permissions->getKey($perm);
        
        if( $key !== null ) {
            
            $this->permissions->removeByKey($key);
        }
        return $this;
    }

    /**
     * For each permission `$x` in $perms, remove `$x` from the current instance's list of permission if it `$x` exists in the list.
     *
     * @param PermissionsCollectionInterface $perms
     * 
     * @return $this
     */
    public function removePermissionsThatExist(PermissionsCollectionInterface $perms): PermissionableEntityInterface {
        
        foreach ($perms as $perm) {
            
            $this->removePermissionIfExists($perm);
        }
        return $this;
    }
    
    public function __toString(): string {
        
        return $this->dump();
    }
    
    public function dump(array $propertiesToExcludeFromDump=[]):string {
        
        static $propertiesToExcludeFromDumpAcrossAllInstances;
        
        if(
            !$propertiesToExcludeFromDumpAcrossAllInstances  // first call
            || 
            (
                $propertiesToExcludeFromDumpAcrossAllInstances !== $propertiesToExcludeFromDump
            ) // handle multiple calls with different values of $propertiesToExcludeFromDump
        ) {
            $propertiesToExcludeFromDumpAcrossAllInstances = $propertiesToExcludeFromDump;
        }
        
        $propertiesToExcludeFromThisCall = $propertiesToExcludeFromDumpAcrossAllInstances;

        $objAsStr = static::class ." (". spl_object_hash($this) . ")" . PHP_EOL . "{" . PHP_EOL;
        
        $objAsStr .= in_array('id', $propertiesToExcludeFromThisCall) ? '' : "\t"."id: `{$this->id}`" . PHP_EOL;
        $objAsStr .= in_array('parentEntities', $propertiesToExcludeFromThisCall) ? '' : "\t"."parentEntities: " . PHP_EOL . "\t\t". str_replace(PHP_EOL, PHP_EOL."\t\t", ''.$this->parentEntities) . PHP_EOL;
        $objAsStr .= in_array('permissions', $propertiesToExcludeFromThisCall) ? '' : "\t"."permissions: " . PHP_EOL . "\t\t". str_replace(PHP_EOL, PHP_EOL."\t\t", ''.$this->permissions) . PHP_EOL;
        
        $objAsStr .= PHP_EOL . "}" . PHP_EOL;
        
        return $objAsStr;
    }
}
