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

#### Special Permissions 

We can create three types of special permissions:

1. A permission that allows all defined actions on all defined resources in an application. 
    * Entities with this type of permission can perform all actions on all resources and are technically super entities.
2. A permission that allows all defined actions on a specific resource in an application.
3. A permission that allows a specific action on all defined resources in an application.

The code below illustrates how to create these special permissions:

```php
<?php
// entities with this permission are ALLOWED to perform all actions on
// all resources defined in the application. Such entities in an 
// application are technically super users or super groups
$allActionsOnAllResourcesPermissionGranted = new GenericPermission(
    GenericPermission::getAllActionsIdentifier(),
    GenericPermission::getAllResourcesIdentifier(),
    true
);

// entities with this permission are ALLOWED to perform all actions on
// the specified resource (`specified-resource`) in the application
$allActionsOnSpecifiedResourcePermissionGranted = new GenericPermission(
    GenericPermission::getAllActionsIdentifier(),
    'specified-resource',
    true
);

// entities with this permission are ALLOWED to perform the specified action
// (`specified-action`) on all resources defined in the application
$specifiedActionOnAllResourcesPermissionGranted = new GenericPermission(
    'specified-action',
    GenericPermission::getAllResourcesIdentifier(),
    true
);
```

> **NOTE:** You could write your own sub-class of **\SimpleAcl\GenericPermission** and then override the **getAllActionsIdentifier()** and **getAllResourcesIdentifier()** in order to define custom identifiers for your own application if the wildcard `*` string returned by these methods by default are not suitable for your application.

## Adding Permissions to Entities

Now that we have seen how to create entity objects and permission objects. Lets see the various ways we can inject permission objects into entity objects using the objects we created in the preceding examples.

```php
<?php
// We can inject permissions into entities by
// 1. Injecting a collection of permissions as the second argument of the
//    GenericPermissionableEntity class' constructor when creating new
//    entity objects

// 2. Calling the addPermission method on an instance of the
//    GenericPermissionableEntity class and passing the permission to be 
//    added as its argument.

// 3. Calling the addPermissions method on an instance of the
//    GenericPermissionableEntity class and passing a collection of  
//    permissions to be added as its argument.


// We can grant the admin entity object (we created earlier)
// the most powerful special permission. Note that $johnDoeEntity will
// now inherit this special permission too since $adminEntity is its 
// parent.
$adminEntity->addPermission($allActionsOnAllResourcesPermissionGranted);

// Even though $johnDoeEntity inherits permissions from $adminEntity to 
// perform all actions on all resources, we can add direct permissions
// to $johnDoeEntity to override some of the inherited permissions
// 
// Deny $johnDoeEntity the permission to add comments to any news article
$johnDoeEntity->addPermission($createNewsCommentPermissionDenied);

// Let's create another entity called zacdoe
$zacDoeEntity = new GenericPermissionableEntity('zacdoe');

$createDeleteAndEditNewsCommentPermissions = 
    GenericPermission::createCollection()
        ->add($createNewsCommentPermissionGranted)
        ->add($deleteNewsCommentPermissionGranted)
        ->add($editNewsCommentPermissionGranted);

// Grant $zacDoeEntity the permissions to create, delete and edit 
// news article comments.
$zacDoeEntity
    ->addPermissions($createDeleteAndEditNewsCommentPermissions);

// Grant $kateDoeEntity the permissions to delete and edit 
// ONLY news article comments she has created.
// Also grant her the permission to add comments to any news article.
$kateDoeEntity->addPermission($createNewsCommentPermissionGranted)
              ->addPermission($deleteOnlyMyOwnNewsCommentPermissionGranted)
              ->addPermission($editOnlyMyOwnNewsCommentPermissionGranted);
```


## Checking if an Entity is allowed to perform a specifed Action on a specified Resource

At this point, let's list the expected permissions our entity objects should have:

* **$adminEntity**
    * Can perform all actions on all resources

* **$johnDoeEntity**
    * Can perform all actions on all resources (based on inherited permissions from $adminEntity) EXCEPT adding comments to any news article

* **$zacDoeEntity**
    * Can add comments to news articles
    * Can delete any comment on news articles
    * Can edit any comment on news articles

