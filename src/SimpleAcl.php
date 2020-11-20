<?php
declare(strict_types=1);

namespace SimpleAcl;

use SimpleAcl\Interfaces\{
    PermissionInterface, PermissionableEntitiesCollectionInterface, 
    PermissionableEntityInterface, PermissionsCollectionInterface
};

/**
 * A class for managing entities and permissions for access controlling resources in applications using this package 
 *
 * @author rotimi
 */
class SimpleAcl {
    
    /**
     *
     * @var string name of the class that implements PermissionableEntityInterface that will be used to create new entities
     * 
     */
    protected $permissionableEntityInterfaceClassName;
    
    /**
     *
     * @var string name of the class that implements PermissionInterface that will be used to create new permissions
     *  
     */
    protected $permissionInterfaceClassName;
    
    /**
     *
     * @var string name of the class that implements PermissionableEntitiesCollectionInterface that will be used to create new entity collections
     *  
     */
    protected $permissionableEntitiesCollectionInterfaceClassName;
    
    /**
     *
     * @var string name of the class that implements PermissionsCollectionInterface that will be used to create new permission collections
     * 
     */
    protected $permissionsCollectionInterfaceClassName;
    
    /**
     *
     * @var PermissionableEntitiesCollectionInterface collection of entities for each instance of this class
     */
    protected $entitiesCollection;
    
    public function __construct(
        string $permissionableEntityInterfaceClassName = GenericPermissionableEntity::class,
        string $permissionInterfaceClassName = GenericPermission::class,
        string $permissionableEntitiesCollectionInterfaceClassName = GenericPermissionableEntitiesCollection::class,
        string $permissionsCollectionInterfaceClassName = GenericPermissionsCollection::class
    ) {
        if(!is_subclass_of($permissionableEntityInterfaceClassName, PermissionableEntityInterface::class)) {
           
            $this->throwInvalidArgExceptionDueToWrongClassName(
                get_class($this), __FUNCTION__, $permissionableEntityInterfaceClassName, PermissionableEntityInterface::class, 'first'
            );
        }
        
        if(!is_subclass_of($permissionInterfaceClassName, PermissionInterface::class)) {
           
            $this->throwInvalidArgExceptionDueToWrongClassName(
                get_class($this), __FUNCTION__, $permissionInterfaceClassName, PermissionInterface::class, 'second'
            );
        }
        
        if(!is_subclass_of($permissionableEntitiesCollectionInterfaceClassName, PermissionableEntitiesCollectionInterface::class)) {
           
            $this->throwInvalidArgExceptionDueToWrongClassName(
                get_class($this), __FUNCTION__, $permissionableEntitiesCollectionInterfaceClassName, PermissionableEntitiesCollectionInterface::class, 'third'
            );
        }
        
        if(!is_subclass_of($permissionsCollectionInterfaceClassName, PermissionsCollectionInterface::class)) {
           
            $this->throwInvalidArgExceptionDueToWrongClassName(
                get_class($this), __FUNCTION__, $permissionsCollectionInterfaceClassName, PermissionsCollectionInterface::class, 'fourth'
            );
        }
        
        $this->permissionInterfaceClassName = $permissionInterfaceClassName;
        $this->permissionableEntityInterfaceClassName = $permissionableEntityInterfaceClassName;
        $this->permissionsCollectionInterfaceClassName = $permissionsCollectionInterfaceClassName;
        $this->permissionableEntitiesCollectionInterfaceClassName = $permissionableEntitiesCollectionInterfaceClassName;
        
        $this->entitiesCollection = $this->createEntityCollection();
    }
    
    /**
     * Adds an entity to an instance of this class if it doesn't already exist.
     * 
     * @param string $entityId
     * 
     * @return $this
     */
    public function addEntity(string $entityId): self {
        
        $entity = $this->createEntity($entityId);
        
        if(!($this->entitiesCollection instanceof PermissionableEntitiesCollectionInterface)) {
            
            $this->entitiesCollection = $this->createEntityCollection();
        }
        
        if(!$this->entitiesCollection->hasEntity($entity)) {
            
            $this->entitiesCollection->add($entity);
        }
        
        return $this;
    }
    
    /**
     * Add an entity with an ID value of $parentEntityId as a parent entity to 
     * another entity with an ID value of $entityId in an instance of this class.
     * If an entity with an ID value of $entityId does not exist in the instance 
     * of this class upon which this method is called, the entity will be created
     * and added first before the parent entity is added to it.
     * 
     * @param string $entityId
     * @param string $parentEntityId
     * 
     * @return \self
     * 
     * @throws \RuntimeException
     */
    public function addParentEntity(string $entityId, string $parentEntityId): self {
        
        $existingEntity = $this->getEntity($entityId);
        
        if($existingEntity === null) {
         
            $this->addEntity($entityId);
            $existingEntity = $this->getEntity($entityId);
        }
        
        if($existingEntity instanceof PermissionableEntityInterface) {
            
            $existingEntity->addParentEntity($this->createEntity($parentEntityId));
            
        } else {
            
            // We should never really get here in most cases.
            // Something weird happened, we could not create or retreive
            // the entity to which a parent is to be added
            $class = get_class($this);
            $function = __FUNCTION__;
            $msg = "Error [{$class}::{$function}(...)]:"
            . " Could not create or retreive the entity with an ID of `{$entityId}`"
            . " to which the parent entity with an ID of `{$parentEntityId}` is to be added.";

            throw new \RuntimeException($msg); 
        }
        
        return $this;
    }
    
