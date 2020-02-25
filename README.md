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

## Example Usage
In your applications, you will be mostly working with instances of **[\SimpleAcl\GenericPermissionableEntity](src/GenericPermissionableEntity.php)**
and **[\SimpleAcl\GenericPermission](src/GenericPermission.php)**. Both classes have a static **createCollection()** method that can be used to 
create the appropriate collection object to house one or more instances of each class respectively.

### Creating Entities

You can create an entity with or without permissions and parent entities. Let's create an entity object that represents an **admin** group in a php application.

```php
<?php
use \SimpleAcl\GenericPermissionableEntity;

// an entity object representing an admin group in an application
$adminEntity = new GenericPermissionableEntity('admin'); 
```

Typically in most applications, there are users that login and perform tasks in the application. These users can sometimes belong to one or more groups which allows them to inherit permissions from those groups. Let's create a sample user with the userid of **johndoe** and let's make that user a member of the admin group.

```php
<?php
use \SimpleAcl\GenericPermissionableEntity;

// an entity object representing a user with the userid of johndoe
$johnDoeEntity = new GenericPermissionableEntity('johndoe');

// we now add this user to the admin group by adding the admin entity object as this user's parent entity
$johnDoeEntity->addParentEntity($adminEntity);


// Alternatively, we could create the user entity and add the user to the admin group in one step via the code below
$johnDoeEntity = new GenericPermissionableEntity(
    'johndoe', 
    null,  // <== we could inject a collection of permissions for johndoe here
    GenericPermissionableEntity::createCollection()->add($adminEntity)
);

```

### Creating Permissions

We have created entities in the sample code above. Now let's create some permissions for those entities. Let's assume that our application is a news reading application. Each news article would be a good example of a **resource** in this application and operations like logged in users:
* adding comments to each new article, 
* editing their comments on each news article
* and deleting their comments on each news articles 

are the **actions** we want to grant or deny via permissions objects. Here's how we would do that in code:

```php
<?php
use \SimpleAcl\GenericPermission;

// entities with this permission are ALLOWED to create comments on news articles
$createNewsCommentPermissionGranted = new GenericPermission(
    'create-comment', 'news-article', true
);

// entities with this permission are NOT ALLOWED to create comments on news articles
$createNewsCommentPermissionDenied = new GenericPermission(
    'create-comment', 'news-article', false
);

// entities with this permission are ALLOWED to edit comments on news articles
$editNewsCommentPermissionGranted = new GenericPermission(
    'edit-comment', 'news-article', true
);

// entities with this permission are ALLOWED to delete comments on news articles
$deleteNewsCommentPermissionGranted = new GenericPermission(
    'delete-comment', 'news-article', true
);

// We could further spice things up by creating two variants of the
// $editNewsCommentPermissionGranted and $deleteNewsCommentPermissionGranted that allow 
// the entities (users or groups) with these variant permissions to only be able  
// to edit or delete only comments that they have created and not comments created 
// by other entities (users or groups) in the application. This will be achieved 
// via injecting a callback into these permissions
$isOwnArticleAsserter = function(string $loggedInUsersId, array $newsArticleComment) {
    
    return $loggedInUsersId === $newsArticleComment['creators_userid'];
}; // return true is the logged in user is the creator of $newsArticleComment

// entities with this permission are ALLOWED to edit ONLY comments they created on news articles
$editOnlyMyOwnNewsCommentPermissionGranted = new GenericPermission(
    'edit-own-comment', 'news-article', true, $isOwnArticleAsserter
);

// entities with this permission are ALLOWED to delete ONLY comments they created on news articles
$deleteOnlyMyOwnNewsCommentPermissionGranted = new GenericPermission(
    'delete-own-comment', 'news-article', true, $isOwnArticleAsserter
);
```




We will be using a blog application that has a users table containing information
about registered blog users (the users in this table are also authors in the application), 
a posts table and a comments table. Below is the schema for the sample application:

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

> **NOTE:** the permissions associated with the **comments-owners** and **posts-owners** will require an assertion callback that further checks that members of the group can only permorm actions on the comments or posts they own (not comments and posts owned by other users).

Below is a list of userids of users in the application

* frankwhite
* ginawhite
* johndoe
* janedoe
* jackbauer
* jillbauer

Below are the group membership definitions:

