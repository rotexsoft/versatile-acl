# simple-acl
A simple and highly flexible and customizable access control library for PHP 

Entities
----------
* **Resource:** an item on which an **action** will be denied or allowed to be performed on.
It is just a case-insensitive string as far as this package is concerned.

* **Action:** represents a task that can be performed on a **resource**. 
It is just a case-insensitive string as far as this package is concerned.

* **Permission (see [\SimpleAcl\Interfaces\PermissionInterface](src/interfaces/PermissionInterface.php)):** an object defining whether 
or not a **PermissionableEntity** is allowed or not allowed to perform an **action** on a 
particular **resource**. This object will allow additional assertions (to test if a **PermissionableEntity** 
is allowed or not allowed to perform an **action** on a particular **resource**) to be injected via a callback.

* **PermissionableEntity (see [\SimpleAcl\Interfaces\PermissionableEntityInterface](src/interfaces/PermissionableEntityInterface.php)):** 
has one or more unique **permissions** and can have one or more other unique **PermissionableEntities** related to it as parents 
and consequently inherit permissions from its parent relations. Each parent can have parents and those parents can in turn have 
parents and so on. An entity cannot become a parent of another entity that is already its parent. Each parent of a 
**PermissionableEntity** must have a unique id value. Permissions directly associated with an entity have higher 
priority than those inherited from parent related entities (you can however choose to reverse this behavior using
**\SimpleAcl\Interfaces\PermissionableEntityInterface::getAllPermissions(false)->isAllowed(...)**). 
Each entity must have a unique case-insensitive string identifier (an Entities Repository maybe introduced 
to guarantee this uniqueness). A **permission** is unique to a **PermissionableEntity** if it is the only 
permission associated with the entity having a specific **action** and **resource** value pair. In a real 
world application an entity can represent various things such as a user or a group (that users can belong to).
 
![Class Diagram](class-diagram.svg)

## Example Usage

```php
<?php
use \SimpleAcl\GenericPermission;
use \SimpleAcl\GenericPermissionableEntity;

$user_entity = new GenericPermissionableEntity('jblow');
$group_entity = new GenericPermissionableEntity('admin');

$group_entity->addPermission(new GenericPermission('browse', 'blog-post'))
             ->addPermission(new GenericPermission('read', 'blog-post'))
             ->addPermission(new GenericPermission('edit', 'blog-post'))
             // deny add action to members of the admin group on the blog-post resource
             ->addPermission(new GenericPermission('add', 'blog-post', false))
             ->addPermission(new GenericPermission('delete', 'blog-post'));

$user_entity->addParentEntity($group_entity);

var_dump(
    $user_entity->getDirectPermissions()
                ->isAllowed('browse', 'blog-post')
); // returns false

var_dump(
    $user_entity->getInheritedPermissions()
                ->isAllowed('browse', 'blog-post')
); // returns true

var_dump(
    $user_entity->getInheritedPermissions()
                ->isAllowed('add', 'blog-post')
); // returns false

$user_entity->addPermission(new GenericPermission('browse', 'blog-post'));

var_dump(
    $user_entity->getDirectPermissions()
                ->isAllowed('browse', 'blog-post')
); // returns true

//////////////////////////////////////////////////
// Test all actions and all resources permissions
//////////////////////////////////////////////////

$superuser_entity = new GenericPermissionableEntity('superuser');

// make this user a super user by allowing it to be able to 
// perform any action on any resource
$superuser_entity->addPermission(
    new GenericPermission(
        GenericPermission::getAllActionsIdentifier(), 
        GenericPermission::getAllResoucesIdentifier()
    )
);

var_dump(
    $superuser_entity->getDirectPermissions()
                 ->isAllowed('browse', 'blog-post')
); // returns true

var_dump(
    $superuser_entity->getDirectPermissions()
                 ->isAllowed('read', 'blog-post')
); // returns true

var_dump(
    $superuser_entity->getDirectPermissions()->isAllowed('edit', 'blog-post')
); // returns true

var_dump(
    $superuser_entity->getDirectPermissions()
                 ->isAllowed('add', 'blog-post')
); // returns true

var_dump(
    $superuser_entity->getDirectPermissions()
                 ->isAllowed('delete', 'blog-post')
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

var_dump(
    $user_entity->getDirectPermissions()
                ->isAllowed(
                    'edit', 
                    'blog-post',
                    $assert_callback,
                    $logged_in_user_data,
                    $blog_record_1
                )
); // true since $logged_in_user_data['id'] === $blog_record_1['author_id']

var_dump(
    $user_entity->getDirectPermissions()
                ->isAllowed(
                    'edit', 
                    'blog-post',
                    $assert_callback,
                    $logged_in_user_data,
                    $blog_record_2
                )
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

var_dump(
    $user_entity3->getDirectPermissions()
                ->isAllowed(
                    'edit', 
                    'blog-post',
                    null, // no callback injected here, 
                          // the one injected via the 
                          // constructor will be called
                    $blog_record_1
                )
); // false since $blog_record_1['year_created'] === 2019 which is a past year

var_dump(
    $user_entity3->getDirectPermissions()
                ->isAllowed(
                    'edit', 
                    'blog-post',
                    null, // no callback injected here, 
                          // the one injected via the 
                          // constructor will be called
                    $blog_record_2
                )
); // true since $blog_record_2['year_created'] === 2020 which is the 
   // current year as of the writing of this example
```
