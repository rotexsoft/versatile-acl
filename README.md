# Simple Acl

[![Build Status](https://img.shields.io/travis/rotexsoft/simple-acl/master.png?style=flat-square)](https://travis-ci.org/rotexsoft/simple-acl) &nbsp; 
[![Release](https://img.shields.io/github/release/rotexsoft/simple-acl.png?style=flat-square)](https://github.com/rotexsoft/simple-acl/releases/latest) &nbsp; 
[![License](https://img.shields.io/badge/license-BSD-brightgreen.png?style=flat-square)](https://github.com/rotexsoft/simple-acl/blob/master/LICENSE) &nbsp; 


A simple, highly flexible and customizable access control library for PHP applications.


## Installation 

**Via composer:** (Requires PHP 7.2+). 

    composer require rotexsoft/simple-acl

## Introduction
A PHP application can use this library to define **Permissionable Entities** (e.g. application users or groups that users can belong to).
* Each entity is an instance of **[\SimpleAcl\Interfaces\PermissionableEntityInterface](src/interfaces/PermissionableEntityInterface.php)** 
which is implemented by **[\SimpleAcl\GenericPermissionableEntity](src/GenericPermissionableEntity.php)** in this package.
* Each entity can be associated to another entity as a parent Entity. 
* Each entity can have one or more permissions defined. These are **direct permissions**
    * A **permission** in this library is an object that represents whether or not an **action** (represented by a case-insensitive string) can be performed by an entity on a **resource** (represented by a case-insensitive string).
    * A permission is an instance of **[\SimpleAcl\Interfaces\PermissionInterface](src/interfaces/PermissionInterface.php)** which is implemented by 
    **[\SimpleAcl\GenericPermission](src/GenericPermission.php)** in this package.
* Each entity also inherits permissions from its parent entities.
    * The library allows you to give direct permissions a higher priority than inherited permissions (the default behavior) and also allows you to do the reverse, if you so desire.

 
Click [here](class-diagram.png) to see the full Class Diagram of this library.

In your applications, you will be mostly be working with instances of **[SimpleAcl\SimpleAcl](src/SimpleAcl.php)**; this class exposes most of the functionality of the underlying classes in this package listed below: 
* **[\SimpleAcl\GenericPermissionableEntity](src/GenericPermissionableEntity.php) :** Represents an entity in your application

* **[\SimpleAcl\GenericPermissionableEntitiesCollection](src/GenericPermissionableEntitiesCollection.php) :** A collection class for storing one or more entities in your application

* **[\SimpleAcl\GenericPermission](src/GenericPermission.php) :** Represents a permission to be assigned to an entity in your application

* **[\SimpleAcl\GenericPermissionsCollection](src/GenericPermissionsCollection.php) :** A collection class for storing one or more permissions belonging to a particular entity in your application. It is possible to assign the same instance of this class to more than one entity, but it is recommended that you maintain separate instances of this class for each entity in your application.


## Example Real-world Usage

We will be using a blog application that has a users table containing information
about registered blog users (the users in this table are also authors of blog posts and commentators on blog posts in the application), a posts table and a comments table. Below is the schema for the sample application:

![example blog database schema](docs/blog.png)

Below are the relationship rules for the blog application

* A user can author many posts
* A user can make one or more comments on each post
* A post can have one or more comments associated with it

Below are some access control group definitions that are relevant to this sample blog application:

| Group Name          | Resource | Action  | Allowed |
|---------------------|----------|---------|---------|
| admin               | all      | all     | yes     |
| comments-moderators | comment  | approve | yes     |
| comments-moderators | comment  | delete  | yes     |
| comments-owners     | comment  | all     | yes     |
| posts-moderators    | post     | approve | yes     |
| posts-moderators    | post     | delete  | yes     |
| posts-owners        | post     | all     | yes     |

> **NOTE:** the permissions associated with the **comments-owners** and **posts-owners** will require an assertion callback that further checks that members of the group can only perform actions on the comments or posts they own (not comments and posts owned by other users).

Let's model these groups using [SimpleAcl\SimpleAcl](src/SimpleAcl.php).

First, we create entity objects representing each group.

```php
<?php
use SimpleAcl\SimpleAcl;

$sAclObj = new SimpleAcl();

$adminEntity = $sAclObj->createEntity('admin');
$commentsModeratorsEntity = $sAclObj->createEntity('comments-moderators');
$commentsOwnersEntity = $sAclObj->createEntity('comments-owners');
$postsModeratorsEntity = $sAclObj->createEntity('posts-moderators');
$postsOwnersEntity = $sAclObj->createEntity('posts-owners');
```

Next we create the required permission objects that we will be added later on to each group's entity object.

```php
<?php
// Permission below will allow an entity to
// perform any action on any resource in
// an application
$adminPermissionAll = 
    $sAclObj->createPermission(
        \SimpleAcl\GenericPermission::getAllActionsIdentifier(),
        \SimpleAcl\GenericPermission::getAllResourcesIdentifier(), 
        true
    );

// Permission below allows an entity to approve 
// comments made on a blog post
$approveCommentsPermission = 
    $sAclObj->createPermission('approve', 'comment', true);

// Permission below allows an entity to delete 
// comments made on a blog post
$deleteCommentsPermission = 
    $sAclObj->createPermission('delete', 'comment', true);

// Permission below allows an entity to both  
// approve and delete comments made on 
// blog posts created by the entity
$approveAndDeleteCommentsByOwnerPermission = 
    $sAclObj->createPermission(
        \SimpleAcl\GenericPermission::getAllActionsIdentifier(), 
        'comment', 
        true,
        function(array $userRecord, array $commentRecord){
            return isset($userRecord['id'])
                && isset($commentRecord['commenter_id'])
                && $userRecord['id'] === $commentRecord['commenter_id'];
        } 
    );

// Permission below allows an entity to approve 
// any blog post created in your application
$approvePostsPermission = 
    $sAclObj->createPermission('approve', 'post', true);

// Permission below allows an entity to delete 
// any blog post created in your application
$deletePostsPermission = 
    $sAclObj->createPermission('delete', 'post', true);

// Permission below allows an entity to both  
// approve and delete blog posts 
// created by the entity
$approveAndDeletePostsByOwnerPermission = 
    $sAclObj->createPermission(
        \SimpleAcl\GenericPermission::getAllActionsIdentifier(), 
        'post', 
        true,
        function(array $userRecord, array $blogPostRecord){
            return isset($userRecord['id'])
                && isset($blogPostRecord['creators_id'])
                && $userRecord['id'] === $blogPostRecord['creators_id'];
        } 
    );
```
> **NOTE:** \SimpleAcl\GenericPermission::getAllActionsIdentifier() is a special string that represents all actions any entity can perform on each resource in your application.

> **NOTE:** \SimpleAcl\GenericPermission::getAllResourcesIdentifier() is a special string that represents all available resources in your application.

Next we add the permission objects to each respective group's entity object.

| Group Name          | Resource | Action  | Allowed |
|---------------------|----------|---------|---------|
| admin               | all      | all     | yes     |

```php
<?php

$adminEntity->addPermission($adminPermissionAll);
```

| Group Name          | Resource | Action  | Allowed |
|---------------------|----------|---------|---------|
| comments-moderators | comment  | approve | yes     |
| comments-moderators | comment  | delete  | yes     |

```php
<?php

$commentsModeratorsEntity->addPermission($approveCommentsPermission)
                         ->addPermission($deleteCommentsPermission);
```

| Group Name          | Resource | Action  | Allowed |
|---------------------|----------|---------|---------|
| comments-owners     | comment  | all     | yes     |

```php
<?php

$commentsOwnersEntity->addPermission($approveAndDeleteCommentsByOwnerPermission);
```

| Group Name          | Resource | Action  | Allowed |
|---------------------|----------|---------|---------|
| posts-moderators    | post     | approve | yes     |
| posts-moderators    | post     | delete  | yes     |

```php
<?php

$postsModeratorsEntity->addPermission($approvePostsPermission)
                      ->addPermission($deletePostsPermission);
```

| Group Name          | Resource | Action  | Allowed |
|---------------------|----------|---------|---------|
| posts-owners        | post     | all     | yes     |

```php
<?php

$postsOwnersEntity->addPermission($approveAndDeletePostsByOwnerPermission);
```


Now that we have created entity objects for each group and created the necessary permission objects and added them to the appropriate entity objects, we are ready to go ahead with defining the entity objects that will represent the users below in the blog application. 

Below is a list of userids of users in the application

* frankwhite
* ginawhite
* johndoe
* janedoe
* jackbauer
* jillbauer

Let's create and register entity objects for each user in our **SimpleAcl** object:

```php
<?php

$sAclObj->addEntity('frankwhite')
        ->addEntity('ginawhite')
        ->addEntity('johndoe')
        ->addEntity('janedoe')
        ->addEntity('jackbauer')
        ->addEntity('jillbauer');
```

Below are the group membership definitions:

| Group               | User      |
|---------------------|-----------|
| admin               | frankwhite|
| comments-moderators | ginawhite |
| comments-moderators | johndoe   |
| posts-moderators    | janedoe   |
| comments-owners     | all       |
| posts-owners        | all       |

<br>
Let's model these relationships by adding the appropriate entity objects representing the groups as parent entities to the respective user entity objects:

```php
<?php

// add 'frankwhite' to the admin group
$sAclObj->getEntity('frankwhite')->addParent($adminEntity);

// add 'ginawhite' to the comments-moderators group
$sAclObj->getEntity('ginawhite')->addParent($commentsModeratorsEntity);

// add 'johndoe' to the comments-moderators group
$sAclObj->getEntity('johndoe')->addParent($commentsModeratorsEntity);

// add 'janedoe' to the posts-moderators group
$sAclObj->getEntity('janedoe')->addParent($postsModeratorsEntity);
    
// Create an entity called 'all' whose permissions will apply
// to all users. Add the $commentsOwnersEntity and
// $postsOwnersEntity as its parent entities.
// This entity models the group memberships below
// | Group               | User      |
// |---------------------|-----------|
// | comments-owners     | all       |
// | posts-owners        | all       |

$sAclObj->addEntity('all')
        ->getEntity('all')
        ->addParent($commentsOwnersEntity)
        ->addParent($postsOwnersEntity);

```

Now that we have set up or groups, users and permissions, let's see how to check if a user is allowed to perform an action on a resource in our application.

Let's start with the user **'frankwhite'** that belongs to the **'admin'**  group. This user should be able to perform any action on any resource in the application:


```php
<?php

$sAclObj->isAllowed('frankwhite', 'approve', 'comment'); // === true
$sAclObj->isAllowed('frankwhite', 'delete', 'comment'); // === true
$sAclObj->isAllowed('frankwhite', 'approve', 'post'); // === true
$sAclObj->isAllowed('frankwhite', 'delete', 'post'); // === true
```

Now let's continue with the user **'ginawhite'** that belongs to the **'comments-moderators'**  group. This user should be able to only approve and delete comments in the application (the user should also be able to approve and delete posts they have created):

```php
<?php
$sAclObj->isAllowed('ginawhite', 'approve', 'comment'); // === true
$sAclObj->isAllowed('ginawhite', 'delete', 'comment'); // === true
$sAclObj->isAllowed('ginawhite', 'approve', 'post'); // === false
$sAclObj->isAllowed('ginawhite', 'delete', 'post'); // === false

// Assuming we have the post and comment records below and the user record for 'ginawhite' below
$postRecord = [
    'id' => 2,
    'body' => 'Some random post',
    'creators_id' => 'ginawhite',
    'last_updaters_id' => 'ginawhite',
    'date_created' => '2019-08-01 13:43:21',
    'last_updated' => '2019-08-01 13:43:21',
    'is_approved' => '0',
];

$commentRecord = [
    'id' => 1,
    'post_id' => 2,
    'commenter_id' => 'ginawhite',
    'comment' => 'Some random comment',
    'date_created' => '2019-08-01 13:43:21',
    'last_updated' => '2019-08-01 13:43:21',
    'is_approved' => '0',
];

$userRecord = [
    'id' => 'ginawhite',
    'password' => 'TydlfEUSqnVMu'
];

```




### Public Methods of **[SimpleAcl\SimpleAcl](src/SimpleAcl.php)**
| Method | Description |
|--------|-------------|
| **__construct( <br> string $permissionableEntityInterfaceClassName = GenericPermissionableEntity::class, <br> string $permissionInterfaceClassName = GenericPermission::class, <br> string $permissionableEntitiesCollectionInterfaceClassName = GenericPermissionableEntitiesCollection::class, <br> string $permissionsCollectionInterfaceClassName = GenericPermissionsCollection::class <br>)** | The constructor through which you can specify alternate fully qualified class names of classes that implement **[PermissionableEntityInterface](src/interfaces/PermissionableEntityInterface.php)**, **[PermissionInterface](src/interfaces/PermissionInterface.php)**, **[PermissionableEntitiesCollectionInterface](src/interfaces/PermissionableEntitiesCollectionInterface.php)** and **[PermissionsCollectionInterface](src/interfaces/PermissionsCollectionInterface.php)** .|
| **addEntity(string $entityId): self** | Adds an entity to an instance of this class if it doesn't already exist. Entity IDs are treated in a case-insensitive manner, meaning that 'ALice' and 'alicE' both refer to the same entity |
| **addParentEntity(string $entityId, string $parentEntityId): self** | Add an entity with an ID value of $parentEntityId as a parent entity to another entity with an ID value of $entityId in an instance of this class. <br> If an entity with an ID value of $entityId does not exist in the instance of this class upon which this method is called, the entity will be created and added first before the parent entity is added to it. <br>Both IDs are treated in a case-insensitive manner, meaning that if **$entityId** has a value of 'ALice' or 'alicE' both will refer to the same entity or if **$parentEntityId** has a value of 'ALice' or 'alicE' both will refer to the same parent entity. <br> You can use this method as a shortcut to creating both an entity and its associated parent entities, eliminating the need for calling **addEntity** first before calling this method. |
| **addPermission( <br> string $entityId, <br> string $action, <br> string $resource, <br> bool $allowActionOnResource = true, <br> callable $additionalAssertions = null, <br> ...$argsForCallback <br>): self** | Used for adding a permission to an entity with the specified ID. If the entity does not exist, it will be created and the permission will be added to it. <br>See **__construct** in **[PermissionInterface](src/interfaces/PermissionInterface.php)** for more information about all the parameters to this method (except the first). |
| **clearAuditTrail(): self** | Empties the contents of the Audit Trail containing the trace of all logged internal activities. |
| **createEntity(string $entityId): PermissionableEntityInterface** | A helper method for creating entity objects which you would be responsible for managing outside of this class. You would not normally need to use this method except if you want to more control over how the entities and permissions in your application are managed. |
| **createEntityCollection(): PermissionableEntitiesCollectionInterface** | A helper method for creating collection objects used to store entity objects which you would be responsible for managing outside of this class. You would not normally need to use this method except if you want to more control over how the entities and permissions in your application are managed. |
| **createPermission( <br>string $action, <br>string $resource, <br>bool $allowActionOnResource = true, <br>callable $additionalAssertions = null, <br>...$argsForCallback <br>): PermissionInterface** | A helper method for creating permission objects which you would be responsible for managing outside of this class. You would not normally need to use this method except if you want to more control over how the entities and permissions in your application are managed. <br>See **__construct** in **[PermissionInterface](src/interfaces/PermissionInterface.php)** for more information about the parameters to this method.|
| **createPermissionCollection(): PermissionsCollectionInterface** | A helper method for creating collection objects used to store permission objects which you would be responsible for managing outside of this class. You would not normally need to use this method except if you want to more control over how the entities and permissions in your application are managed. |
| **enableAuditTrail(bool $canAudit=true): self** | Enables or disables the logging of internal activities performed in some of the important public methods of this class. |
| **enableVerboseAudit(bool $performVerboseAudit=true): self** | Enables or disables verbose logging of internal activities performed in some of the important public methods of this class. This method only increases or decreases the level of detail of the internal activities that are logged. It does not not disable logging. |
| **getAllEntities() : ?PermissionableEntitiesCollectionInterface** | Returns a collection of all entities added to an instance of this class or null if the collection has not yet been initialized. |
| **getAuditTrail(): string** | Returns a string containing a trace of all logged internal activities performed in some of the important public methods of this class. |
| **getEntity(string $entityId): ?PermissionableEntityInterface** | Gets and returns an entity with specified Id from an instance of this class or returns NULL if an entity with specified Id doesn't already exist. |
| **isAllowed(string $entityId, string $action, string $resource, callable $additionalAssertions = null, ...$argsForCallback): bool** | Check if the specified action in **$action** can be performed on the specified resource in **$resource** based on the existing permissions associated with either the specified entity with an ID value in **$entityId** or all entities associated with the instance  of this class this method is being invoked on if **$entityId** === ''. <br>See **PermissionInterface::isAllowed($action, $resource, $additionalAssertions, ...$argsForCallback)** for definitions of all but the first parameter. |
| **removeEntity(string $entityId): ?PermissionableEntityInterface** | Removes an entity from an instance of this class if it exists and returns the removed entity or NULL if the entity does not exist. |
| **removeParentEntity(string $entityId, string $parentEntityId): ?PermissionableEntityInterface** | Remove and return an entity with an ID value in **$parentEntityId** that is a parent entity to another entity with an ID value in **$entityId**, if the instance of this class upon which this method is being called contains an entity with the ID value in **$entityId**, else NULL is returned. |
| **removePermission(string $entityId, string $action, string $resource, bool $allowActionOnResource = true, callable $additionalAssertions = null, ...$argsForCallback): ?PermissionInterface** | Remove a permission from the entity with an ID value specified in **$entityId** and return the removed permission or return null if either the entity and / or permission do not exist. |