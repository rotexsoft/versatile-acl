<?php
declare(strict_types=1);

namespace VersatileAcl;

use DateTime;
use ReflectionMethod;
use InvalidArgumentException;
use VersatileAcl\Interfaces\{
    PermissionInterface, PermissionableEntitiesCollectionInterface, 
    PermissionableEntityInterface, PermissionsCollectionInterface
};
use ReflectionParameter;
use RuntimeException;
use VersatileAcl\Exceptions\ParentCannotBeChildException;

use function array_key_exists;
use function array_shift;
use function func_get_args;
use function get_class;
use function gettype;
use function is_object;
use function is_subclass_of;
use function method_exists;
use function str_repeat;
use function str_replace;
use function trim;
use function var_export;

/**
 * A class for managing entities and permissions for access controlling resources in applications using this package 
 *
 * @author rotimi
 * 
 * @psalm-suppress RedundantCondition
 */
class VersatileAcl {
    
    /**
     *
     * @var string name of the class that implements PermissionableEntityInterface that will be used to create new entities
     * 
     */
    protected string $permissionableEntityInterfaceClassName;
    
    /**
     *
     * @var string name of the class that implements PermissionInterface that will be used to create new permissions
     *  
     */
    protected string $permissionInterfaceClassName;
    
    /**
     *
     * @var string name of the class that implements PermissionableEntitiesCollectionInterface that will be used to create new entity collections
     *  
     */
    protected string $permissionableEntitiesCollectionInterfaceClassName;
    
    /**
     *
     * @var string name of the class that implements PermissionsCollectionInterface that will be used to create new permission collections
     * 
     */
    protected string $permissionsCollectionInterfaceClassName;
    
    /**
     *
     * @var PermissionableEntitiesCollectionInterface|null collection of entities for each instance of this class
     */
    protected ?PermissionableEntitiesCollectionInterface $entitiesCollection;
    
    /**
     *
     * @var string tracks activities performed in methods in this class
     */
    protected string $auditTrail = '';
    
    /**
     *
     * @var bool true for activities performed in methods in this class to be tracked by concatenating descriptive messages to $this->auditTrail, false for no tracking
     */
    protected bool $auditActivities = false;
    
    /**
     * An integer to be used within $this->logActivity(..) to control the number of tabs to prepend to its return value
     *  
     * 
     * @var int 
     */
    protected int $numTabsForIndentingAudit = 0;
    
    /**
     * True means that $this->logActivity(string $description, string $shortDescription='') 
     * should use the first parameter for auditing, false means that it should use the second
     * parameter if not empty (else it will use the first parameter).
     * 
     * If $this->auditActivities === false, then the value of this property is irrelevant.
     * 
     * @var bool
     */
    protected bool $performVerboseAudit = true;


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
     * @param string $entityId ID of the entity to be added. It is treated in a case-insensitive manner, meaning that 'ALice' and 'alicE' both refer to the same entity
     */
    public function addEntity(string $entityId): self {
        
        $this->auditActivities
            && ++$this->numTabsForIndentingAudit // increment on first call to logActivity within a method
            && $this->logActivity(
                    "Entered " . __METHOD__ . "('{$entityId}') trying to create and add a new entity whose ID will be `{$entityId}`",
                    "Entered " . __METHOD__ . "('{$entityId}')"
                );
        
        $entity = $this->createEntity($entityId);
        
        $this->auditActivities &&  $this->logActivity("Entity created", "Entity created");
        
        if(!($this->entitiesCollection instanceof PermissionableEntitiesCollectionInterface)) {
            
            $this->entitiesCollection = $this->createEntityCollection();
            
            $this->auditActivities 
                && $this->logActivity(
                    "Initialized " . get_class($this) 
                    . '::entitiesCollection to a new empty instance of ' 
                    . get_class($this->entitiesCollection),
                    "Initialized " . get_class($this) . '::entitiesCollection'
                );
        }
        
        if(!$this->entitiesCollection->has($entity)) {
            
            $this->entitiesCollection->add($entity);
            
            $this->auditActivities 
                && $this->logActivity( 
                        "Successfully added the following entity:" .PHP_EOL . trim(''.$entity),
                        "Successfully added the entity whose ID is `{$entityId}`"
                   );
            
        } else {
            
            $this->auditActivities 
                && $this->logActivity(
                        "An entity with the specified entity ID `{$entityId}` already exists in the entities collection, no need to add",
                        "An entity with the specified entity ID `{$entityId}` already exists"
                   );
        }
        
        $this->auditActivities
            && $this->logActivity("Exiting " . __METHOD__ . "('{$entityId}')")
            && $this->numTabsForIndentingAudit--; // decrement on last call to logActivity within a method
        
        return $this;
    }
    