| Group               | User      |
|---------------------|-----------|
| admin               | frankwhite|
| comments-moderators | ginawhite |
| comments-moderators | johndoe   |
| posts-moderators    | janedoe   |
| comments-owners     | all       |
| posts-owners        | all       |

First let's create the permissions for each group:

```php
<?php

```




```php
<?php
use \SimpleAcl\GenericPermission;
use \SimpleAcl\GenericPermissionableEntity;

function dump($var,$line='') {
    
    echo $line ? "Line {$line}: " : '';
    var_export($var);
    echo PHP_EOL;
}

$user_entity = new GenericPermissionableEntity('jblow');
$group_entity = new GenericPermissionableEntity('admin');

$group_entity->addPermission(new GenericPermission('browse', 'blog-post')) // allow
             ->addPermission(new GenericPermission('read', 'blog-post')) // allow
             ->addPermission(new GenericPermission('edit', 'blog-post')) // allow
             // deny add action to members of the admin group on the blog-post resource
             ->addPermission(new GenericPermission('add', 'blog-post', false)) // deny
             ->addPermission(new GenericPermission('delete', 'blog-post'));// allow

$user_entity->addParentEntity($group_entity);

dump(
    $user_entity->getDirectPermissions()
                ->isAllowed('browse', 'blog-post'), __LINE__
); // returns false because we haven't added any permission for 
   // 'browse', 'blog-post' to $user_entity

dump(
    $user_entity->getInheritedPermissions()
                ->isAllowed('browse', 'blog-post'), __LINE__
); // returns true because $user_entity inherits the 'browse', 'blog-post' permission
   // added to its parent ($group_entity) above

dump(
    $user_entity->getAllPermissions()
                ->isAllowed('browse', 'blog-post'), __LINE__
); // returns true because $user_entity->getAllPermissions() includes permissions 
   // returned by both $user_entity->getDirectPermissions() and $user_entity->getInheritedPermissions()

dump(
    $user_entity->getInheritedPermissions()
                ->isAllowed('add', 'blog-post'), __LINE__
); // returns false because we explicitly added a permission to $group_entity 
   // to deny the 'add' action on a 'blog-post' resource above

dump(
    $user_entity->getAllPermissions()
                ->isAllowed('add', 'blog-post'), __LINE__
); // returns false because we explicitly added a permission to $group_entity 
   // to deny the 'add' action on a 'blog-post' resource above and we have also 
   // not directly added a permission to $user_entity allowing the 'add' action 
   // on a 'blog-post' resource

///////////////////////////////////////////////////////////////////////////////
// let's directly add a permission to $user_entity allowing the 'add' action
// on a 'blog-post' resource to demonstrate how directly added permissions can
// override inherited permissions
///////////////////////////////////////////////////////////////////////////////

// add permission that will override the $group_entity equivalent 
$user_entity->addPermission(new GenericPermission('add', 'blog-post'));

dump(
    $user_entity->getInheritedPermissions()
                ->isAllowed('add', 'blog-post'), __LINE__
); // still returns false because we explicitly added a permission to $group_entity 
   // to deny the 'add' action on a 'blog-post' resource above

dump(
    $user_entity->getAllPermissions(true) // default makes directly added permissions
                                          // have higher priority over inherited ones
                ->isAllowed('add', 'blog-post'), __LINE__
); // now returns true because we directly added a permission to $user_entity 
   // allowing the 'add' action on a 'blog-post' resource which overrides the 
   // one we explicitly added a permission to $group_entity to deny the 'add' 
   // action on a 'blog-post' resource

dump(
    $user_entity->getAllPermissions(false) // makes inherited permissions have 
                                           // higher priority over directly added ones
                ->isAllowed('add', 'blog-post'), __LINE__
); // now returns false because we indicated that getAllPermissions should
   // give inherited permissions a higher priority over directly added ones. 

$user_entity->addPermission(new GenericPermission('browse', 'blog-post'));

dump(
    $user_entity->getDirectPermissions()
                ->isAllowed('browse', 'blog-post'), __LINE__
); // returns true becuse we just added a permission above to $user_entity
   // allow performing the 'browse' action on a 'blog-post' resource

//////////////////////////////////////////////////
// Test all actions and all resources permissions
//////////////////////////////////////////////////

$superuser_entity = new GenericPermissionableEntity('superuser');

// make this user a super user by allowing it to be able to 
// perform any action on any resource
$superuser_entity->addPermission(
    new GenericPermission(
        GenericPermission::getAllActionsIdentifier(), // all actions
        GenericPermission::getAllResoucesIdentifier() // all resources
    )
);

dump(
    $superuser_entity->getDirectPermissions()
                     ->isAllowed('browse', 'blog-post'), __LINE__
); // returns true

dump(
    $superuser_entity->getInheritedPermissions()
                     ->isAllowed('browse', 'blog-post'), __LINE__
); // returns false because $superuser_entity has no parent and even if it had
   // parents, the all action and all resorces permission has to have been added 
   // to one of its parents or their parents in order for it to return true

dump(
    $superuser_entity->getAllPermissions()
                     ->isAllowed('browse', 'blog-post'), __LINE__
); // returns true

dump(
    $superuser_entity->getDirectPermissions()
                     ->isAllowed('read', 'blog-post'), __LINE__
); // returns true

dump(
    $superuser_entity->getAllPermissions()
                     ->isAllowed('read', 'blog-post'), __LINE__
); // returns true

dump(
    $superuser_entity->getDirectPermissions()
                     ->isAllowed('edit', 'blog-post'), __LINE__
); // returns true

dump(
    $superuser_entity->getAllPermissions()
                     ->isAllowed('edit', 'blog-post'), __LINE__
); // returns true

dump(
    $superuser_entity->getDirectPermissions()
                     ->isAllowed('add', 'blog-post'), __LINE__
); // returns true

dump(
    $superuser_entity->getAllPermissions()
                     ->isAllowed('add', 'blog-post'), __LINE__
); // returns true

dump(
    $superuser_entity->getDirectPermissions()
                     ->isAllowed('delete', 'blog-post'), __LINE__
); // returns true

dump(
    $superuser_entity->getAllPermissions()
                     ->isAllowed('delete', 'blog-post'), __LINE__
); // returns true

///////////////////////////////////////////////////////
// You can make things even more interesting by using
// callbacks for permission checks. The callback must 
// return a boolean: true means action is allowed on 
// the resource and false if action is not allowed.
///////////////////////////////////////////////////////

// For Example you can use the assertion callback to 
// enforce that a blog-post can only be edited by the 
// author if the author is the currently logged in user

// let's grant the user the permission to edit any blog-post
$user_entity->addPermission(new GenericPermission('edit', 'blog-post'));

// assume we have a logged in user data
$logged_in_user_data = ['id'=> 'jblow'];

// assume we also have some blog records we want to edit
$blog_record_1 = [
    'title' => 'Blah',
    'body' => 'Some post content',
    'author_id' => 'jblow',
    'year_created' => '2019'
];
$blog_record_2 = [
    'title' => 'Bloom',
    'body' => 'Some post content 2',
    'author_id' => 'jdoe',
    'year_created' => '2020'
];

$assert_callback = function(array $user_data, array $blog_record){
    return isset($user_data['id'])
        && isset($blog_record['author_id'])
        && $user_data['id'] === $blog_record['author_id'];
};

dump(
    $user_entity->getAllPermissions()
                ->isAllowed(
                    'edit', 
                    'blog-post',
                    $assert_callback,
                    $logged_in_user_data,
                    $blog_record_1
                ), __LINE__
); // true since $logged_in_user_data['id'] === $blog_record_1['author_id']

dump(
    $user_entity->getAllPermissions()
                ->isAllowed(
                    'edit', 
                    'blog-post',
                    $assert_callback,
                    $logged_in_user_data,
                    $blog_record_2
                ), __LINE__
); // false since $logged_in_user_data['id'] !== $blog_record_2['author_id']

// Note that you can register a default callback and default arguments 
// when you create a new instance of GenericPermission so that 
// you don't have to keep invoking isAllowed 
// with the callback and its arguments if the arguments do
// not change between calls. IF there's a default callback
// whose arguments change from call to call, you can pass
// null as the third argument to isAllowed
// and then pass the arguments needed for each call as the
// the fourth and so on arguments to isAllowed

// let's create another callback that checks if the 
// 'year_created' field of a blog post has the same
// value as the current year in order to allow editing
// of only blog posts created in the current year.
$assert_current_year_edit_callback = function(array $blog_record){
    return isset($blog_record['year_created'])
        && $blog_record['year_created'] === date('Y');
};

$user_entity3 = new GenericPermissionableEntity('kblow');
$user_entity3->addPermission(
    new GenericPermission(
        'edit', 
        'blog-post',
        true, 
        $assert_current_year_edit_callback // inject the callback here
    )
);

dump(
    $user_entity3->getAllPermissions()
                ->isAllowed(
                    'edit', 
                    'blog-post',
                    null, // no callback injected here, 
                          // the one injected via the 
                          // constructor will be called
                    $blog_record_1
                ), __LINE__
); // false since $blog_record_1['year_created'] === 2019 which is a past year

dump(
    $user_entity3->getAllPermissions()
                ->isAllowed(
                    'edit', 
                    'blog-post',
                    null, // no callback injected here, 
                          // the one injected via the 
                          // constructor will be called
                    $blog_record_2
                ), __LINE__
); // true since $blog_record_2['year_created'] === 2020 which is the 
   // current year as of the writing of this example
```

