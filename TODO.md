# Things To Do
 * ~~Implement Generic Classes implementing the various interfaces~~ [DONE]
 * Implement a profiling mechanism for debugging purposes that shows an audit trail of how permissions were calculated when isAllowed is invoked
 * ~~Write unit tests~~ [DONE]
 * ~~Hook up to travis and other code monitoring services~~ [DONE]
 * Implement a separate package illustrating how to implement Owner, User and Group level permission enforcement
 * Check other stuff in my other projects that could be of value in this one
 * ~~Update class diagram once package is stable~~ [DONE]
 * Document using this package using acl examples from existing application and even using examples from the zend packages.
    * Add guidelines on how to customize this package to suit various requirements like the 
    Owner, User and Group level permission enforcement described above.
 * If possible, Implement a single class that can be used to marshall the various objects for an application
    * This way, most users of this package will only need to interact with this class to help them avoid the mental complexity of managing entity and permissions objects and collections themselves
    * For example, the class could be called \SimpleAcl\SimpleAcl (it should have ) having instance and static methods like:
        * This \SimpleAcl\SimpleAcl class will have a protected property of type PermissionableEntitiesCollectionInterface which is a collection for which entities are added under the hood
        * __construct(
            string $permissionableEntityInterfaceClassName = GenericPermissionableEntity::class,
            string $permissionInterfaceClassName = GenericPermission::class,
            string $permissionableEntitiesCollectionInterfaceClassName = GenericPermissionableEntitiesCollection::class,
            string $permissionsCollectionInterfaceClassName = GenericPermissionsCollection::class
        )
        * addEntity(string $entityId): $this see first parameter of the constructor of GenericPermissionableEntity
        * getEntity(string $entityId): PermissionableEntityInterface
        * getAllEntities(): PermissionableEntitiesCollectionInterface
        * addParentEntity(string $entityId, string $parentEntityId): $this 
        * removeParentEntity(string $entityId, string $parentEntityId): $this 
        * addPermission(string $entityId, string $action, string $resource, bool $allowActionOnResource = true, callable $additionalAssertions = null, ...$argsForCallback): $this 
        * removePermission(string $entityId, string $action, string $resource, bool $allowActionOnResource = true, callable $additionalAssertions = null, ...$argsForCallback): $this 
        * isAllowed(string $action, string $resource, string $entityId = '', callable $additionalAssertions = null, ...$argsForCallback): bool see GenericPermission::isAllowed for param documentation
            * $entityId === '' means search through all entities and break at the first entity whose permissions collection contains a matching permission or return false if no matching permission is found
        
    
 * Submit to packagist once it's well done.