    /**
     * Removes an entity from an instance of this class if it exists.
     * 
     * @param string $entityId ID of the entity to be removed. It is treated in a case-insensitive manner, meaning that 'ALice' and 'alicE' both refer to the same entity
     * 
     * @return PermissionableEntityInterface|null the removed entity object or NULL if no such entity exists
     * 
     */
    public function removeEntity(string $entityId): ?PermissionableEntityInterface {
        
        $this->auditActivities
            && ++$this->numTabsForIndentingAudit // increment on first call to logActivity within a method
            && $this->logActivity(
                    "Entered " . __METHOD__ . "('{$entityId}') trying to remove the entity whose ID is `{$entityId}`",
                    "Entered " . __METHOD__ . "('{$entityId}')"
                );
        
        $entity = $this->getEntity($entityId);
        
        if($entity !== null) {
            
            ($this->entitiesCollection !== null) && $this->entitiesCollection->remove($entity);
            $this->auditActivities 
                && $this->logActivity("Successfully removed the entity whose ID is `{$entityId}`.");
        } else {
            
            $this->auditActivities 
                && $this->logActivity("The entity whose ID is `{$entityId}` does not exist, no need for removal.");
        }
        
        $this->auditActivities
            && $this->logActivity(
                    "Exiting " . __METHOD__ . "('{$entityId}')" . $this->formatReturnValueForAudit($entity),
                    "Exiting " . __METHOD__ . "('{$entityId}')"
                )
            && $this->numTabsForIndentingAudit--; // decrement on last call to logActivity within a method
        
        return $entity;
    }
    
