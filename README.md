# simple-acl
A simple and highly flexible and customizable access control library for PHP 

Entities
----------
* Resource: an item of which an **action** will be denied or allowed to be performed on.
It is just a case-insensitive string as far as this package is concerned.

* Action: represents a task that can be performed on a **resource**. 
It is just a case-insensitive string as far as this package is concerned.

* Permission: an entity defining whether or not a **group** or a **user** 
is allowed or not allowed to perform an **action** on a particular **resource**.

* User: has on or more unique **permissions** and can belong to one or more unique **groups** 
and consequently inherit permissions from those groups. Permissions directly 
associated with a user have higher priority than those inherited from groups 
from the group(s). Must have a unique string identifier.

* Group: has on or more unique **permissions** and can have one or more unique **users** associated with it.
Must have a unique string identifier.

Ideas / Work in Progress

```
Simple Acl

SimpleAclUserInterface:
	getId(): string

    addGroup(SimpleAclGroupInterface $group): static | throw exception if unsuccesfull
    addGroups(SimpleAclGroupsInterface $groups): static | throw exception if unsuccesfull
    belongsToGroup(SimpleAclGroupInterface $group): bool
    belongsToAtleastOneGroup(SimpleAclGroupsInterface $groups): bool
    belongsToAllGroups(SimpleAclGroupsInterface $groups): bool
	getGroups(): SimpleAclGroupsInterface
    removeGroup(SimpleAclGroupInterface $group): static | throw exception if unsuccesfull
    removeGroups(SimpleAclGroupsInterface $groups): static | throw exception if unsuccesfull

    addPermission(SimpleAclPermissionInterface $perm): static | throw exception if unsuccesfull
    addPermissions(SimpleAclPermissionsInterface $perms): static | throw exception if unsuccesfull
    getPermissions(): SimpleAclPermissionsInterface
    hasPermission(SimpleAclPermissionInterface $perm): bool
    hasAtleastOnePermission(SimpleAclPermissionsInterface $perms): bool
    hasAllPermissions(SimpleAclPermissionsInterface $perms): bool    
    removePermission(SimpleAclPermissionInterface $perm): static | throw exception if unsuccesfull
    removePermissions(SimpleAclPermissionsInterface $perms): static | throw exception if unsuccesfull

    __construct(string $id, SimpleAclGroupsInterface $groups=null, SimpleAclPermissionsInterface $perms=null)

SimpleAclGroupInterface:
    getId(): string

    addUser(SimpleAclUserInterface $user): static | throw exception if unsuccesfull
    addUsers(SimpleAclUsersInterface $users): static | throw exception if unsuccesfull
    getUsers(): SimpleAclUsersInterface
    hasUser(SimpleAclUserInterface $user): bool
    hasAtleastOneUser(SimpleAclUsersInterface $users): bool
    hasAllUsers(SimpleAclUsersInterface $users): bool  
    removeUser(SimpleAclUserInterface $user): static | throw exception if unsuccesfull
    removeUsers(SimpleAclUsersInterface $users): static | throw exception if unsuccesfull

    addPermission(SimpleAclPermissionInterface $perm): static | throw exception if unsuccesfull
    addPermissions(SimpleAclPermissionsInterface $perms): static | throw exception if unsuccesfull
    getPermissions(): SimpleAclPermissionsInterface
    hasPermission(SimpleAclPermissionInterface $perm): bool
    hasAtleastOnePermission(SimpleAclPermissionsInterface $perms): bool
    hasAllPermissions(SimpleAclPermissionsInterface $perms): bool    
    removePermission(SimpleAclPermissionInterface $perm): static | throw exception if unsuccesfull
    removePermissions(SimpleAclPermissionsInterface $perms): static | throw exception if unsuccesfull

    __construct(string $id, SimpleAclUsersInterface $users=null, SimpleAclPermissionsInterface $perms=null)

SimpleAclPermissionInterface
    getAction(): string
    static getAllActionsIdentifier(): string [a special string representing all performable actions on a resource]

    getResource(): string
    static getAllResoucesIdentifier(): string [a special string representing all resources in the system]

    __construct(string $action, string $resource, bool $allow_action_on_resource=true)


Collections:
    SimpleAclCollectionInterface extends \ArrayAccess, \Countable, \IteratorAggregate
        SimpleAclUsersInterface  extends SimpleAclCollectionInterface
        SimpleAclGroupsInterface  extends SimpleAclCollectionInterface
        SimpleAclPermissionsInterface  extends SimpleAclCollectionInterface
	

AclUserAssertionInterface
	allowActionForGroup(AclGroupActionTagInterface $action_tag,  AclGroupInterface $group)
	allowActionForGroups(AclGroupActionTagInterface $action_tag,  AclGroupInterface ...$group)
	denyActionForGroup(AclGroupActionTagInterface $action_tag,  AclGroupInterface $group)
	denyActionForGroups(AclGroupActionTagInterface $action_tag,  AclGroupInterface ...$group)
	
	isUserAllowedToPerformAction(AclGroupActionTagInterface $action_tag): bool
	
	isUserAllowedByOwnership(AclUserInterface $user, AclUserOwnableInterface $ownable): bool
```