    /**
     * Remove and return an entity with an ID value of $parentEntityId that is a parent entity 
     * to another entity with an ID value of $entityId, if the instance of this class
     * upon which this method is being called contains an entity with an ID value of $entityId,
     * else return NULL
     * 
     * @param string $entityId ID of entity from which a parent entity with ID value of $parentEntityId is to be removed
     * @param string $parentEntityId ID of the parent entity to be removed from the specified entity with the ID value of $entityId
     * 
     * @return PermissionableEntityInterface|null
     */
    public function removeParentEntity(string $entityId, string $parentEntityId): ?PermissionableEntityInterface {
        
        $removedParentEntity = null;
        $existingEntity = $this->getEntity($entityId);
        
        if($existingEntity instanceof PermissionableEntityInterface) {
            
            $keyForParentEntity = $existingEntity->getDirectParentEntities()->getKey($this->createEntity($parentEntityId));
            $removedParentEntity = $existingEntity->getDirectParentEntities()->get($keyForParentEntity); // get the parent entity object
            $existingEntity->getDirectParentEntities()->removeByKey($keyForParentEntity); // remove the parent
        }
        
        return $removedParentEntity;
    }
    
    /**
     * Gets and returns an entity with specified Id from an instance of this class 
     * or returns NULL if an entity with specified Id doesn't already exist in the 
     * instance of this class this method is being invoked on.
     * 
     * @param string $entityId
     * 
     * @return $this
     */
    public function getEntity(string $entityId): ?PermissionableEntityInterface {
        
        $entity = $this->createEntity($entityId);
        $entityToReturn = null; 
        
        if(
            $this->entitiesCollection instanceof PermissionableEntitiesCollectionInterface
            && !$this->entitiesCollection->hasEntity($entity)
        ) {
            $key = $this->entitiesCollection->getKey($entity);
            
            if($key !== null) {
                
                $entityToReturn = $this->entitiesCollection->get($key);
            }
        }
        
        return $entityToReturn;
    }
    
    /**
     * Returns a collection of all entities added to an instance of this class via $this->addEntity(string $entityId)
     * 
     * @return PermissionableEntitiesCollectionInterface a collection of all entities added to an instance of this class via $this->addEntity(string $entityId)
     */
    public function getAllEntities() : PermissionableEntitiesCollectionInterface {
        
        return $this->entitiesCollection;
    }
    
    /**
     * Add a permission to an entity with the ID value of $entityId. 
     * This entity will be created and added to the instance of this class upon
     * which this method is being invoked if the entity does not exist.
     * 
     * @see PermissionInterface::__construct($action, $resource, $allowActionOnResource, $additionalAssertions, $argsForCallback) 
     * for definitions of all but the first parameter
     * 
     * @param string $entityId
     * @param string $action
     * @param string $resource
     * @param bool $allowActionOnResource
     * @param callable $additionalAssertions
     * @param type $argsForCallback
     * @return \self
     */
    public function addPermission(
        string $entityId, 
        string $action, 
        string $resource, 
        bool $allowActionOnResource = true, 
        callable $additionalAssertions = null, 
        ...$argsForCallback
    ): self {
        
        $existingEntity = $this->getEntity($entityId);
        
        if($existingEntity === null) {
         
            $this->addEntity($entityId);
            $existingEntity = $this->getEntity($entityId);
        }
        
        if($existingEntity instanceof PermissionableEntityInterface) {
            
            $existingEntity->addPermission(
                $this->createPermission($action, $resource, $allowActionOnResource, $additionalAssertions, ...$argsForCallback)
            );
            
        } else {
            
            $funcArgs = func_get_args();
            $funcArgsMinusFirstArg = array_shift($funcArgs);
            
            // We should never really get here in most cases.
            // Something weird happened, we could not create or retreive
            // the entity to which a parent is to be added
            $class = get_class($this);
            $function = __FUNCTION__;
            $msg = "Error [{$class}::{$function}(...)]:"
            . " Could not create or retreive the entity with an ID of `{$entityId}`"
            . " to which the following permission is to be added:"
            . PHP_EOL . PHP_EOL . var_export($funcArgsMinusFirstArg, true);

            throw new \RuntimeException($msg); 
        }
        
        return $this;
    }
    
