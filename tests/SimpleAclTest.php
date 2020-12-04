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
        $this->assertCount(1, $sAclObj->getEntity('jdoe')->getDirectParents()); // confirm that only 1 parent was added
        $this->assertCount(1, $sAclObj->getEntity('jdoe')->getAllParents());     // confirm that only 1 parent was added
        
        /** @var PermissionableEntityInterface $parentEntity */
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $parentEntity = $sAclObj->getEntity('jdoe')->getAllParents()->getIterator()->current(); //grab the first and only parent
        
        $this->assertEquals('admin', $parentEntity->getId());

        /** @noinspection PhpUnhandledExceptionInspection */
        $parentEntity->addParent($sAclObj->createEntity('super-admin'));          // add a parent to the original entity's parent
        $this->assertCount(1, $sAclObj->getEntity('jdoe')->getDirectParents());  // confirm that still only 1 direct parent was added
        $this->assertCount(2, $sAclObj->getEntity('jdoe')->getAllParents());     // confirm that there are 2 parents, 1 direct parent & the direct parent's parent
        
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
    
    public function testThatClearAuditTrailWorksAsExpected() {
        
        $sAclObj = new SimpleAcl();

        $sAclObj->enableAuditTrail(true)->addEntity('bob');
        
        $this->assertTrue( (strlen($sAclObj->getAuditTrail()) > 0) );
        $this->assertSame($sAclObj, $sAclObj->clearAuditTrail());
        $this->assertTrue( (strlen($sAclObj->getAuditTrail()) === 0) );
    }
    
    public function testThatEnableAuditTrailWorksAsExpected() {
        
        $sAclObj = new SimpleAcl();

        $sAclObj->enableAuditTrail(true)->addEntity('bob');
        
        $this->assertTrue( (strlen($sAclObj->getAuditTrail()) > 0) );
        $this->assertSame($sAclObj, $sAclObj->clearAuditTrail());
        $this->assertTrue( (strlen($sAclObj->getAuditTrail()) === 0) );
        
        $sAclObj->enableAuditTrail(false)->addEntity('alice');
        $this->assertTrue( (strlen($sAclObj->getAuditTrail()) === 0) );
    }
    
    public function testThatEnableVerboseAuditWorksAsExpected() {
        
        $sAclObj = new SimpleAcl();

        $sAclObj->enableAuditTrail(true)
                ->enableVerboseAudit(true)
                ->addEntity('bob');
        
        $verboseAuditTrailStr1 = $sAclObj->getAuditTrail();
        
        $this->assertTrue( (strlen($verboseAuditTrailStr1) > 0) );
        
        $sAclObj->removeEntity('bob');
        
        $this->assertNull($sAclObj->getEntity('bob'));
        
        $sAclObj->clearAuditTrail()
                ->enableVerboseAudit(false)
                ->addEntity('bob');
        
        $this->assertTrue(
            (strlen($verboseAuditTrailStr1) > strlen($sAclObj->getAuditTrail())) 
        );
    }
    
    public function testThatGetAuditTrailWorksAsExpected() {
        
        $sAclObj = new SimpleAcl();

        $sAclObj->enableAuditTrail(true);
        
        $this->assertTrue( (strlen($sAclObj->getAuditTrail()) === 0) );
        
        $sAclObj->addEntity('bob');
        
        $this->assertTrue( (strlen($sAclObj->getAuditTrail()) > 0) );
        
        $sAclObj2 = new SimpleAcl();

        $sAclObj2->enableAuditTrail(false);
        
        $this->assertTrue( (strlen($sAclObj2->getAuditTrail()) === 0) );
        
        $sAclObj2->addEntity('bob');
        
        $this->assertTrue( (strlen($sAclObj2->getAuditTrail()) === 0) );
    }
    
    public function testThatGetAuditTrailOutputsWorksAsExpected() {
        
        $sAclObj = $this->createNewSimpleAclExposingNonPublicMethods(
            GenericPermissionableEntity::class, 
            GenericPermission::class,
            GenericPermissionableEntitiesCollection::class,
            GenericPermissionsCollection::class,
            true
        );

        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        //// TESTING OUTPUTS FOR addEntity
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $auditStr = $sAclObj->clearAuditTrail()
                            ->enableAuditTrail(true)
                            ->enableVerboseAudit(true)
                            ->addEntity('bob')
                            ->getAuditTrail();
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::addEntity('bob') trying to create and add a new entity whose ID will be `bob`", $auditStr);
        $this->assertStringContainsString("Entity created", $auditStr);
        $this->assertStringContainsString("Initialized ", $auditStr);
        $this->assertStringContainsString("::entitiesCollection to a new empty instance of SimpleAcl\GenericPermissionableEntitiesCollection", $auditStr);
        $this->assertStringContainsString("Successfully added the following entity:".PHP_EOL, $auditStr);
        $this->assertStringContainsString("SimpleAcl\GenericPermissionableEntity ", $auditStr);
        $this->assertStringContainsString("id: `bob`", $auditStr);
        $this->assertStringContainsString("parentEntities:", $auditStr);
        $this->assertStringContainsString("SimpleAcl\GenericPermissionableEntitiesCollection (", $auditStr);
        $this->assertStringContainsString("permissions: ", $auditStr);
        $this->assertStringContainsString("SimpleAcl\GenericPermissionsCollection (", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::addEntity('bob')", $auditStr);

        $sAclObj->removeEntity('bob');
        $auditStr = $sAclObj->clearAuditTrail()
                            ->enableAuditTrail(true)
                            ->enableVerboseAudit(false)
                            ->addEntity('bob')
                            ->getAuditTrail();
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::addEntity('bob')", $auditStr);
        $this->assertStringContainsString("Entity created", $auditStr);
        $this->assertStringContainsString("Successfully added the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::addEntity('bob')", $auditStr);
        
        $sAclObj->removeEntity('bob');
        $auditStr = $sAclObj->clearAuditTrail()
                            ->addEntity('bob')
                            ->enableAuditTrail(true)
                            ->enableVerboseAudit(false)
                            ->addEntity('bob')
                            ->getAuditTrail();
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::addEntity('bob')", $auditStr);
        $this->assertStringContainsString("Entity created", $auditStr);
        $this->assertStringContainsString("An entity with the specified entity ID `bob` already exists", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::addEntity('bob')", $auditStr);
        
        $sAclObj->removeEntity('bob');
        $auditStr = $sAclObj->clearAuditTrail()
                            ->addEntity('bob')
                            ->enableAuditTrail(true)
                            ->enableVerboseAudit(true)
                            ->addEntity('bob')
                            ->getAuditTrail();
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::addEntity('bob') trying to create and add a new entity whose ID will be `bob`", $auditStr);
        $this->assertStringContainsString("Entity created", $auditStr);
        $this->assertStringContainsString("An entity with the specified entity ID `bob` already exists in the entities collection, no need to add", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::addEntity('bob')", $auditStr);
        

        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        //// TESTING OUTPUTS FOR addParentEntity
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $auditStr = $sAclObj->clearAuditTrail()
                            ->enableAuditTrail(true)
                            ->enableVerboseAudit(true)
                            ->addParentEntity('bob', 'parent')
                            ->getAuditTrail();
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::addParentEntity('bob', 'parent') trying to add a new parent entity whose ID will be `parent` to  the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("Retrieved the following item: NULL", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `NULL` and actual return value of NULL", $auditStr);
        $this->assertStringContainsString("The entity whose ID is `bob` is not yet created, trying to create it now", $auditStr);
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::addEntity('bob') trying to create and add a new entity whose ID will be `bob`", $auditStr);
        $this->assertStringContainsString("Entity created", $auditStr);
        $this->assertStringContainsString("Successfully added the following entity:", $auditStr);
        $this->assertStringContainsString("		SimpleAcl\GenericPermissionableEntity", $auditStr);
        $this->assertStringContainsString("		{", $auditStr);
        $this->assertStringContainsString("			id: `bob`", $auditStr);
        $this->assertStringContainsString("			parentEntities:", $auditStr);
        $this->assertStringContainsString("				SimpleAcl\GenericPermissionableEntitiesCollection", $auditStr);
        $this->assertStringContainsString("				{", $auditStr);
        $this->assertStringContainsString("				}", $auditStr);
        $this->assertStringContainsString("			permissions:", $auditStr);
        $this->assertStringContainsString("				SimpleAcl\GenericPermissionsCollection", $auditStr);
        $this->assertStringContainsString("		}", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::addEntity('bob')", $auditStr);
        $this->assertStringContainsString("Retrieved the following item: SimpleAcl\GenericPermissionableEntity", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `object` that is an instance of `SimpleAcl\GenericPermissionableEntity` with the following string representation:", $auditStr);
        $this->assertStringContainsString("Parent entity whose ID is `parent` has been added to the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::addParentEntity('bob', 'parent')", $auditStr);
        
        
        $sAclObj->removeEntity('bob');
        $auditStr = $sAclObj->clearAuditTrail()
                            ->enableAuditTrail(true)
                            ->enableVerboseAudit(false)
                            ->addParentEntity('bob', 'parent')
                            ->getAuditTrail();
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::addParentEntity('bob', 'parent')", $auditStr);
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("Retrieved the following item: NULL", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("The entity whose ID is `bob` is not yet created, trying to create it now", $auditStr);
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::addEntity('bob')", $auditStr);
        $this->assertStringContainsString("Entity created", $auditStr);
        $this->assertStringContainsString("Successfully added the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::addEntity('bob')", $auditStr);
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("Successfully retrieved the desired entity.", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("Parent entity whose ID is `parent` has been added to the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::addParentEntity('bob', 'parent')", $auditStr);
        
        
        $sAclObj->removeEntity('bob');
        $auditStr = $sAclObj->addEntity('bob')
                            ->clearAuditTrail()
                            ->enableAuditTrail(true)
                            ->enableVerboseAudit(false)
                            ->addParentEntity('bob', 'parent')
                            ->getAuditTrail();
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::addParentEntity('bob', 'parent')", $auditStr);
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("Successfully retrieved the desired entity.", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("Parent entity whose ID is `parent` has been added to the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::addParentEntity('bob', 'parent')", $auditStr);
        
        
        $sAclObj->removeEntity('bob');
        $auditStr = $sAclObj->addEntity('bob')
                            ->clearAuditTrail()
                            ->enableAuditTrail(true)
                            ->enableVerboseAudit(true)
                            ->addParentEntity('bob', 'parent')
                            ->getAuditTrail();
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::addParentEntity('bob', 'parent') trying to add a new parent entity whose ID will be `parent` to  the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("Retrieved the following item: SimpleAcl\GenericPermissionableEntity", $auditStr);
        $this->assertStringContainsString("		{", $auditStr);
        $this->assertStringContainsString("			id: `bob`", $auditStr);
        $this->assertStringContainsString("			parentEntities: ", $auditStr);
        $this->assertStringContainsString("				SimpleAcl\GenericPermissionableEntitiesCollection", $auditStr);
        $this->assertStringContainsString("				{", $auditStr);
        $this->assertStringContainsString("				}", $auditStr);
        $this->assertStringContainsString("			permissions:", $auditStr);
        $this->assertStringContainsString("				SimpleAcl\GenericPermissionsCollection", $auditStr);
        $this->assertStringContainsString("				{", $auditStr);
        $this->assertStringContainsString("				}", $auditStr);
        $this->assertStringContainsString("		}", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `object` that is an instance of `SimpleAcl\GenericPermissionableEntity` with the following string representation:", $auditStr);
        $this->assertStringContainsString("Parent entity whose ID is `parent` has been added to the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("Exiting SimpleAcl\SimpleAcl::addParentEntity('bob', 'parent')", $auditStr);
        

        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        //// TESTING OUTPUTS FOR addPermission
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $auditStr = $sAclObj//->addEntity('bob')
                            ->clearAuditTrail()
                            ->enableAuditTrail(true)
                            ->enableVerboseAudit(true)
                            ->addPermission(
                               'bob', 
                               'edit',
                               'blog-post',
                               true,
                               function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                               true,
                               false
                            )
                            ->getAuditTrail();
        $this->assertStringContainsString(": Entered SimpleAcl\SimpleAcl::addPermission(...) to try to add a permission to  the entity whose ID is `bob`. Method Parameters:", $auditStr);
        $this->assertStringContainsString(
            "
array (
  'entityId' => 'bob',
  'action' => 'edit',
  'resource' => 'blog-post',
  'allowActionOnResource' => true,
  'additionalAssertions' => 
  Closure::__set_state(array(
  )),
  'argsForCallback' => 
  array (
    0 => true,
    1 => false,
  ),
)", $auditStr);
        $this->assertStringContainsString(": Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString(": Retrieved the following item: NULL", $auditStr);
        $this->assertStringContainsString(": Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `NULL` and actual return value of NULL", $auditStr);
        $this->assertStringContainsString(": The entity whose ID is `bob` has not yet been created, trying to create it now", $auditStr);
        $this->assertStringContainsString(": Entered SimpleAcl\SimpleAcl::addEntity('bob') trying to create and add a new entity whose ID will be `bob`", $auditStr);
        $this->assertStringContainsString(": Entity created", $auditStr);
        $this->assertStringContainsString(": Successfully added the following entity:", $auditStr);
        $this->assertStringContainsString("SimpleAcl\GenericPermissionableEntity (", $auditStr);
        $this->assertStringContainsString(
            "
		{
			id: `bob`
			parentEntities: 
				SimpleAcl\GenericPermissionableEntitiesCollection (", 
            $auditStr);
        $this->assertStringContainsString(
            "
				{
				
				}
				
			permissions: 
				SimpleAcl\GenericPermissionsCollection (", 
            $auditStr);
        $this->assertStringContainsString(
            "
				{
				
				}
				
		
		}", 
            $auditStr);
        $this->assertStringContainsString(": Exiting SimpleAcl\SimpleAcl::addEntity('bob')", $auditStr);
        $this->assertStringContainsString(": Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString(": Retrieved the following item: SimpleAcl\GenericPermissionableEntity (", $auditStr);
        $this->assertStringContainsString(": Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `object` that is an instance of `SimpleAcl\GenericPermissionableEntity` with the following string representation:", $auditStr);
        $this->assertStringContainsString(": Permission with the parameters below has been added to the entity whose ID is `bob`:", $auditStr);
        $this->assertStringContainsString("
action: `edit`
resource: `blog-post`
allowActionOnResource: true
additionalAssertions: Closure::__set_state(array(
))
argsForCallback: array (
  0 => true,
  1 => false,
)",      
            $auditStr);
        $this->assertStringContainsString(": Exiting SimpleAcl\SimpleAcl::addPermission(...)", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $auditStr = $sAclObj//->addEntity('bob')
                            ->clearAuditTrail()
                            ->enableAuditTrail(true)
                            ->enableVerboseAudit(false)
                            ->addPermission(
                               'bob', 
                               'edit',
                               'blog-post',
                               true,
                               function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                               true,
                               false
                            )
                            ->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::addPermission(...)", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: NULL", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: The entity whose ID is `bob` has not yet been created, trying to create it now", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::addEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Entity created", $auditStr);
        $this->assertStringContainsString("]: Successfully added the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::addEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Successfully retrieved the desired entity.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Permission with the parameters below has been added to the entity whose ID is `bob`:", $auditStr);
        $this->assertStringContainsString("
action: `edit`
resource: `blog-post`
allowActionOnResource: true
additionalAssertions: Closure::__set_state(array(
))
argsForCallback: array (
  0 => true,
  1 => false,
)",
            $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::addPermission(...)", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $auditStr = $sAclObj->addEntity('bob')
                            ->clearAuditTrail()
                            ->enableAuditTrail(true)
                            ->enableVerboseAudit(true)
                            ->addPermission(
                               'bob', 
                               'edit',
                               'blog-post',
                               true,
                               function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                               true,
                               false
                            )
                            ->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::addPermission(...) to try to add a permission to  the entity whose ID is `bob`. Method Parameters:", $auditStr);
        $this->assertStringContainsString("
array (
  'entityId' => 'bob',
  'action' => 'edit',
  'resource' => 'blog-post',
  'allowActionOnResource' => true,
  'additionalAssertions' => 
  Closure::__set_state(array(
  )),
  'argsForCallback' => 
  array (
    0 => true,
    1 => false,
  ),
)", 
            $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: SimpleAcl\GenericPermissionableEntity (", $auditStr);
        $this->assertStringContainsString("
		{
			id: `bob`
			parentEntities: 
				SimpleAcl\GenericPermissionableEntitiesCollection (", $auditStr);
        $this->assertStringContainsString("
				{
				
				}", 
            $auditStr);
        $this->assertStringContainsString("
			permissions: 
				SimpleAcl\GenericPermissionsCollection (", 
            $auditStr);
        $this->assertStringContainsString("
				{
				
				}
				
		
		}", 
            $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `object` that is an instance of `SimpleAcl\GenericPermissionableEntity` with the following string representation:", $auditStr);
        $this->assertStringContainsString("]: Permission with the parameters below has been added to the entity whose ID is `bob`:", $auditStr);
        $this->assertStringContainsString("
action: `edit`
resource: `blog-post`
allowActionOnResource: true
additionalAssertions: Closure::__set_state(array(
))
argsForCallback: array (
  0 => true,
  1 => false,
)", 
            $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::addPermission(...)", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $auditStr = $sAclObj->addEntity('bob')
                            ->clearAuditTrail()
                            ->enableAuditTrail(true)
                            ->enableVerboseAudit(false)
                            ->addPermission(
                               'bob', 
                               'edit',
                               'blog-post',
                               true,
                               function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                               true,
                               false
                            )
                            ->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::addPermission(...)", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Successfully retrieved the desired entity.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Permission with the parameters below has been added to the entity whose ID is `bob`:", $auditStr);
        $this->assertStringContainsString("
action: `edit`
resource: `blog-post`
allowActionOnResource: true
additionalAssertions: Closure::__set_state(array(
))
argsForCallback: array (
  0 => true,
  1 => false,
)", 
            $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::addPermission(...)", $auditStr);
        

        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        //// TESTING OUTPUTS FOR getEntity
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj//->addEntity('bob')
                ->enableAuditTrail(true)
                ->enableVerboseAudit(true)
                ->getEntity('bob');
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: NULL", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `NULL` and actual return value of NULL", $auditStr);
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj//->addEntity('bob')
                ->enableAuditTrail(true)
                ->enableVerboseAudit(false)
                ->getEntity('bob');
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: NULL", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj->addEntity('bob')
                ->enableAuditTrail(true)
                ->enableVerboseAudit(true)
                ->getEntity('bob');
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: SimpleAcl\GenericPermissionableEntity (", $auditStr);
        $this->assertStringContainsString("
{
	id: `bob`
	parentEntities: 
		SimpleAcl\GenericPermissionableEntitiesCollection (", 
            $auditStr);
        $this->assertStringContainsString(
"
		{
		
		}
		
	permissions: 
		SimpleAcl\GenericPermissionsCollection (", 
            $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `object` that is an instance of `SimpleAcl\GenericPermissionableEntity` with the following string representation: ", $auditStr);
        $this->assertStringContainsString("SimpleAcl\GenericPermissionableEntity (", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj->addEntity('bob')
                ->enableAuditTrail(true)
                ->enableVerboseAudit(false)
                ->getEntity('bob');
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Successfully retrieved the desired entity.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        

        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        //// TESTING OUTPUTS FOR removeEntity
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj//->addEntity('bob')
                ->enableAuditTrail(true)
                ->enableVerboseAudit(true)
                ->removeEntity('bob');
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::removeEntity('bob') trying to remove the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: NULL", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `NULL` and actual return value of NULL", $auditStr);
        $this->assertStringContainsString("]: The entity whose ID is `bob` does not exist, no need for removal.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::removeEntity('bob') with a return type of `NULL` and actual return value of NULL", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj//->addEntity('bob')
                ->enableAuditTrail(true)
                ->enableVerboseAudit(false)
                ->removeEntity('bob');
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::removeEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: NULL", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: The entity whose ID is `bob` does not exist, no need for removal.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::removeEntity('bob')", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj->addEntity('bob')
                ->enableAuditTrail(true)
                ->enableVerboseAudit(true)
                ->removeEntity('bob');
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::removeEntity('bob') trying to remove the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: SimpleAcl\GenericPermissionableEntity (", $auditStr);
        $this->assertStringContainsString("
		{
			id: `bob`
			parentEntities: 
				SimpleAcl\GenericPermissionableEntitiesCollection (", 
            $auditStr);
        $this->assertStringContainsString("
				{
				
				}
				
			permissions: 
				SimpleAcl\GenericPermissionsCollection (", 
            $auditStr);
        $this->assertStringContainsString("
				{
				
				}
				
		
		}", 
            $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `object` that is an instance of `SimpleAcl\GenericPermissionableEntity` with the following string representation:", $auditStr);
        $this->assertStringContainsString("]: Successfully removed the entity whose ID is `bob`.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::removeEntity('bob') with a return type of `object` that is an instance of `SimpleAcl\GenericPermissionableEntity` with the following string representation:", $auditStr);
        $this->assertStringContainsString("SimpleAcl\GenericPermissionableEntity (", $auditStr);
        $this->assertStringContainsString("
{
	id: `bob`
	parentEntities: 
		SimpleAcl\GenericPermissionableEntitiesCollection (", 
            $auditStr);
        $this->assertStringContainsString("
		{
		
		}
		
	permissions: 
		SimpleAcl\GenericPermissionsCollection (", 
            $auditStr);
        $this->assertStringContainsString("
		{
		
		}
		

}", 
        $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj->addEntity('bob')
                ->enableAuditTrail(true)
                ->enableVerboseAudit(false)
                ->removeEntity('bob');
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::removeEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Successfully retrieved the desired entity.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Successfully removed the entity whose ID is `bob`.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::removeEntity('bob')", $auditStr);
        

        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        //// TESTING OUTPUTS FOR removeParentEntity
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj//->addParentEntity('bob', 'parent')
                ->enableAuditTrail(true)
                ->enableVerboseAudit(true)
                ->removeParentEntity('bob', 'parent');
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::removeParentEntity('bob', 'parent') trying to remove the parent entity whose ID is `parent` from  the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: NULL", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `NULL` and actual return value of NULL", $auditStr);
        $this->assertStringContainsString("]: The entity whose ID is `bob` doesn't exist, no need trying to remove a parent entity.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::removeParentEntity('bob', 'parent') with a return type of `NULL` and actual return value of NULL", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj//->addParentEntity('bob', 'parent')
                ->enableAuditTrail(true)
                ->enableVerboseAudit(false)
                ->removeParentEntity('bob', 'parent');
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::removeParentEntity('bob', 'parent')", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: NULL", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: The entity whose ID is `bob` doesn't exist, no need trying to remove a parent entity.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::removeParentEntity('bob', 'parent')", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj->addParentEntity('bob', 'parent')
                ->enableAuditTrail(true)
                ->enableVerboseAudit(true)
                ->removeParentEntity('bob', 'parent');
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::removeParentEntity('bob', 'parent') trying to remove the parent entity whose ID is `parent` from  the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: SimpleAcl\GenericPermissionableEntity (", $auditStr);
        $this->assertStringContainsString("
		{
			id: `bob`
			parentEntities: 
				SimpleAcl\GenericPermissionableEntitiesCollection (", 
            $auditStr);
        $this->assertStringContainsString("
				{
					item[0]: SimpleAcl\GenericPermissionableEntity (", 
            $auditStr);
        $this->assertStringContainsString("
					{
						id: `parent`
						parentEntities: 
							SimpleAcl\GenericPermissionableEntitiesCollection (", 
            $auditStr);
        $this->assertStringContainsString("
							{
							
							}
							
						permissions: 
							SimpleAcl\GenericPermissionsCollection (", 
            $auditStr);
        $this->assertStringContainsString("
							}
							
					
					}
					
				
				}
				
			permissions: 
				SimpleAcl\GenericPermissionsCollection (", 
            $auditStr);
        $this->assertStringContainsString("
				{
				
				}
				
		
		}", 
            $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `object` that is an instance of `SimpleAcl\GenericPermissionableEntity` with the following string representation: ", 
            $auditStr);
        $this->assertStringContainsString("]: Parent entity has been successfully removed.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::removeParentEntity('bob', 'parent') with a return type of `object` that is an instance of `SimpleAcl\GenericPermissionableEntity` with the following string representation: ", 
            $auditStr);
        $this->assertStringContainsString("
{
	id: `parent`
	parentEntities: 
		SimpleAcl\GenericPermissionableEntitiesCollection (", 
            $auditStr);
        $this->assertStringContainsString("
		{
		
		}
		
	permissions: 
		SimpleAcl\GenericPermissionsCollection (", $auditStr);
        $this->assertStringContainsString("
		{
		
		}
		

}", 
            $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj->addParentEntity('bob', 'parent')
                ->enableAuditTrail(true)
                ->enableVerboseAudit(false)
                ->removeParentEntity('bob', 'parent');
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::removeParentEntity('bob', 'parent')", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Successfully retrieved the desired entity.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Parent entity has been successfully removed.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::removeParentEntity('bob', 'parent')", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        //// TESTING OUTPUTS FOR removePermission
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj
//                ->addPermission(
//                    'bob', 
//                    'edit',
//                    'blog-post',
//                    true,
//                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
//                    true,
//                    false
//                 )
                 ->clearAuditTrail()
                 ->enableAuditTrail(true)
                 ->enableVerboseAudit(true)
                 ->removePermission(
                    'bob', 
                    'edit',
                    'blog-post',
                    true,
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 );
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::removePermission(...) to try to remove a permission from  the entity whose ID is `bob`. Method Parameters:", $auditStr);
        $this->assertStringContainsString("
array (
  'entityId' => 'bob',
  'action' => 'edit',
  'resource' => 'blog-post',
  'allowActionOnResource' => true,
  'additionalAssertions' => 
  Closure::__set_state(array(
  )),
  'argsForCallback' => 
  array (
    0 => true,
    1 => false,
  ),
)", 
            $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: NULL", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `NULL` and actual return value of NULL", $auditStr);
        $this->assertStringContainsString("]: The entity whose ID is `bob` doesn't exist, no need trying to remove the specified permission.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::removePermission(....) with a return type of `NULL` and actual return value of NULL", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj
//                ->addPermission(
//                    'bob', 
//                    'edit',
//                    'blog-post',
//                    true,
//                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
//                    true,
//                    false
//                 )
                 ->clearAuditTrail()
                 ->enableAuditTrail(true)
                 ->enableVerboseAudit(false)
                 ->removePermission(
                    'bob', 
                    'edit',
                    'blog-post',
                    true,
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 );
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::removePermission(...)", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: NULL", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: The entity whose ID is `bob` doesn't exist, no need trying to remove the specified permission.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::removePermission(....)", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj
                ->addPermission(
                    'bob', 
                    'edit',
                    'blog-post',
                    true,
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 )
                 ->clearAuditTrail()
                 ->enableAuditTrail(true)
                 ->enableVerboseAudit(true)
                 ->removePermission(
                    'bob', 
                    'edit',
                    'blog-post',
                    true,
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 );
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::removePermission(...) to try to remove a permission from  the entity whose ID is `bob`. Method Parameters:", $auditStr);
        $this->assertStringContainsString("
array (
  'entityId' => 'bob',
  'action' => 'edit',
  'resource' => 'blog-post',
  'allowActionOnResource' => true,
  'additionalAssertions' => 
  Closure::__set_state(array(
  )),
  'argsForCallback' => 
  array (
    0 => true,
    1 => false,
  ),
)", 
            $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: SimpleAcl\GenericPermissionableEntity (", $auditStr);
        $this->assertStringContainsString("
		{
			id: `bob`
			parentEntities: 
				SimpleAcl\GenericPermissionableEntitiesCollection (", 
            $auditStr);
        $this->assertStringContainsString("
				{
				
				}
				
			permissions: 
				SimpleAcl\GenericPermissionsCollection (", 
            $auditStr);
        $this->assertStringContainsString("
				{
					item[0]: SimpleAcl\GenericPermission (", 
            $auditStr);
        $this->assertStringContainsString("
					{
						action: `edit`
						resource: `blog-post`
						allowActionOnResource: true
						additionalAssertions: Closure::__set_state(array(
						))
						argsForCallback: array (
						  0 => true,
						  1 => false,
						)
					
					}
					
				
				}
				
		
		}", 
            $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `object` that is an instance of `SimpleAcl\GenericPermissionableEntity` with the following string representation:", $auditStr);
        $this->assertStringContainsString("		SimpleAcl\GenericPermissionableEntity (", $auditStr);
        $this->assertStringContainsString("]: Permission with the parameters below has been removed from the entity whose ID is `bob`:", $auditStr);
        $this->assertStringContainsString("
action: `edit`
resource: `blog-post`
allowActionOnResource: true
additionalAssertions: Closure::__set_state(array(
))
argsForCallback: array (
  0 => true,
  1 => false,
)", 
            $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::removePermission(....) with a return type of `object` that is an instance of `SimpleAcl\GenericPermission` with the following string representation:", $auditStr);
        $this->assertStringContainsString("SimpleAcl\GenericPermission (", $auditStr);
        $this->assertStringContainsString("
{
	action: `edit`
	resource: `blog-post`
	allowActionOnResource: true
	additionalAssertions: Closure::__set_state(array(
	))
	argsForCallback: array (
	  0 => true,
	  1 => false,
	)

}", 
            $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj->addPermission(
                    'bob', 
                    'edit',
                    'blog-post',
                    true,
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 )
                 ->clearAuditTrail()
                 ->enableAuditTrail(true)
                 ->enableVerboseAudit(false)
                 ->removePermission(
                    'bob', 
                    'edit',
                    'blog-post',
                    true,
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 );
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::removePermission(...)", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Successfully retrieved the desired entity.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Permission with the parameters below has been removed from the entity whose ID is `bob`:", $auditStr);
        $this->assertStringContainsString("
action: `edit`
resource: `blog-post`
allowActionOnResource: true
additionalAssertions: Closure::__set_state(array(
))
argsForCallback: array (
  0 => true,
  1 => false,
)", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::removePermission(....)", $auditStr);
        
        
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        //// TESTING OUTPUTS FOR isAllowed
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj->clearAuditTrail()
                ->enableAuditTrail(true)
                ->enableVerboseAudit(true)
                ->isAllowed(
                    'bob', 
                    'edit',
                    'blog-post',
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                );
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::isAllowed(...) to check if the entity `bob` is allowed to perform the specified action `edit` on the specified resource `blog-post`.  
Supplied Method Parameters:", $auditStr);
        $this->assertStringContainsString("
array (
  'entityId' => 'bob',
  'action' => 'edit',
  'resource' => 'blog-post',
  'additionalAssertions' => 
  Closure::__set_state(array(
  )),
  'argsForCallback' => 
  array (
    0 => true,
    1 => false,
  ),
)", $auditStr);
        $this->assertStringContainsString("]: Trying to retrieve the entity object associated with specified entity ID `bob`", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: NULL", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `NULL` and actual return value of NULL", $auditStr);
        $this->assertStringContainsString("]: Could not retrieve the entity object associated with specified entity ID `bob`.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::isAllowed(....) with a return type of `boolean` and actual return value of false", $auditStr);

        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj->clearAuditTrail()
                ->enableAuditTrail(true)
                ->enableVerboseAudit(false)
                ->isAllowed(
                    'bob', 
                    'edit',
                    'blog-post',
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                );
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::isAllowed(...)", $auditStr);
        $this->assertStringContainsString("]: Trying to retrieve the entity object associated with specified entity ID `bob`", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: NULL", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Could not retrieve the entity object associated with specified entity ID `bob`.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::isAllowed(....)", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj->clearAuditTrail()
                ->enableAuditTrail(true)
                ->enableVerboseAudit(true)
                ->isAllowed(
                    '', 
                    'edit',
                    'blog-post',
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                );
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::isAllowed(...) to check if the entity `` is allowed to perform the specified action `edit` on the specified resource `blog-post`.  
Supplied Method Parameters:", $auditStr);
        $this->assertStringContainsString("
array (
  'entityId' => '',
  'action' => 'edit',
  'resource' => 'blog-post',
  'additionalAssertions' => 
  Closure::__set_state(array(
  )),
  'argsForCallback' => 
  array (
    0 => true,
    1 => false,
  ),
)", 
            $auditStr);
        $this->assertStringContainsString("]: An empty string was supplied as the entity ID, so we are searching through permissions for all existing entities until we get the first match", $auditStr);
        $this->assertStringContainsString("]: Did not find any permission belonging to any entity that allows the specified action `edit` to be performed on the specified resource `blog-post`.", $auditStr);
        $this->assertStringContainsString("Either no entity has such a permission or an entity has a permission that explicitly denies the specified action `edit` from being performed on the specified resource `blog-post`", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::isAllowed(....) with a return type of `boolean` and actual return value of false", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj->clearAuditTrail()
                ->enableAuditTrail(true)
                ->enableVerboseAudit(false)
                ->isAllowed(
                    '', 
                    'edit',
                    'blog-post',
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 );
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::isAllowed(...)", $auditStr);
        $this->assertStringContainsString("]: An empty string was supplied as the entity ID, so we are searching through permissions for all existing entities until we get the first match", $auditStr);
        $this->assertStringContainsString("]: Did not find any permission belonging to any entity that allows the specified action `edit` to be performed on the specified resource `blog-post`.", $auditStr);
        $this->assertStringContainsString("Either no entity has such a permission or an entity has a permission that explicitly denies the specified action `edit` from being performed on the specified resource `blog-post`", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::isAllowed(....)", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj
                ->addPermission(
                    'bob', 
                    'edit',
                    'blog-post',
                    true,
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 )
                 ->clearAuditTrail()
                 ->enableAuditTrail(true)
                 ->enableVerboseAudit(true)
                 ->isAllowed(
                    'bob', 
                    'edit',
                    'blog-post',
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 );
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::isAllowed(...) to check if the entity `bob` is allowed to perform the specified action `edit` on the specified resource `blog-post`.  
Supplied Method Parameters:", $auditStr);
        $this->assertStringContainsString("
array (
  'entityId' => 'bob',
  'action' => 'edit',
  'resource' => 'blog-post',
  'additionalAssertions' => 
  Closure::__set_state(array(
  )),
  'argsForCallback' => 
  array (
    0 => true,
    1 => false,
  ),
)", $auditStr);
        $this->assertStringContainsString("]: Trying to retrieve the entity object associated with specified entity ID `bob`", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: SimpleAcl\GenericPermissionableEntity (", $auditStr);
        $this->assertStringContainsString("
		{
			id: `bob`
			parentEntities: 
				SimpleAcl\GenericPermissionableEntitiesCollection (", 
            $auditStr);
        $this->assertStringContainsString("
				{
				
				}
				
			permissions: 
				SimpleAcl\GenericPermissionsCollection (", 
            $auditStr);
        $this->assertStringContainsString("
				{
					item[0]: SimpleAcl\GenericPermission (", 
            $auditStr);
        $this->assertStringContainsString("
					{
						action: `edit`
						resource: `blog-post`
						allowActionOnResource: true
						additionalAssertions: Closure::__set_state(array(
						))
						argsForCallback: array (
						  0 => true,
						  1 => false,
						)
					
					}
					
				
				}
				
		
		}", 
            $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `object` that is an instance of `SimpleAcl\GenericPermissionableEntity` with the following string representation: ", $auditStr);
        $this->assertStringContainsString("		SimpleAcl\GenericPermissionableEntity (", $auditStr);
        $this->assertStringContainsString("]: Successfully retrieved the entity object associated with specified entity ID `bob`.", $auditStr);
        $this->assertStringContainsString("]: Searching through the permissions for the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Found a permission belonging to the entity whose ID is `bob` that allows the specified action `edit` to be performed on the specified resource `blog-post`.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::isAllowed(....) with a return type of `boolean` and actual return value of true", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj
                ->addPermission(
                    'bob', 
                    'edit',
                    'blog-post',
                    true,
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 )
                 ->clearAuditTrail()
                 ->enableAuditTrail(true)
                 ->enableVerboseAudit(false)
                 ->isAllowed(
                    'bob', 
                    'edit',
                    'blog-post',
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 );
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::isAllowed(...)", $auditStr);
        $this->assertStringContainsString("]: Trying to retrieve the entity object associated with specified entity ID `bob`", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Successfully retrieved the desired entity.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Successfully retrieved the entity object associated with specified entity ID `bob`.", $auditStr);
        $this->assertStringContainsString("]: Searching through the permissions for the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Found a permission belonging to the entity whose ID is `bob` that allows the specified action `edit` to be performed on the specified resource `blog-post`.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::isAllowed(....)", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj
                ->addPermission(
                    'bob', 
                    'edit',
                    'blog-post',
                    true,
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 )
                 ->clearAuditTrail()
                 ->enableAuditTrail(true)
                 ->enableVerboseAudit(true)
                 ->isAllowed(
                    '', 
                    'edit',
                    'blog-post',
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 );
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::isAllowed(...) to check if the entity `` is allowed to perform the specified action `edit` on the specified resource `blog-post`.  
Supplied Method Parameters:", $auditStr);
        $this->assertStringContainsString("
array (
  'entityId' => '',
  'action' => 'edit',
  'resource' => 'blog-post',
  'additionalAssertions' => 
  Closure::__set_state(array(
  )),
  'argsForCallback' => 
  array (
    0 => true,
    1 => false,
  ),
)", 
            $auditStr);
        $this->assertStringContainsString("]: An empty string was supplied as the entity ID, so we are searching through permissions for all existing entities until we get the first match", $auditStr);
        $this->assertStringContainsString("]: Currently searching through the permissions for the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Found a permission belonging to the entity whose ID is `bob` that allows the specified action `edit` to be performed on the specified resource `blog-post`.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::isAllowed(....) with a return type of `boolean` and actual return value of true", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj->addPermission(
                    'bob', 
                    'edit',
                    'blog-post',
                    true,
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 )
                 ->clearAuditTrail()
                 ->enableAuditTrail(true)
                 ->enableVerboseAudit(false)
                 ->isAllowed(
                    '', 
                    'edit',
                    'blog-post',
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 );
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::isAllowed(...)", $auditStr);
        $this->assertStringContainsString("]: An empty string was supplied as the entity ID, so we are searching through permissions for all existing entities until we get the first match", $auditStr);
        $this->assertStringContainsString("]: Currently searching through the permissions for the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Found a permission belonging to the entity whose ID is `bob` that allows the specified action `edit` to be performed on the specified resource `blog-post`.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::isAllowed(....)", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj->addPermission(
                    'bob', 
                    'edit',
                    'blog-post',
                    true,
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 )
                 ->clearAuditTrail()
                 ->enableAuditTrail(true)
                 ->enableVerboseAudit(true)
                 ->isAllowed(
                    'bob', 
                    'edit',
                    'blog-post',
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    false,
                    false
                 );
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::isAllowed(...) to check if the entity `bob` is allowed to perform the specified action `edit` on the specified resource `blog-post`.  
Supplied Method Parameters:", $auditStr);
        $this->assertStringContainsString("
array (
  'entityId' => 'bob',
  'action' => 'edit',
  'resource' => 'blog-post',
  'additionalAssertions' => 
  Closure::__set_state(array(
  )),
  'argsForCallback' => 
  array (
    0 => false,
    1 => false,
  ),
)", 
            $auditStr);
        $this->assertStringContainsString("]: Trying to retrieve the entity object associated with specified entity ID `bob`", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob') trying to retrieve the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Retrieved the following item: SimpleAcl\GenericPermissionableEntity (", $auditStr);
        $this->assertStringContainsString("
		{
			id: `bob`
			parentEntities: 
				SimpleAcl\GenericPermissionableEntitiesCollection (", 
            $auditStr);
        $this->assertStringContainsString("
				{
				
				}
				
			permissions: 
				SimpleAcl\GenericPermissionsCollection (", 
            $auditStr);
        $this->assertStringContainsString("
				{
					item[0]: SimpleAcl\GenericPermission (", 
            $auditStr);
        $this->assertStringContainsString("
					{
						action: `edit`
						resource: `blog-post`
						allowActionOnResource: true
						additionalAssertions: Closure::__set_state(array(
						))
						argsForCallback: array (
						  0 => true,
						  1 => false,
						)
					
					}
					
				
				}
				
		
		}", 
            $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob') with a return type of `object` that is an instance of `SimpleAcl\GenericPermissionableEntity` with the following string representation:",  $auditStr);
        $this->assertStringContainsString("		SimpleAcl\GenericPermissionableEntity (", $auditStr);
        $this->assertStringContainsString("]: Successfully retrieved the entity object associated with specified entity ID `bob`.", $auditStr);
        $this->assertStringContainsString("]: Searching through the permissions for the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Did not find any permission belonging to the entity whose ID is `bob` that allows the specified action `edit` to be performed on the specified resource `blog-post`.", $auditStr);
        $this->assertStringContainsString("Either the entity whose ID is `bob` has no such permission or has a permission that explicitly denies the specified action `edit` from being performed on the specified resource `blog-post`", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::isAllowed(....) with a return type of `boolean` and actual return value of false", $auditStr);
        
        
        ////////////////////////////////////////////////////////////////////////
        $sAclObj->removeEntity('bob');
        $sAclObj->addPermission(
                    'bob', 
                    'edit',
                    'blog-post',
                    true,
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    true,
                    false
                 )
                 ->clearAuditTrail()
                 ->enableAuditTrail(true)
                 ->enableVerboseAudit(false)
                 ->isAllowed(
                    'bob', 
                    'edit',
                    'blog-post',
                    function(bool $boolVal, $boolVal2) {return $boolVal || $boolVal2;},
                    false,
                    false
                 );
        $auditStr = $sAclObj->getAuditTrail();
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::isAllowed(...)", $auditStr);
        $this->assertStringContainsString("]: Trying to retrieve the entity object associated with specified entity ID `bob`", $auditStr);
        $this->assertStringContainsString("]: Entered SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Successfully retrieved the desired entity.", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::getEntity('bob')", $auditStr);
        $this->assertStringContainsString("]: Successfully retrieved the entity object associated with specified entity ID `bob`.", $auditStr);
        $this->assertStringContainsString("]: Searching through the permissions for the entity whose ID is `bob`", $auditStr);
        $this->assertStringContainsString("]: Did not find any permission belonging to the entity whose ID is `bob` that allows the specified action `edit` to be performed on the specified resource `blog-post`.", $auditStr);
        $this->assertStringContainsString("Either the entity whose ID is `bob` has no such permission or has a permission that explicitly denies the specified action `edit` from being performed on the specified resource `blog-post`", $auditStr);
        $this->assertStringContainsString("]: Exiting SimpleAcl\SimpleAcl::isAllowed(....)", $auditStr);
    }
    
    public function testThatRemoveEntityWorksAsExpected() {
        
        $sAclObj = new SimpleAcl();

        $this->assertNull($sAclObj->removeEntity('bob'));
        
        $sAclObj->addEntity('bob');
        
        $entityBob = $sAclObj->getEntity('bob');
        
        $this->assertInstanceOf(PermissionableEntityInterface::class, $entityBob);
        
        $removedEntity = $sAclObj->removeEntity('bob');
        
        $this->assertSame($entityBob, $removedEntity);
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
                ->getAllParents()
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
                ->getAllParents()
                ->find('admin')
                ->addParent($sAclObj->createEntity('super-admin'));
        
        $sAclObj->getEntity('jdoe')
                ->getAllParents()
                ->find('admin')
                ->getAllParents()
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
        
        $this->assertCount(2, $sAclObj->getEntity('son')->getAllParents());
        $this->assertCount(2, $sAclObj->getEntity('son')->getDirectParents());
        
        $father = $sAclObj->removeParentEntity('son', 'father');
        $this->assertCount(1, $sAclObj->getEntity('son')->getAllParents());
        $this->assertInstanceOf(PermissionableEntityInterface::class, $father);
        $this->assertEquals('father', $father->getId());
        
        $mother = $sAclObj->removeParentEntity('son', 'mother');
        $this->assertCount(0, $sAclObj->getEntity('son')->getAllParents());
        $this->assertInstanceOf(PermissionableEntityInterface::class, $mother);
        $this->assertEquals('mother', $mother->getId());
        
        // NOTE: since permissions are mainly identified by the (action, resource) pairing
        // I am not bothering to add tests to see if the last three parameters to 
        // make any significant different when removing permissions. I may have to
        // revisit in a future release if things change.
    }
    
    public function testThatRemovePermissionWorksAsExpected() {
        
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
    
    public function testThatFormatReturnValueForAuditWorksAsExpected() {
        
        // Scalar variables are those containing an int, float, string or bool.
        // Types array, object and resource are not scalar.
        $nonObjectValues = [
            ['yabadoo'], "string",
            777.888, 777, tmpfile(), true, false
        ];
        $sAclObj = $this->createNewSimpleAclExposingNonPublicMethods();

        foreach ($nonObjectValues as $value) {
            
            $this->assertStringStartsWith(" with a return type of `", $sAclObj->formatReturnValueForAudit($value));
            $this->assertStringContainsString("` and actual return value of ", $sAclObj->formatReturnValueForAudit($value));
            $this->assertStringEndsWith(var_export($value, true), $sAclObj->formatReturnValueForAudit($value));
        }
        
        $entity = $sAclObj->createEntity('jill');
        $this->assertStringStartsWith(" with a return type of `object` that is an instance of `", $sAclObj->formatReturnValueForAudit($entity));
        $this->assertStringContainsString(get_class($entity), $sAclObj->formatReturnValueForAudit($entity));
        $this->assertStringContainsString("` with the following string representation: ".PHP_EOL, $sAclObj->formatReturnValueForAudit($entity));
        $this->assertStringEndsWith(trim(((string)$entity)), $sAclObj->formatReturnValueForAudit($entity));
        
        $arrObj = new ArrayObject();
        $this->assertStringStartsWith(" with a return type of `object` that is an instance of `", $sAclObj->formatReturnValueForAudit($arrObj));
        $this->assertStringContainsString(get_class($arrObj), $sAclObj->formatReturnValueForAudit($arrObj));
        $this->assertStringContainsString("` with the following string representation: ".PHP_EOL, $sAclObj->formatReturnValueForAudit($arrObj));
        $this->assertStringEndsWith(trim(var_export($arrObj, true)), $sAclObj->formatReturnValueForAudit($arrObj));
    }
    
    public function testThatGetMethodParameterNamesAndValsWorksAsExpected() {
        
        $sAclObj = $this->createNewSimpleAclExposingNonPublicMethods();
        
        $this->assertSame([], $sAclObj->getMethodParameterNamesAndVals('nonExistentMethod', ['dont care']));
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $expected = [ 'entityId' => 'bob', 'parentEntityId' => 'parent' ];
        $this->assertSame($expected, $sAclObj->getMethodParameterNamesAndVals('addParentEntity', ['bob', 'parent']));
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $expected2 = [ 'entityId' => 'bob' ];
        $this->assertSame($expected2, $sAclObj->getMethodParameterNamesAndVals('addParentEntity', ['bob']));
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $expected3 = [ 'parentEntityId' => 'parent' ];
        $this->assertSame($expected3, $sAclObj->getMethodParameterNamesAndVals('addParentEntity', [1 => 'parent']));
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $expected4 = [];
        $this->assertSame($expected4, $sAclObj->getMethodParameterNamesAndVals('addParentEntity', [2=>'bob', 3=>'parent']));
    }
    
    public function testThatLogActivityWorksAsExpected() {
        
        $sAclObj = $this->createNewSimpleAclExposingNonPublicMethods();
        
        $sAclObj->enableAuditTrail(true);
        
        $sAclObj->addParentEntity('bob', 'parent');
        
        $logStr = $sAclObj->getAuditTrail();
        
        // matches stuff like:
        //  [2020-12-01 18:15:40]
        $datePrefixRegex = '/\[[0-9][0-9][0-9][0-9]\-[0-9][0-9]\-[0-9][0-9] [0-9][0-9]:[0-9][0-9]:[0-9][0-9]\]/';
        $this->assertMatchesRegularExpression($datePrefixRegex, $logStr);
        
        // matches stuff like below prefixed with a tab:
        //  [2020-12-01 18:15:40]
        $datePrefixRegexWithTab = '/\t\[[0-9][0-9][0-9][0-9]\-[0-9][0-9]\-[0-9][0-9] [0-9][0-9]:[0-9][0-9]:[0-9][0-9]\]/';
        $this->assertMatchesRegularExpression($datePrefixRegexWithTab, $logStr);
                
        $shortLogStr = $sAclObj->clearAuditTrail()
                          ->enableVerboseAudit(false)
                          ->addParentEntity('bob', 'parent')
                          ->enableVerboseAudit(true)
                          ->getAuditTrail();
        
        // matches stuff like:
        //  [2020-12-01 18:15:40]
        $this->assertMatchesRegularExpression($datePrefixRegex, $shortLogStr);
        
        // matches stuff like below prefixed with a tab:
        //  [2020-12-01 18:15:40]
        $this->assertMatchesRegularExpression($datePrefixRegexWithTab, $shortLogStr);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        $strWithRegularDescription = $sAclObj->clearAuditTrail()
                                             ->logActivityPublic('Regular description', '')
                                             ->getAuditTrail();
        $this->assertMatchesRegularExpression($datePrefixRegex, $strWithRegularDescription);
        $this->assertStringContainsString('Regular description', $strWithRegularDescription);
        
        $strWithShortDescription = $sAclObj->clearAuditTrail()
                                           ->enableVerboseAudit(false)
                                           ->logActivityPublic('Regular description', 'Short description')
                                           ->getAuditTrail();
        $this->assertMatchesRegularExpression($datePrefixRegex, $strWithShortDescription);
        $this->assertStringContainsString('Short description', $strWithShortDescription);
        
        // Test that regular description is used when non-verbose audit
        // is enabled but there is an empty short description.
        $strWithShortDescription2 = $sAclObj->clearAuditTrail()
                                           ->enableVerboseAudit(false)
                                           ->logActivityPublic('Regular description', '')
                                           ->getAuditTrail();
        $this->assertMatchesRegularExpression($datePrefixRegex, $strWithShortDescription2);
        $this->assertStringContainsString('Regular description', $strWithShortDescription2);
        $this->assertStringNotContainsString('Short description', $strWithShortDescription2);
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
            public function throwInvalidArgExceptionDueToWrongClassName(string $class, string $function, string $wrongClassName, string $expectedIntefaceName, string $positionthParameter): void {
                parent::throwInvalidArgExceptionDueToWrongClassName($class, $function, $wrongClassName, $expectedIntefaceName, $positionthParameter);
            }
            
            public function formatReturnValueForAudit($returnVal): string {
                return parent::formatReturnValueForAudit($returnVal);
            }
            
            public function getMethodParameterNamesAndVals(string $methodName, array $paramVals): array {
                return parent::getMethodParameterNamesAndVals($methodName, $paramVals);
            }
            
            public function logActivityPublic(string $description, string $shortDescription = ''): SimpleAcl {
                return parent::logActivity($description, $shortDescription);
            }
        };
    }
}