    /**
     * Add an entity with an ID value of $parentEntityId as a parent entity to 
     * another entity with an ID value of $entityId in an instance of this class.
     * If an entity with an ID value of $entityId does not exist in the instance 
     * of this class upon which this method is called, the entity will be created
     * and added first before the parent entity is added to it.
     *
     * @param string $entityId ID of the entity to which a parent entity is to be added. It is treated in a case-insensitive manner, meaning that 'ALice' and 'alicE' both refer to the same entity
     * @param string $parentEntityId ID of the parent entity to be added. It is treated in a case-insensitive manner, meaning that 'ALice' and 'alicE' both refer to the same entity
     *
     *
     * @throws RuntimeException
     * @throws ParentCannotBeChildException
     */
    public function addParentEntity(string $entityId, string $parentEntityId): self {
        
        $this->auditActivities
            && ++$this->numTabsForIndentingAudit // increment on first call to logActivity within a method
            && $this->logActivity(
                    "Entered " . __METHOD__ . "('{$entityId}', '{$parentEntityId}')"
                    . " trying to add a new parent entity whose ID will be `{$parentEntityId}` to "
                    . " the entity whose ID is `{$entityId}`",
                    "Entered " . __METHOD__ . "('{$entityId}', '{$parentEntityId}')"
                );
        
        $existingEntity = $this->getEntity($entityId);

        if($existingEntity === null) {
         
            $this->auditActivities 
                && $this->logActivity("The entity whose ID is `{$entityId}` is not yet created, trying to create it now");
            
            $this->addEntity($entityId);
            $existingEntity = $this->getEntity($entityId);
        }
        
        if($existingEntity instanceof PermissionableEntityInterface) {

            $existingEntity->addParent($this->createEntity($parentEntityId));
            
            $this->auditActivities 
                && $this->logActivity("Parent entity whose ID is `{$parentEntityId}` has been added to the entity whose ID is `{$entityId}`");
            
        } else {
            
            // We should never really get here in most cases.
            // Something weird happened, we could not create or retrieve
            // the entity to which a parent is to be added
            $class = get_class($this);
            $function = __FUNCTION__;
            $msg = "Error [{$class}::{$function}(...)]:"
            . " Could not create or retrieve the entity with an ID of `{$entityId}`"
            . " to which the parent entity with an ID of `{$parentEntityId}` is to be added.";

            throw new RuntimeException($msg);
        }
        
        $this->auditActivities
            && $this->logActivity(
                    "Exiting " . __METHOD__ . "('{$entityId}', '{$parentEntityId}')"
                )
            && $this->numTabsForIndentingAudit--; // decrement on last call to logActivity within a method
        
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
        
        $this->auditActivities
            && ++$this->numTabsForIndentingAudit // increment on first call to logActivity within a method
            && $this->logActivity(
                    "Entered " . __METHOD__ . "('{$entityId}', '{$parentEntityId}')"
                    . " trying to remove the parent entity whose ID is `{$parentEntityId}` from "
                    . " the entity whose ID is `{$entityId}`",
                    "Entered " . __METHOD__ . "('{$entityId}', '{$parentEntityId}')"
                );
        
        $removedParentEntity = null;
        $existingEntity = $this->getEntity($entityId);
        
        if($existingEntity instanceof PermissionableEntityInterface) {
            
            $keyForParentEntity = $existingEntity->getDirectParents()->getKey($this->createEntity($parentEntityId));
            $removedParentEntity = $existingEntity->getDirectParents()->get(($keyForParentEntity === null)? '' : ''.$keyForParentEntity); // get the parent entity object
            $keyForParentEntity !== null
                && $existingEntity->getDirectParents()->removeByKey($keyForParentEntity); // remove the parent
            
            $this->auditActivities 
                && $this->logActivity("Parent entity has been successfully removed.");
            
        } else {
            
            $this->auditActivities 
                && $this->logActivity("The entity whose ID is `{$entityId}` doesn't exist, no need trying to remove a parent entity.");
        }
        
        $this->auditActivities
            && $this->logActivity(
                    "Exiting " . __METHOD__ . "('{$entityId}', '{$parentEntityId}')"
                    . $this->formatReturnValueForAudit($removedParentEntity),
                    "Exiting " . __METHOD__ . "('{$entityId}', '{$parentEntityId}')"
                )
            && $this->numTabsForIndentingAudit--; // decrement on last call to logActivity within a method
        
        return $removedParentEntity;
    }
    
    /**
     * Gets and returns an entity with specified Id from an instance of this class 
     * or returns NULL if an entity with specified Id doesn't already exist in the 
     * instance of this class this method is being invoked on.
     *
     *
     * @return PermissionableEntityInterface|null
     */
    public function getEntity(string $entityId): ?PermissionableEntityInterface {
        
        $this->auditActivities
            && ++$this->numTabsForIndentingAudit // increment on first call to logActivity within a method
            && $this->logActivity(
                    "Entered " . __METHOD__ . "('{$entityId}') trying to retrieve the entity whose ID is `{$entityId}`",
                    "Entered " . __METHOD__ . "('{$entityId}')"
               );
        
        $entityToReturn = null; 
        
        if($this->entitiesCollection instanceof PermissionableEntitiesCollectionInterface) {
            
            $entityToReturn = $this->entitiesCollection->find($entityId);
            
            $this->auditActivities 
                && $this->logActivity(
                        "Retrieved the following item: "
                        . (
                            ($entityToReturn instanceof PermissionableEntityInterface) 
                                ? trim(((string)$entityToReturn))
                                : trim(var_export($entityToReturn, true))
                        ),
                        (
                            ($entityToReturn instanceof PermissionableEntityInterface) 
                                ? "Successfully retrieved the desired entity." // avoid dumping the string representation of the entity for non-verbose audit
                                : "Retrieved the following item: " . trim(var_export($entityToReturn, true))
                        )
                    );
        }
        
        $this->auditActivities
            && $this->logActivity(
                    "Exiting " . __METHOD__ . "('{$entityId}')"
                    . $this->formatReturnValueForAudit($entityToReturn),
                    "Exiting " . __METHOD__ . "('{$entityId}')"
                )
            && $this->numTabsForIndentingAudit--; // decrement on last call to logActivity within a method
        
        return $entityToReturn;
    }
    