* **$kateDoeEntity**
    * Can add comments to news articles
    * Can delete only comments created by  on news articles
    * Can edit any comment on news articles

There are three ways to check if an entity object is allowed to perform a specified action on a specified resource:

1. retrieving its direct permissions collection via **getDirectPermissions()** and calling the **isAllowed(...)** method on the collection. 
    * This method of checking if an entity object is allowed to perform a specified action on a specified resource **ignores all inherited permissions**

2. retrieving its inherited permissions collection via **getInheritedPermissions(...)** and calling the **isAllowed(...)** method on the collection. 
    * This method of checking if an entity object is allowed to perform a specified action on a specified resource **ignores all direct permissions**

3. retrieving both its direct and inherited permissions in one single collection via **getAllPermissions(...)** and calling the **isAllowed(...)** method on the collection. 
    * This method of checking if an entity object is allowed to perform a specified action on a specified resource gives direct permissions a higher priority by default but it can be made to do the reverse by passing the boolean value of **false** as the first argument to **getAllPermissions(...)**

The third method is the recommended way for you to test if an entity can perform a specified action on a specified resource in your application. Only when you want to exclude **Direct Permissions** or **Inherited Permissions** should you use the first and second methods in your application.

> **NOTE:** When **isAllowed(...)** is called on any of the three permissions collections described above, the first permission in the collection applicable to the specifed Action and specified Resource will be used to determine if the entity object is allowed to perform a specified action on the specified resource. <br>If there is no applicable permission in the collection, then **isAllowed(...)** would return false meaning that the entity is not allowed to perform the specified action on the specified resource. **isAllowed(...)** would also return false if the first applicable permission object in the collection returns false when **getAllowActionOnResource()** or if the assertion callback associated with the permission object or supplied as **isAllowed(...)**'s third argument returns false. 

Now let's see some code to demonstrate the concepts above:

```php
<?php
// $adminEntity has been directly granted super permissions for all actions on
// all resources. However, since it doesn't have any parent entity, it collection
// of inherited permissions would be empty and will always return false if
// isAllowed is called on it

// $adminEntity should be able to add comments to news articles:
$adminEntity->getDirectPermissions()
            ->isAllowed('create-comment', 'news-article'); // returns true

$adminEntity->getInheritedPermissions()
            ->isAllowed('create-comment', 'news-article'); // returns false
                                                           // no inherited
                                                           // permissions
$adminEntity->getAllPermissions() // recommended way
            ->isAllowed('create-comment', 'news-article'); // returns true

// $adminEntity should be able to edit comments on news articles:
$adminEntity->getDirectPermissions()
            ->isAllowed('edit-comment', 'news-article'); // returns true

$adminEntity->getInheritedPermissions()
            ->isAllowed('edit-comment', 'news-article'); // returns false
                                                         // no inherited
                                                         // permissions
$adminEntity->getAllPermissions() // recommended way
            ->isAllowed('edit-comment', 'news-article'); // returns true

// $adminEntity should be able to delete comments on news articles:
$adminEntity->getDirectPermissions()
            ->isAllowed('delete-comment', 'news-article'); // returns true

$adminEntity->getInheritedPermissions()
            ->isAllowed('delete-comment', 'news-article'); // returns false
                                                           // no inherited
                                                           // permissions
$adminEntity->getAllPermissions() // recommended way
            ->isAllowed('delete-comment', 'news-article'); // returns true
 

```

SHOW HOW TO OVERRIDE CONSTRUCTOR TIME ASSERTION CALLBACKS WITH CALLBACK INJECTED INTO ISALLOWED



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
        GenericPermission::getAllResourcesIdentifier() // all resources
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

    * **getAllParents(): PermissionableEntitiesCollectionInterface:** returns a collection (an instance of
    **\SimpleAcl\Interfaces\PermissionableEntitiesCollectionInterface**) containing all parent entities added via 
    **addParentEntities** and **addParentEntity** and their parents and parents' parents and so on.

    * **getDirectParents(): PermissionableEntitiesCollectionInterface:** returns a collection (an instance of
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
    when **getDirectPermissions()** is invoked on each parent entity returned by **getAllParents()** on an 
    instance of **\SimpleAcl\Interfaces\PermissionableEntityInterface**.