## API
* Below are the most important methods in **[\SimpleAcl\Interfaces\PermissionableEntityInterface](src/interfaces/PermissionableEntityInterface.php)** which are implemented by **[\SimpleAcl\GenericPermissionableEntity](src/GenericPermissionableEntity.php):**
    * **addParentEntity(PermissionableEntityInterface $entity): $this:** used for adding an instance **X** of 
    **\SimpleAcl\Interfaces\PermissionableEntityInterface** to another instance **Y** of 
    **\SimpleAcl\Interfaces\PermissionableEntityInterface** as a parent entity of **Y**.

    * **addParentEntities(PermissionableEntitiesCollectionInterface $entities): $this :** used for adding a collection of one or more instances of 
    **\SimpleAcl\Interfaces\PermissionableEntityInterface** to another instance **X** of **\SimpleAcl\Interfaces\PermissionableEntityInterface** as 
    parent entities of **X**.

    * **addPermission(PermissionInterface $perm): $this:** used for adding a Permission (an instance of 
    **\SimpleAcl\Interfaces\PermissionInterface**) to an instance of 
    **\SimpleAcl\Interfaces\PermissionableEntityInterface**.

    * **addPermissions(PermissionsCollectionInterface $perms): $this:** used for adding a collection of Permissions 
    (an instance of **\SimpleAcl\Interfaces\PermissionsCollectionInterface**) to an instance of 
    **\SimpleAcl\Interfaces\PermissionableEntityInterface**.

    * **getAllParentEntities(): PermissionableEntitiesCollectionInterface:** returns a collection (an instance of
    **\SimpleAcl\Interfaces\PermissionableEntitiesCollectionInterface**) containing all parent entities added via 
    **addParentEntities** and **addParentEntity** and their parents and parents' parents and so on.

    * **getDirectParentEntities(): PermissionableEntitiesCollectionInterface:** returns a collection (an instance of
    **\SimpleAcl\Interfaces\PermissionableEntitiesCollectionInterface**) containing all parent entities added via 
    **addParentEntities** and **addParentEntity** to an instance of **\SimpleAcl\Interfaces\PermissionableEntityInterface**. 
    The returned collection does not include the parents of the direct parents and so on. 

    * **getAllPermissions(bool $directPermissionsFirst=true): PermissionsCollectionInterface:** returns a collection
    (an instance of **\SimpleAcl\Interfaces\PermissionsCollectionInterface**) containing all permissions returned by
    invoking **getDirectPermissions()** and **getInheritedPermissions()** on an instance of **\SimpleAcl\Interfaces\PermissionableEntityInterface**.

    * **getDirectPermissions(): PermissionsCollectionInterface:** returns a collection 
    (an instance of **\SimpleAcl\Interfaces\PermissionsCollectionInterface**) containing all permissions added to 
    an instance of **\SimpleAcl\Interfaces\PermissionableEntityInterface** via **addPermission(PermissionInterface $perm)**
    and **addPermissions(PermissionsCollectionInterface $perms)**.

    * **getInheritedPermissions(): PermissionsCollectionInterface:** returns a collection
    (an instance of **\SimpleAcl\Interfaces\PermissionsCollectionInterface**) containing all permissions returned 
    when **getDirectPermissions()** is invoked on each parent entity returned by **getAllParentEntities()** on an 
    instance of **\SimpleAcl\Interfaces\PermissionableEntityInterface**.