    /**
     * Remove a permission from the entity with an ID value of $entityId and return the removed permission
     * or return null if either the entity of permission do not exist.
     * 
     * @see PermissionInterface::__construct($action, $resource, $allowActionOnResource, $additionalAssertions, $argsForCallback) 
     * for definitions of all but the first parameter
     * 
     * @param string $entityId
     * @param string $action
     * @param string $resource
     * @param bool $allowActionOnResource
     * @param callable $additionalAssertions
     * @param mixed $argsForCallback
     * 
     * @return PermissionInterface|null
     */
    public function removePermission(
        string $entityId, 
        string $action, 
        string $resource, 
        bool $allowActionOnResource = true, 
        callable $additionalAssertions = null, 
        ...$argsForCallback
    ): ?PermissionInterface {
        
        $removedPermission = null;
        $existingEntity = $this->getEntity($entityId);
        
        if($existingEntity instanceof PermissionableEntityInterface) {
            
            $keyForPermission = 
                $existingEntity->getDirectPermissions()
                               ->getKey(
                                    $this->createPermission(
                                        $action, $resource,
                                        $allowActionOnResource, 
                                        $additionalAssertions, 
                                        ...$argsForCallback
                                    )
                                );
            $removedPermission = $existingEntity->getDirectPermissions()->get($keyForPermission); // get the permission object
            $existingEntity->getDirectPermissions()->removeByKey($keyForPermission); // remove the permission
        }
        
        return $removedPermission;
    }
    
    /**
     * Check if the specified action $action can be performed on the specified 
     * resource $resource based on the existing permissions associated with
     * either the specified entity with an ID of $entityId or all entities
     * associated with the instance  of this class this method is being invoked
     * on if $entityId === ''
     * 
     * @see PermissionInterface::isAllowed($action, $resource, $additionalAssertions, ...$argsForCallback) 
     * for definitions of all but the first parameter
     * 
     * @param string $entityId                  ID of the entity whose permissions will be searched. 
     *                                          Pass an empty string to search the permissions for 
     *                                          all entities added to the instance of this class 
     *                                          this method is being invoked on.
     * @param string $action                    See the see section above
     * @param string $resource                  See the see section above
     * @param callable $additionalAssertions    See the see section above
     * @param mixed $argsForCallback            See the see section above
     * 
     * @return bool
     */
    public function isAllowed(string $entityId, string $action, string $resource, callable $additionalAssertions = null, ...$argsForCallback): bool  {
        
        $isAllowed = false;
        
        if( $entityId === '' && $this->entitiesCollection instanceof PermissionableEntitiesCollectionInterface ) {
            
            // loop through all entities
            /** @var PermissionableEntityInterface $currentEntity */
            foreach ($this->entitiesCollection as $currentEntity) {
                
                // Get all permissions including inherited ones and check if 
                // permission test evaluates to true for any of the permissions.
                if( 
                    $currentEntity->getAllPermissions(true, $this->createPermissionCollection())
                                  ->isAllowed($action, $resource, $additionalAssertions, ...$argsForCallback)
                ) {
                    $isAllowed = true;
                    break;
                }
            }
            
        } else {
            
            // get specified entity if it exists and check through its permissions
            // including inherited ones, to if the permission test evaluates to 
            // true for any of the permissions
            $specifiedEntity = $this->getEntity($entityId);
            
            if($specifiedEntity instanceof PermissionableEntityInterface) {
                
                $isAllowed = $specifiedEntity->getAllPermissions(true, $this->createPermissionCollection())
                                             ->isAllowed($action, $resource, $additionalAssertions, ...$argsForCallback);
            }
        }
        
        return $isAllowed;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    //////////////////// non-public methods ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    
    protected function throwInvalidArgExceptionDueToWrongClassName(
        string $class, string $function, string $wrongClassName, 
        string $expectedIntefaceName, string $positionthParameter
    ) {
        $msg = "Error [{$class}::{$function}(...)]:"
        . " You must specify the fully qualified name of a class that implements `{$expectedIntefaceName}` "
        . " as the {$positionthParameter} parameter to {$class}::{$function}(...)."
        . PHP_EOL . " You supplied a wrong value of: `{$wrongClassName}` ";
        
        throw new \InvalidArgumentException($msg); 
    }
    
    protected function createEntityCollection(): PermissionableEntitiesCollectionInterface {
        
        $collectionClassName = $this->permissionableEntitiesCollectionInterfaceClassName;
        
        return new $collectionClassName();
    }
    
    protected function createPermissionCollection(): PermissionsCollectionInterface {
        
        $collectionClassName = $this->permissionsCollectionInterfaceClassName;
        
        return new $collectionClassName();
    }
    
    protected function createEntity(string $entityId): PermissionableEntityInterface {
        
        $entityClassName = $this->permissionableEntityInterfaceClassName;
        
        return new $entityClassName($entityId, $this->createPermissionCollection(), $this->createEntityCollection());
    }
    
    protected function createPermission(
        string $action, string $resource, bool $allowActionOnResource = true, callable $additionalAssertions = null, ...$argsForCallback
    ): PermissionInterface {
        
        $permissionClassName = $this->permissionInterfaceClassName;
        
        return new $permissionClassName($action, $resource, $allowActionOnResource, $additionalAssertions, ...$argsForCallback);
    }
    
}
