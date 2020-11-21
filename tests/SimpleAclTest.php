<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
declare(strict_types=1);

use SimpleAcl\SimpleAcl;

use SimpleAcl\{
    GenericPermissionableEntity, GenericPermission, 
    GenericPermissionableEntitiesCollection, GenericPermissionsCollection
};

use SimpleAcl\Interfaces\{
    PermissionInterface, PermissionableEntitiesCollectionInterface, 
    PermissionableEntityInterface, PermissionsCollectionInterface
};

/**
 * Description of SimpleAclTest
 *
 * @author rotimi
 */
class SimpleAclTest extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void { 
        
        parent::setUp();
    }
    
    public function testThatConstructorWithGoodArgsWorksAsExpected() {
        
        $anExceptionWasThrown = false;

        try {
            $sAclObj = new SimpleAcl(); // no params
            
            $sAclObj1 = new SimpleAcl(
                GenericPermissionableEntity::class
            ); // one valid arg
            
            $sAclObj2 = new SimpleAcl(
                GenericPermissionableEntity::class, 
                GenericPermission::class
            ); // two valid args
            
            $sAclObj3 = new SimpleAcl(
                GenericPermissionableEntity::class, 
                GenericPermission::class,
                GenericPermissionableEntitiesCollection::class
            ); // three valid args
            
            $sAclObj4 = new SimpleAcl(
                GenericPermissionableEntity::class, 
                GenericPermission::class,
                GenericPermissionableEntitiesCollection::class,
                GenericPermissionsCollection::class
            ); // four valid args
            
            $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $sAclObj->getAllEntities());
            $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $sAclObj1->getAllEntities());
            $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $sAclObj2->getAllEntities());
            $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $sAclObj3->getAllEntities());
            $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $sAclObj4->getAllEntities());
            
        } catch (Exception $e) {
            
            $anExceptionWasThrown = true;
        }

        $this->assertFalse($anExceptionWasThrown);
    }
    
    public function testThatConstructorWithBadFirstArgWorksAsExpected() {
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('*Bad First Parameter*');
        
        $sAclObj = new SimpleAcl('Bad First Parameter');
    }
    
    public function testThatConstructorWithBadSecondArgWorksAsExpected() {
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('*Bad Second Parameter*');
        
        $sAclObj = new SimpleAcl(
            GenericPermissionableEntity::class, 
            'Bad Second Parameter'
        );
    }
    
    public function testThatConstructorWithBadThirdArgWorksAsExpected() {
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('*Bad Third Parameter*');
        
        $sAclObj = new SimpleAcl(
            GenericPermissionableEntity::class, 
            GenericPermission::class,
            'Bad Third Parameter'
        );
    }
    
    public function testThatConstructorWithBadFourthArgWorksAsExpected() {
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('*Bad Fourth Parameter*');
        
        $sAclObj = new SimpleAcl(
            GenericPermissionableEntity::class, 
            GenericPermission::class,
            GenericPermissionableEntitiesCollection::class,
            'Bad Fourth Parameter'
        );
    }
    
    public function testThatGetAllEntitiesWorksAsExpected() {
        
        $sAclObj = new SimpleAcl();

        $sAclObj1 = new SimpleAcl(
            GenericPermissionableEntity::class, 
            GenericPermission::class,
            GenericPermissionableEntitiesCollection::class,
            GenericPermissionsCollection::class
        ); // four valid args

        $sAclObj2 = new SimpleAcl(
            GenericPermissionableEntity::class, 
            GenericPermission::class,
            CustomPermissionableEntitiesCollection::class,
            GenericPermissionsCollection::class
        ); // four valid args

        $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $sAclObj->getAllEntities());
        $this->assertInstanceOf(GenericPermissionableEntitiesCollection::class, $sAclObj->getAllEntities());

        $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $sAclObj1->getAllEntities());
        $this->assertInstanceOf(GenericPermissionableEntitiesCollection::class, $sAclObj1->getAllEntities());

        $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $sAclObj2->getAllEntities());
        $this->assertInstanceOf(CustomPermissionableEntitiesCollection::class, $sAclObj2->getAllEntities());
        $this->assertNotInstanceOf(GenericPermissionableEntitiesCollection::class, $sAclObj2->getAllEntities());
        
        $sAclObj3 = $this->createNewSimpleAclExposingNonPublicMethods(
            GenericPermissionableEntity::class, 
            GenericPermission::class,
            GenericPermissionableEntitiesCollection::class,
            GenericPermissionsCollection::class,
            true
        ); // this instance does not have an initialized entitiesCollection
        
        $this->assertNotInstanceOf(PermissionableEntitiesCollectionInterface::class, $sAclObj3->getAllEntities());
        $this->assertNull($sAclObj3->getAllEntities());
    }
    
    public function testThatAddEntityWorksAsExpected() {
        
        $sAclObj = new SimpleAcl();
        
        $this->assertCount(0, $sAclObj->getAllEntities());
        
        $self = $sAclObj->addEntity('jdoe');
        
        $this->assertCount(1, $sAclObj->getAllEntities()); // 1 entity should be in there now
        $this->assertSame($sAclObj, $self); // test fluent return val
        
        $sAclObj->addEntity('jdoe'); // try adding already present entity
        $this->assertCount(1, $sAclObj->getAllEntities()); // 1 entity should still be there
        
        $sAclObj->addEntity('janedoe'); // try adding another non-present entity
        $this->assertCount(2, $sAclObj->getAllEntities()); // 2 entities should be there now
        
        ///////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////
        $sAclObj1 = $this->createNewSimpleAclExposingNonPublicMethods(
            GenericPermissionableEntity::class, 
            GenericPermission::class,
            GenericPermissionableEntitiesCollection::class,
            GenericPermissionsCollection::class,
            true
        ); // this instance does not have an initialized entitiesCollection
        
        $this->assertNotInstanceOf(PermissionableEntitiesCollectionInterface::class, $sAclObj1->getAllEntities());
        
        // this instance now has an initialized entitiesCollection after the first addEntity call
        $sAclObj1->addEntity('jdoe');
        
        $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $sAclObj1->getAllEntities());
        $this->assertCount(1, $sAclObj1->getAllEntities());        
    }
    
    public function testThatAddParentEntityWorksAsExpected() {
        
        $sAclObj = new SimpleAcl();
        
        $this->assertCount(0, $sAclObj->getAllEntities());

        /** @noinspection PhpUnhandledExceptionInspection */
        $self = $sAclObj->addParentEntity('jdoe', 'admin');
        
        $this->assertCount(1, $sAclObj->getAllEntities()); // 1 entity added
        $this->assertSame($sAclObj, $self); // test fluent return
        $this->assertCount(1, $sAclObj->getEntity('jdoe')->getDirectParentEntities()); // confirm that only 1 parent was added
        $this->assertCount(1, $sAclObj->getEntity('jdoe')->getAllParentEntities());     // confirm that only 1 parent was added
        
        /** @var PermissionableEntityInterface $parentEntity */
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $parentEntity = $sAclObj->getEntity('jdoe')->getAllParentEntities()->getIterator()->current(); //grab the first and only parent
        
        $this->assertEquals('admin', $parentEntity->getId());

        /** @noinspection PhpUnhandledExceptionInspection */
        $parentEntity->addParentEntity($sAclObj->createEntity('super-admin'));          // add a parent to the original entity's parent
        $this->assertCount(1, $sAclObj->getEntity('jdoe')->getDirectParentEntities());  // confirm that still only 1 direct parent was added
        $this->assertCount(2, $sAclObj->getEntity('jdoe')->getAllParentEntities());     // confirm that there are 2 parents, 1 direct parent & the direct parent's parent
        
        ///////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////
        
        $sAclObj2 = $this->createNewSimpleAclExposingNonPublicMethods();
        $sAclObj2->doNothingDuringAddEntity = true;
        
        $this->expectException(RuntimeException::class);
        $this->expectErrorMessageMatches(
            '*Could not create or retrieve the entity with an ID of '
            . '`jdoe` to which the parent entity with an ID of `admin`'
            . ' is to be added*'
        );
        
        // Because of the doNothingDuringAddEntity hack above, the call 
        // below will not add the entity `jdoe` to $sAclObj2, this 
        // will trigger an exception since you can't add a parent
        // entity to an entity that does not exist in the acl object
        /** @noinspection PhpUnhandledExceptionInspection */
        $sAclObj2->addParentEntity('jdoe', 'admin');
       
    }
    
    public function testThatCreateEntityCollectionWorksAsExpected() {
        
        $sAclObj = $this->createNewSimpleAclExposingNonPublicMethods();

        $sAclObj1 = $this->createNewSimpleAclExposingNonPublicMethods(
            GenericPermissionableEntity::class, 
            GenericPermission::class,
            GenericPermissionableEntitiesCollection::class,
            GenericPermissionsCollection::class
        ); // four valid args

        $sAclObj2 = $this->createNewSimpleAclExposingNonPublicMethods(
            GenericPermissionableEntity::class, 
            GenericPermission::class,
            CustomPermissionableEntitiesCollection::class,
            GenericPermissionsCollection::class
        ); // four valid args

        $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $sAclObj->createEntityCollection());
        $this->assertInstanceOf(GenericPermissionableEntitiesCollection::class, $sAclObj->createEntityCollection());

        $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $sAclObj1->createEntityCollection());
        $this->assertInstanceOf(GenericPermissionableEntitiesCollection::class, $sAclObj1->createEntityCollection());

        $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $sAclObj2->createEntityCollection());
        $this->assertInstanceOf(CustomPermissionableEntitiesCollection::class, $sAclObj2->createEntityCollection());
        $this->assertNotInstanceOf(GenericPermissionableEntitiesCollection::class, $sAclObj2->createEntityCollection());

        $col1 = $sAclObj2->createEntityCollection();
        $col2 = $sAclObj2->createEntityCollection();

        $this->assertNotSame($col1, $col2); // instances are unique
    }
    
    public function testThatCreateEntityWorksAsExpected() {
        
        $sAclObj = $this->createNewSimpleAclExposingNonPublicMethods();

        $sAclObj1 = $this->createNewSimpleAclExposingNonPublicMethods(
            GenericPermissionableEntity::class, 
            GenericPermission::class,
            GenericPermissionableEntitiesCollection::class,
            GenericPermissionsCollection::class
        ); // four valid args


        $this->assertInstanceOf(PermissionableEntityInterface::class, $sAclObj->createEntity('bob'));
        $this->assertInstanceOf(GenericPermissionableEntity::class, $sAclObj->createEntity('brittany'));

        $this->assertInstanceOf(PermissionableEntityInterface::class, $sAclObj1->createEntity('jack'));
        $this->assertInstanceOf(GenericPermissionableEntity::class, $sAclObj1->createEntity('jill'));


        $entity1 = $sAclObj->createEntity('Alex');
        $entity2 = $sAclObj1->createEntity('Alicia');

        $this->assertNotSame($entity1, $entity2); // instances are unique
    }
    
    public function testThatCreatePermissionCollectionWorksAsExpected() {
        
        $sAclObj = $this->createNewSimpleAclExposingNonPublicMethods();

        $sAclObj1 = $this->createNewSimpleAclExposingNonPublicMethods(
            GenericPermissionableEntity::class, 
            GenericPermission::class,
            GenericPermissionableEntitiesCollection::class,
            GenericPermissionsCollection::class
        ); // four valid args

        $sAclObj2 = $this->createNewSimpleAclExposingNonPublicMethods(
            GenericPermissionableEntity::class, 
            GenericPermission::class,
            GenericPermissionableEntitiesCollection::class,
            CustomPermissionsCollection::class
        ); // four valid args

        $this->assertInstanceOf(PermissionsCollectionInterface::class, $sAclObj->createPermissionCollection());
        $this->assertInstanceOf(GenericPermissionsCollection::class, $sAclObj->createPermissionCollection());

        $this->assertInstanceOf(PermissionsCollectionInterface::class, $sAclObj1->createPermissionCollection());
        $this->assertInstanceOf(GenericPermissionsCollection::class, $sAclObj1->createPermissionCollection());

        $this->assertInstanceOf(PermissionsCollectionInterface::class, $sAclObj2->createPermissionCollection());
        $this->assertInstanceOf(CustomPermissionsCollection::class, $sAclObj2->createPermissionCollection());
        $this->assertNotInstanceOf(GenericPermissionsCollection::class, $sAclObj2->createPermissionCollection());

        $col1 = $sAclObj2->createPermissionCollection();
        $col2 = $sAclObj2->createPermissionCollection();

        $this->assertNotSame($col1, $col2); // instances are unique
    }
    
    public function testThatCreatePermissionWorksAsExpected() {
        
        $sAclObj = $this->createNewSimpleAclExposingNonPublicMethods();

        $sAclObj1 = $this->createNewSimpleAclExposingNonPublicMethods(
            GenericPermissionableEntity::class, 
            GenericPermission::class,
            GenericPermissionableEntitiesCollection::class,
            GenericPermissionsCollection::class
        ); // four valid args

        $this->assertInstanceOf(
            PermissionInterface::class, 
            $sAclObj->createPermission(
                'add', 
                'post', 
                true, 
                null
            )
        );
        $this->assertInstanceOf(
            GenericPermission::class, 
            $sAclObj->createPermission(
                'edit', 
                'post', 
                true, 
                null
            )
        );

        $this->assertInstanceOf(
            PermissionInterface::class, 
            $sAclObj1->createPermission(
                'delete', 
                'post', 
                true, 
                null
            )
        );
        $this->assertInstanceOf(
            GenericPermission::class, 
            $sAclObj1->createPermission(
                'read', 
                'post', 
                true, 
                null
            )
        );

        $perm1 = $sAclObj->createPermission(
                    'add', 
                    'post', 
                    true, 
                    null
                );
        $perm2 = $sAclObj1->createPermission(
                    'edit', 
                    'post', 
                    true, 
                    null
                );

        $this->assertNotSame($perm1, $perm2); // instances are unique
    }
    
    public function testThatAddPermissionWorksAsExpected() {
        
        $sAclObj = new SimpleAcl();
        
        $this->assertCount(0, $sAclObj->getAllEntities());
        
        $sAclObj->addEntity('first-entity');
        $this->assertCount(1, $sAclObj->getAllEntities());
        
        $flagVal = true;
        $sAclObj->addPermission('entity-a', 'edit', 'blog-post', true, function(bool $flag){ return $flag; }, $flagVal);
        
        // test that non-existent entity 'entity-a' was automatically created by addPermission 
        $this->assertCount(2, $sAclObj->getAllEntities());
        $this->assertInstanceOf(PermissionableEntityInterface::class, $sAclObj->getEntity('entity-a'));
        $this->assertInstanceOf(PermissionInterface::class, $sAclObj->getEntity('entity-a')->getDirectPermissions()->findOne('edit', 'blog-post'));
        $this->assertInstanceOf(PermissionInterface::class, $sAclObj->getEntity('entity-a')->getAllPermissions()->findOne('edit', 'blog-post'));
        $this->assertNull($sAclObj->getEntity('entity-a')->getInheritedPermissions()->findOne('edit', 'blog-post'));
        
        
        // test adding permission to already existent entity
        $sAclObj->addPermission('first-entity', 'add', 'blog-post', true, function(bool $flag){ return $flag; }, $flagVal);
        $this->assertInstanceOf(PermissionInterface::class, $sAclObj->getEntity('first-entity')->getDirectPermissions()->findOne('add', 'blog-post'));
        $this->assertInstanceOf(PermissionInterface::class, $sAclObj->getEntity('first-entity')->getAllPermissions()->findOne('add', 'blog-post'));
        $this->assertNull($sAclObj->getEntity('first-entity')->getInheritedPermissions()->findOne('add', 'blog-post'));
        
        ////////////////////////////////////////////////////////////////////////
        // test the weird impossible Runtime exception scenario
        ////////////////////////////////////////////////////////////////////////
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches(
            '*Could not create or retrieve the entity with an ID of'
            . ' `third-entity` to which the following permission is to be added*'
        );
        $this->expectExceptionMessageMatches('*add*');
        $this->expectExceptionMessageMatches('*blog-post*');
        $this->expectExceptionMessageMatches('*true*');
        $this->expectExceptionMessageMatches('*Closure*');
        
        $sAclObjWeird = $this->createNewSimpleAclExposingNonPublicMethods();
        $sAclObjWeird->doNothingDuringAddEntity = true;
        
        // call below will throw exception because the addEntity method in 
        // $sAclObjWeird has been temporarily turned into a method that
        // just reurns $this and does not actually add the entity.
        $sAclObjWeird->addPermission('third-entity', 'add', 'blog-post', true, function(bool $flag){ return $flag; }, $flagVal);
    }
    
    public function testThatGetEntityWorksAsExpected() {
        
        $sAclObj = new SimpleAcl();
        
        $this->assertNull($sAclObj->getEntity('non-existent'));
        
        $sAclObj->addEntity('first-entity');
        
        $this->assertInstanceOf(PermissionableEntityInterface::class, $sAclObj->getEntity('first-entity'));
        $this->assertEquals('first-entity', $sAclObj->getEntity('first-entity')->getId());
    }
    
    public function testThatIsAllowedWorksAsExpected() {
        
        $sAclObj = new SimpleAcl();
        
        $this->assertCount(0, $sAclObj->getAllEntities());
                
        $boolValForCallback = true;
        $callbackThatAcceptsBool = function(bool $flag){ return $flag; };
        $falsyCallBack = function(){ return false; };
        
        $sAclObj->addPermission('jdoe', 'read', 'blog-post', true, $callbackThatAcceptsBool, $boolValForCallback);
        $this->assertCount(1, $sAclObj->getAllEntities());
        
        $this->assertTrue($sAclObj->isAllowed('jdoe', 'read', 'blog-post'));
        $this->assertTrue($sAclObj->isAllowed('', 'read', 'blog-post')); // searches all entities
        
        // falsy param added for the callback registered when
        // addPermission was called above
        $this->assertFalse($sAclObj->isAllowed('jdoe', 'read', 'blog-post', null, false));
        $this->assertFalse($sAclObj->isAllowed('jdoe', 'read', 'blog-post', $falsyCallBack));
        
        // time for some inherited permissions tests
        
        // non existent permission at this point
        $this->assertFalse($sAclObj->isAllowed('jdoe', 'add', 'blog-post'));
        
        $sAclObj->addParentEntity('jdoe', 'admin'); // add parent entity
        
        // add permission to be checked on child entity 'jdoe' to the parent entity 'admin'
        $sAclObj->getEntity('jdoe')
                ->getAllParentEntities()
                ->find('admin')
                ->addPermission($sAclObj->createPermission('add', 'blog-post', true));
        
        // now the permission check below should pass because 'jdoe' will
        // inherit the permission from its parent 'admin'
        $this->assertTrue($sAclObj->isAllowed('jdoe', 'add', 'blog-post'));
        $this->assertTrue($sAclObj->isAllowed('', 'add', 'blog-post'));
        
        // let's check for another non-existent permission
        $this->assertFalse($sAclObj->isAllowed('jdoe', 'edit', 'blog-post'));
        
        // add a parent ('super-admin') to the parent ('admin') of 'jdoe' and grant this 
        // new parent the new permission
        $sAclObj->getEntity('jdoe')
                ->getAllParentEntities()
                ->find('admin')
                ->addParentEntity($sAclObj->createEntity('super-admin'));
        
        $sAclObj->getEntity('jdoe')
                ->getAllParentEntities()
                ->find('admin')
                ->getAllParentEntities()
                ->find('super-admin')
                ->addPermission($sAclObj->createPermission('edit', 'blog-post', true));
        
        // now the permission check below should pass because 'jdoe' will
        // inherit the permission from its parent's ('admin') parent ('super-admin')
        $this->assertTrue($sAclObj->isAllowed('jdoe', 'edit', 'blog-post'));
        $this->assertTrue($sAclObj->isAllowed('', 'edit', 'blog-post'));
        
        // let's add another entity to our acl object with the same permission
        // as the last but make it false instead of true. 'jdoe's inherited
        // permission will override the permission for this new entity because
        // isAllowed will check 'jdoe's perms before cecking the new entity's
        // perm
        $sAclObj->addPermission('new-entity', 'edit', 'blog-post', false); // add the falsy perm and 'new-entity' together
        
        $this->assertTrue($sAclObj->isAllowed('jdoe', 'edit', 'blog-post'));
        $this->assertTrue($sAclObj->isAllowed('', 'edit', 'blog-post'));
        $this->assertFalse($sAclObj->isAllowed('new-entity', 'edit', 'blog-post'));
        
        // now remove 'jdoe' from the acl
        $sAclObj->getAllEntities()->remove($sAclObj->getEntity('jdoe'));
        
        $this->assertFalse($sAclObj->isAllowed('', 'edit', 'blog-post'));
        $this->assertFalse($sAclObj->isAllowed('new-entity', 'edit', 'blog-post'));
    }
    
    public function testThatRemoveParentEntityWorksAsExpected() {
        
        $sAclObj = new SimpleAcl();
        
        $this->assertNull($sAclObj->removeParentEntity('non-existent-entity', 'non-existent-parent-entity'));
        
        $sAclObj->addParentEntity('son', 'father');
        $sAclObj->addParentEntity('son', 'mother');
        
        $this->assertNull($sAclObj->removeParentEntity('non-existent-entity', 'non-existent-parent-entity'));
        
        $this->assertCount(2, $sAclObj->getEntity('son')->getAllParentEntities());
        $this->assertCount(2, $sAclObj->getEntity('son')->getDirectParentEntities());
        
        $father = $sAclObj->removeParentEntity('son', 'father');
        $this->assertCount(1, $sAclObj->getEntity('son')->getAllParentEntities());
        $this->assertInstanceOf(PermissionableEntityInterface::class, $father);
        $this->assertEquals('father', $father->getId());
        
        $mother = $sAclObj->removeParentEntity('son', 'mother');
        $this->assertCount(0, $sAclObj->getEntity('son')->getAllParentEntities());
        $this->assertInstanceOf(PermissionableEntityInterface::class, $mother);
        $this->assertEquals('mother', $mother->getId());
        
        // NOTE: since permissions are mainly identified by the (action, resource) pairing
        // I am not bothering to add tests to see if the last three parameters to 
        // make any significant different when removing permissions. I may have to
        // revisit in a future release if things change.
    }
    
    public function testThatRemovePermissionEntityWorksAsExpected() {
        
        $sAclObj = new SimpleAcl();
        
        $this->assertNull($sAclObj->removePermission('non-existent-entity', 'non-existent-action', 'non-existent-resource'));
        
        $flagVal = true;
        $sAclObj->addPermission('entity-a', 'add', 'blog-post', true, function(bool $flag){ return $flag; }, $flagVal);
        $sAclObj->addPermission('entity-a', 'edit', 'blog-post', true, function(bool $flag){ return $flag; }, $flagVal);
        
        $this->assertNull($sAclObj->removePermission('non-existent-entity', 'non-existent-action', 'non-existent-resource'));
        $this->assertCount(2, $sAclObj->getEntity('entity-a')->getAllPermissions());
        $this->assertCount(2, $sAclObj->getEntity('entity-a')->getDirectPermissions());
        $this->assertCount(0, $sAclObj->getEntity('entity-a')->getInheritedPermissions());
        
        $perm1 = $sAclObj->removePermission('entity-a', 'add', 'blog-post');
        $this->assertInstanceOf(PermissionInterface::class, $perm1);
        $this->assertEquals('add', $perm1->getAction());
        $this->assertEquals('blog-post', $perm1->getResource());
        $this->assertCount(1, $sAclObj->getEntity('entity-a')->getAllPermissions());
        $this->assertCount(1, $sAclObj->getEntity('entity-a')->getDirectPermissions());
        $this->assertCount(0, $sAclObj->getEntity('entity-a')->getInheritedPermissions());
        
        $perm2 = $sAclObj->removePermission('entity-a', 'edit', 'blog-post');
        $this->assertInstanceOf(PermissionInterface::class, $perm2);
        $this->assertEquals('edit', $perm2->getAction());
        $this->assertEquals('blog-post', $perm2->getResource());
        $this->assertCount(0, $sAclObj->getEntity('entity-a')->getAllPermissions());
        $this->assertCount(0, $sAclObj->getEntity('entity-a')->getDirectPermissions());
        $this->assertCount(0, $sAclObj->getEntity('entity-a')->getInheritedPermissions());
    }
    
    ////////////////////////////////////////////////////////////////////////////
    ////////////////// Begin Testing Non-Public Methods ////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    
    public function testThatThrowInvalidArgExceptionDueToWrongClassNameWorksAsExpected() {
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('*'.get_class($this).'*');
        $this->expectExceptionMessageMatches('*'.__FUNCTION__.'*');
        $this->expectExceptionMessageMatches('*wrongClassName*');
        $this->expectExceptionMessageMatches('*'.PermissionInterface::class.'*');
        $this->expectExceptionMessageMatches('*Tenth*');
        
        $sAclObj = $this->createNewSimpleAclExposingNonPublicMethods(
            GenericPermissionableEntity::class, 
            GenericPermission::class,
            GenericPermissionableEntitiesCollection::class,
            GenericPermissionsCollection::class
        );
        
        $sAclObj->throwInvalidArgExceptionDueToWrongClassName(
            get_class($this), __FUNCTION__, 'wrongClassName', PermissionInterface::class, 'Tenth'
        );
    }
    
    ////////////////////////////////////////////////////////////////////////////
    ///////////////// Finished Testing Non-Public Methods //////////////////////
    ////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////// Helper Methods //////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////

    /**
     *
     * @param string $permissionableEntityInterfaceClassName first parameter required by SimpleAcl::__construct(....)
     * @param string $permissionInterfaceClassName second parameter required by SimpleAcl::__construct(....)
     * @param string $permissionableEntitiesCollectionInterfaceClassName third parameter required by SimpleAcl::__construct(....)
     * @param string $permissionsCollectionInterfaceClassName fourth parameter required by SimpleAcl::__construct(....)
     *
     * @param bool $dontInitializeEntitiesCollectionInConstructor
     *
     * @return SimpleAcl an anonymous class instance that extends SimpleAcl but changes all non-public methods to public
     */
    protected function createNewSimpleAclExposingNonPublicMethods(
        string $permissionableEntityInterfaceClassName = GenericPermissionableEntity::class,
        string $permissionInterfaceClassName = GenericPermission::class,
        string $permissionableEntitiesCollectionInterfaceClassName = GenericPermissionableEntitiesCollection::class,
        string $permissionsCollectionInterfaceClassName = GenericPermissionsCollection::class,
        bool $dontInitializeEntitiesCollectionInConstructor = false
    ) {
        return new class(
            $permissionableEntityInterfaceClassName, 
            $permissionInterfaceClassName, 
            $permissionableEntitiesCollectionInterfaceClassName, 
            $permissionsCollectionInterfaceClassName,
            $dontInitializeEntitiesCollectionInConstructor
        ) extends SimpleAcl {
            
            public $doNothingDuringAddEntity = false;
            
            public function __construct(
                string $permissionableEntityInterfaceClassName = GenericPermissionableEntity::class, 
                string $permissionInterfaceClassName = GenericPermission::class, 
                string $permissionableEntitiesCollectionInterfaceClassName = GenericPermissionableEntitiesCollection::class, 
                string $permissionsCollectionInterfaceClassName = GenericPermissionsCollection::class, 
                bool $dontInitializeEntitiesCollectionInConstructor = false
            ) {
                parent::__construct($permissionableEntityInterfaceClassName, $permissionInterfaceClassName, $permissionableEntitiesCollectionInterfaceClassName, $permissionsCollectionInterfaceClassName);
                
                $this->entitiesCollection = 
                    $dontInitializeEntitiesCollectionInConstructor ? null : $this->createEntityCollection();
            }
            
            public function addEntity(string $entityId): SimpleAcl {
                
                if(!$this->doNothingDuringAddEntity) {
                    
                    return parent::addEntity($entityId);
                }
                
                return $this;
            }

            /** @noinspection SpellCheckingInspection */
            public function throwInvalidArgExceptionDueToWrongClassName(string $class, string $function, string $wrongClassName, string $expectedIntefaceName, string $positionthParameter) {
                parent::throwInvalidArgExceptionDueToWrongClassName($class, $function, $wrongClassName, $expectedIntefaceName, $positionthParameter);
            }
        };
    }
}