    /**
     * Returns a collection of all entities added to an instance of this class via $this->addEntity(string $entityId) or null if the collection has not yet been initialized
     * 
     * @return PermissionableEntitiesCollectionInterface a collection of all entities added to an instance of this class via $this->addEntity(string $entityId) or null if the collection has not yet been initialized
     */
    public function getAllEntities() : ?PermissionableEntitiesCollectionInterface {
        
        return $this->entitiesCollection;
    }
    
    /**
     * Add a permission to an entity with the ID value of $entityId. 
     * This entity will be created and added to the instance of this class upon
     * which this method is being invoked if the entity does not exist.
     *
     * @param string $entityId the ID of the entity to which the permission is to be added
     * @param callable|null $additionalAssertions
     * @param mixed $argsForCallback
     * @noinspection PhpUnhandledExceptionInspection
     * @see PermissionInterface::__construct($action, $resource, $allowActionOnResource, $additionalAssertions, ...$argsForCallback)
     * for definitions of all but the first parameter
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function addPermission(
        string $entityId, 
        string $action, 
        string $resource, 
        bool $allowActionOnResource = true, 
        callable $additionalAssertions = null, 
        ...$argsForCallback
    ): self {

        $this->auditActivities
            && ++$this->numTabsForIndentingAudit // increment on first call to logActivity within a method
            && $this->logActivity(
                    "Entered " . __METHOD__ . "(...) to try to add a permission to "
                    . " the entity whose ID is `{$entityId}`. Method Parameters: "
                    . PHP_EOL 
                    . 
                    trim(
                        var_export(
                            $this->getMethodParameterNamesAndVals(
                                __FUNCTION__,
                                [ 
                                    $entityId, $action, $resource, 
                                    $allowActionOnResource, 
                                    $additionalAssertions, 
                                    $argsForCallback
                                ]
                            ), 
                            true
                        )
                    ),
                    "Entered " . __METHOD__ . "(...)"
                );
        
        $existingEntity = $this->getEntity($entityId);
        
        if($existingEntity === null) {
         
            $this->auditActivities 
                && $this->logActivity("The entity whose ID is `{$entityId}` has not yet been created, trying to create it now");
            
            $this->addEntity($entityId);
            $existingEntity = $this->getEntity($entityId);
        }
        
        if($existingEntity instanceof PermissionableEntityInterface) {

            $existingEntity->addPermission(
                $this->createPermission($action, $resource, $allowActionOnResource, $additionalAssertions, ...$argsForCallback)
            );
            
            $this->auditActivities 
                && $this->logActivity(
                    "Permission with the parameters below has been added to the entity whose ID is `{$entityId}`:"
                    . PHP_EOL . "action: `{$action}`"
                    . PHP_EOL . "resource: `{$resource}`"
                    . PHP_EOL . "allowActionOnResource: " . var_export($allowActionOnResource, true)
                    . PHP_EOL . "additionalAssertions: " . var_export($additionalAssertions, true)
                    . PHP_EOL . "argsForCallback: " . var_export($argsForCallback, true)
                );
            
        } else {
            
            $funcArgs = func_get_args();
            array_shift($funcArgs);
            
            // We should never really get here in most cases. Something weird happened, 
            // we could not create or retrieve the entity to which a parent is to be added
            $class = get_class($this);
            $function = __FUNCTION__;
            $msg = "Error [{$class}::{$function}(...)]:"
            . " Could not create or retrieve the entity with an ID of `{$entityId}`"
            . " to which the following permission is to be added:"
            . PHP_EOL 
            . PHP_EOL . "action: `{$action}`"
            . PHP_EOL . "resource: `{$resource}`"
            . PHP_EOL . "allowActionOnResource: " . var_export($allowActionOnResource, true)
            . PHP_EOL . "additionalAssertions: " . var_export($additionalAssertions, true)
            . PHP_EOL . "argsForCallback: " . var_export($argsForCallback, true);

            throw new RuntimeException($msg);
        }
        
        $this->auditActivities
            && $this->logActivity(
                    "Exiting " . __METHOD__ . "(...)"
                )
            && $this->numTabsForIndentingAudit--; // decrement on last call to logActivity within a method
        
        return $this;
    }

    /**
     * Remove a permission from the entity with an ID value of $entityId and return the removed permission
     * or return null if either the entity and / or permission do not exist.
     *
     * @param callable|null $additionalAssertions
     * @param mixed $argsForCallback
     *
     * @return PermissionInterface|null
     * @noinspection PhpUnhandledExceptionInspection
     * @see PermissionInterface::__construct($action, $resource, $allowActionOnResource, $additionalAssertions, $argsForCallback)
     * for definitions of all but the first parameter
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function removePermission(
        string $entityId, 
        string $action, 
        string $resource, 
        bool $allowActionOnResource = true, 
        callable $additionalAssertions = null, 
        ...$argsForCallback
    ): ?PermissionInterface {

        $this->auditActivities
            && ++$this->numTabsForIndentingAudit // increment on first call to logActivity within a method
            && $this->logActivity(
                    "Entered " . __METHOD__ . "(...) to try to remove a permission from "
                    . " the entity whose ID is `{$entityId}`. Method Parameters: "
                    . PHP_EOL 
                    . 
                    trim(
                        var_export(
                            $this->getMethodParameterNamesAndVals(
                                __FUNCTION__,
                                [ 
                                    $entityId, $action, $resource, 
                                    $allowActionOnResource, 
                                    $additionalAssertions, 
                                    $argsForCallback
                                ]
                            ), 
                            true
                        )
                    ),
                    "Entered " . __METHOD__ . "(...)"
                );
        
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
            $removedPermission = $existingEntity->getDirectPermissions()->get(($keyForPermission === null) ? '' : ''.$keyForPermission); // get the permission object
            $keyForPermission !== null
                && $existingEntity->getDirectPermissions()->removeByKey($keyForPermission); // remove the permission
            
            $this->auditActivities 
                && $this->logActivity(
                    "Permission with the parameters below has been removed from the entity whose ID is `{$entityId}`:"
                    . PHP_EOL . "action: `{$action}`"
                    . PHP_EOL . "resource: `{$resource}`"
                    . PHP_EOL . "allowActionOnResource: " . var_export($allowActionOnResource, true)
                    . PHP_EOL . "additionalAssertions: " . var_export($additionalAssertions, true)
                    . PHP_EOL . "argsForCallback: " . var_export($argsForCallback, true)
                );
        } else {
            
            $this->auditActivities 
                && $this->logActivity("The entity whose ID is `{$entityId}` doesn't exist, no need trying to remove the specified permission.");
        }
        
        $this->auditActivities
            && $this->logActivity(
                    "Exiting " . __METHOD__ . "(....)"
                    . $this->formatReturnValueForAudit($removedPermission),
                    "Exiting " . __METHOD__ . "(....)"
                )
            && $this->numTabsForIndentingAudit--; // decrement on last call to logActivity within a method
        
        return $removedPermission;
    }

    /**
     * Check if the specified action $action can be performed on the specified
     * resource $resource based on the existing permissions associated with
     * either the specified entity with an ID of $entityId or all entities
     * associated with the instance  of this class this method is being invoked
     * on if $entityId === ''
     *
     * @param string $entityId ID of the entity whose permissions will be searched.
     *                                          Pass an empty string to search the permissions for
     *                                          all entities added to the instance of this class
     *                                          this method is being invoked on.
     * @param string $action See the see section above
     * @param string $resource See the see section above
     * @param callable|null $additionalAssertions See the see section above
     * @param mixed $argsForCallback See the see section above
     *
     * @see PermissionInterface::isAllowed($action, $resource, $additionalAssertions, ...$argsForCallback)
     * for definitions of all but the first parameter
     *
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function isAllowed(string $entityId, string $action, string $resource, callable $additionalAssertions = null, ...$argsForCallback): bool  {
        
        $this->auditActivities
            && ++$this->numTabsForIndentingAudit // increment on first call to logActivity within a method
            && $this->logActivity(
                    "Entered " . __METHOD__ . "(...) to check if the entity `{$entityId}`"
                    . " is allowed to perform the specified action `{$action}` on the"
                    . " specified resource `{$resource}`.  "
                    . PHP_EOL . 'Supplied Method Parameters:'
                    . PHP_EOL 
                    . 
                    trim(
                        var_export(
                            $this->getMethodParameterNamesAndVals(
                                __FUNCTION__,
                                [ 
                                    $entityId, 
                                    $action, $resource,
                                    $additionalAssertions, 
                                    $argsForCallback
                                ]
                            ), 
                            true
                        )
                    ),
                    "Entered " . __METHOD__ . "(...)"
                );
        
        $isAllowed = false;
        
        if( $entityId === '' ) {
            
            $this->auditActivities 
                && $this->logActivity(
                        "An empty string was supplied as the entity ID, so we"
                        . " are searching through permissions for all existing"
                        . " entities until we get the first match"
                    );
            
            if($this->entitiesCollection instanceof PermissionableEntitiesCollectionInterface) {
                 // loop through all entities
                /** @var PermissionableEntityInterface $currentEntity */
                foreach ($this->entitiesCollection as $currentEntity) {

                    $this->auditActivities 
                        && $this->logActivity(
                                "Currently searching through the permissions for the entity whose ID is `{$currentEntity->getId()}`"
                            );

                    // Get all permissions including inherited ones and check if 
                    // permission test evaluates to true for any of the permissions.
                    if( 
                        $currentEntity->getAllPermissions(true, $this->createPermissionCollection())
                                      ->isAllowed($action, $resource, $additionalAssertions, ...$argsForCallback)
                    ) {
                        $this->auditActivities 
                            && $this->logActivity(
                                    "Found a permission belonging to the entity"
                                    . " whose ID is `{$currentEntity->getId()}`"
                                    . " that allows the specified action `{$action}`"
                                    . " to be performed on the specified resource `{$resource}`."
                                );
                        $isAllowed = true;
                        break;
                    }
                }
            } // if($this->entitiesCollection instanceof PermissionableEntitiesCollectionInterface) 
            
            if(!$isAllowed) {
                $this->auditActivities 
                    && $this->logActivity(
                            "Did not find any permission belonging to any entity"
                            . " that allows the specified action `{$action}`"
                            . " to be performed on the specified resource `{$resource}`."
                            . PHP_EOL . PHP_EOL . "Either no entity"
                            . " has such a permission or an entity has a permission that explicitly"
                            . " denies the specified action `{$action}` from being performed"
                            . " on the specified resource `{$resource}`"
                        );
            }
            
        } else {
            
            $this->auditActivities 
                && $this->logActivity(
                        "Trying to retrieve the entity object associated with specified entity ID `{$entityId}`"
                    );
            
            // get specified entity if it exists and check through its permissions
            // including inherited ones, to if the permission test evaluates to 
            // true for any of the permissions
            $specifiedEntity = $this->getEntity($entityId);
            
            if($specifiedEntity instanceof PermissionableEntityInterface) {
                
                $this->auditActivities 
                    && $this->logActivity(
                            "Successfully retrieved the entity object associated with specified entity ID `{$entityId}`."
                        );
                            
                $this->auditActivities 
                    && $this->logActivity(
                            "Searching through the permissions for the entity whose ID is `{$entityId}`"
                        );
                
                $isAllowed = $specifiedEntity->getAllPermissions(true, $this->createPermissionCollection())
                                             ->isAllowed($action, $resource, $additionalAssertions, ...$argsForCallback);
                
                if($isAllowed) {
                    
                    $this->auditActivities 
                        && $this->logActivity(
                                "Found a permission belonging to the entity"
                                . " whose ID is `{$entityId}`"
                                . " that allows the specified action `{$action}`"
                                . " to be performed on the specified resource `{$resource}`."
                            );
                } else {
                    
                    $this->auditActivities 
                        && $this->logActivity(
                                "Did not find any permission belonging to the entity"
                                . " whose ID is `{$entityId}`"
                                . " that allows the specified action `{$action}`"
                                . " to be performed on the specified resource `{$resource}`."
                                . PHP_EOL . PHP_EOL . "Either the entity whose ID is `{$entityId}`"
                                . " has no such permission or has a permission that explicitly"
                                . " denies the specified action `{$action}` from being performed"
                                . " on the specified resource `{$resource}`"
                            );
                }
                
            } else {
                
                $this->auditActivities 
                    && $this->logActivity(
                            "Could not retrieve the entity object associated with specified entity ID `{$entityId}`."
                        );
            }
        }
        
        $this->auditActivities 
            && $this->logActivity(
                    "Exiting " . __METHOD__ . "(....)" 
                    .$this->formatReturnValueForAudit($isAllowed),
                    "Exiting " . __METHOD__ . "(....)" 
                )
            && $this->numTabsForIndentingAudit--; // decrement on last call to logActivity within a method
        
        return $isAllowed;
    }
    
    /**
     * 
     * @return PermissionableEntitiesCollectionInterface
     * 
     */
    public function createEntityCollection(): PermissionableEntitiesCollectionInterface {
        
        $collectionClassName = $this->permissionableEntitiesCollectionInterfaceClassName;
        
        /** @var PermissionableEntitiesCollectionInterface */
        return new $collectionClassName();
    }
    
    /**
     * 
     * @return PermissionsCollectionInterface
     * 
     */
    public function createPermissionCollection(): PermissionsCollectionInterface {
        
        $collectionClassName = $this->permissionsCollectionInterfaceClassName;
        
        /** @var PermissionsCollectionInterface */
        return new $collectionClassName();
    }

    /**
     * @param string $entityId the ID of the entity to be created
     *
     * @return PermissionableEntityInterface the created entity object
     * 
     */
    public function createEntity(string $entityId): PermissionableEntityInterface {
        
        $entityClassName = $this->permissionableEntityInterfaceClassName;
        
        /** @var PermissionableEntityInterface */
        return new $entityClassName($entityId, $this->createPermissionCollection(), $this->createEntityCollection());
    }

    /**
     * @see PermissionInterface::__construct($action, $resource, $allowActionOnResource, $additionalAssertions, ...$argsForCallback)
     * for definitions of all the parameters of this method
     *
     * @param callable|null $additionalAssertions
     * @param mixed ...$argsForCallback
     *
     * @return PermissionInterface
     * 
     */
    public function createPermission(
        string $action, string $resource, bool $allowActionOnResource = true, callable $additionalAssertions = null, ...$argsForCallback
    ): PermissionInterface {
        
        $permissionClassName = $this->permissionInterfaceClassName;
        
        /** @var PermissionInterface */
        return new $permissionClassName($action, $resource, $allowActionOnResource, $additionalAssertions, ...$argsForCallback);
    }
    
    
    /**
     * Empties the contents of the Audit Trail containing the trace of all logged internal activities 
     *
     */
    public function clearAuditTrail(): self {
        
        $this->auditTrail = '';
        
        return $this;
    }
    
    /**
     * Returns a string containing a trace of all logged internal activities 
     *
     */
    public function getAuditTrail(): string {
        
        return $this->auditTrail;
    }
    
    /**
     * Enables or disables the logging of internal activities performed in the public methods of this class
     *
     * @param bool $canAudit true to start logging internal activities, false to stop logging internal activities
     */
    public function enableAuditTrail(bool $canAudit=true): self {
        
        $this->auditActivities = $canAudit;
        
        return $this;
    }
    
    /**
     * Sets a boolean value for $this->performVerboseAudit.
     *
     * True means that $this->logActivity(string $description, string $shortDescription='') 
     * should use the first parameter for auditing, false means that it should use the second
     * parameter if not empty (else it will use the first parameter).
     *
     *
     */
    public function enableVerboseAudit(bool $performVerboseAudit=true): self {
        
        $this->performVerboseAudit = $performVerboseAudit;
        
        return $this;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    //////////////////// non-public methods ////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    
    protected function logActivity(string $description, string $shortDescription=''): self {
        
        if($this->auditActivities) {
            
            $useShortDescr = $shortDescription !== '' && (!$this->performVerboseAudit);
            
            $this->auditTrail .= 
                (($this->numTabsForIndentingAudit <= 1) ? '' : str_repeat("\t", $this->numTabsForIndentingAudit))
                . '[' . (new DateTime())->format('Y-m-d H:i:s') . ']: '
                . 
                (
                    ($this->numTabsForIndentingAudit <= 1) 
                    ? 
                        (
                            $useShortDescr
                            ? $shortDescription
                            : $description 
                        )
                    : 
                        (
                            $useShortDescr
                            ? str_replace(PHP_EOL, PHP_EOL . str_repeat("\t", $this->numTabsForIndentingAudit), $shortDescription)
                            : str_replace(PHP_EOL, PHP_EOL . str_repeat("\t", $this->numTabsForIndentingAudit), $description)
                        )
                )
                . PHP_EOL .PHP_EOL .PHP_EOL;
        }
        
        return $this;
    }
    
    protected function throwInvalidArgExceptionDueToWrongClassName(
        string $class, string $function, string $wrongClassName, 
        string $expectedInterfaceName, string $positionthParameter
    ): void {
        $msg = "Error [{$class}::{$function}(...)]:"
        . " You must specify the fully qualified name of a class that implements `{$expectedInterfaceName}` "
        . " as the {$positionthParameter} parameter to {$class}::{$function}(...)."
        . PHP_EOL . " You supplied a wrong value of: `{$wrongClassName}` ";
        
        throw new InvalidArgumentException($msg);
    }

    /**
     * Returns an associative array whose keys are the names of the parameters for the specified method ($methodName) and the values are the values in $paramVals
     *
     * @param string $methodName name of a method in this class
     * @param array<string|int, mixed> $paramVals an array of values supplied as arguments to the method named by $methodName
     *
     * @return array an associative array whose keys are the names of the parameters for the specified method ($methodName) and the values are the values in $paramVals
     * @noinspection PhpRedundantVariableDocTypeInspection
     * @throws \ReflectionException if $methodName is not the name of an actual method in this class
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    protected function getMethodParameterNamesAndVals(string $methodName, array $paramVals): array {
        
        $paramPosToNameMap = [];
        
        if(method_exists($this, $methodName)) {
        
            $refMethodObj = new ReflectionMethod($this, $methodName);

            $parameters = $refMethodObj->getParameters();

            foreach ($parameters as $parameter) {

                $pos = $parameter->getPosition();
                
                if(array_key_exists($pos, $paramVals)) {
                    
                    /** @var mixed */
                    $paramPosToNameMap[$parameter->getName()] = $paramVals[$pos];
                }
            }
        }
        
        return $paramPosToNameMap;
    }
    
    /**
     * Helper method to describe the value in $returnVal
     * 
     * @param mixed $returnVal
     * 
     * @return string description of the value in $returnVal
     */
    protected function formatReturnValueForAudit($returnVal): string {

        $returnType = gettype($returnVal);
        $formattedSentence = 
            " with a return type of `$returnType` and actual return value of "
            . trim(var_export($returnVal, true));
        
        if(is_object($returnVal)) {
            
            $formattedSentence = 
                " with a return type of `object` that is an instance of" 
                . " `" . get_class($returnVal) . "` with the following"
                . " string representation: "
                . PHP_EOL
                . 
                    (
                        (method_exists($returnVal, '__toString'))
                        ? trim(((string)$returnVal))
                        : trim(var_export($returnVal, true))
                    );
        }
        
        return $formattedSentence;
    }
}
