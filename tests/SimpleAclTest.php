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
    ////////////////////////// Helper Methods //////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    
    /**
     * 
     * @param string $permissionableEntityInterfaceClassName                first parameter required by SimpleAcl::__construct(....)
     * @param string $permissionInterfaceClassName                          second parameter required by SimpleAcl::__construct(....)
     * @param string $permissionableEntitiesCollectionInterfaceClassName    third parameter required by SimpleAcl::__construct(....)
     * @param string $permissionsCollectionInterfaceClassName               fourth parameter required by SimpleAcl::__construct(....)
     * 
     * @return SimpleAcl an anonymous class instance that extends SimpleAcl but changes all non-public methods to public
     */
    protected function createNewSimpleAclExposingNonPublicMethods(
        string $permissionableEntityInterfaceClassName = GenericPermissionableEntity::class,
        string $permissionInterfaceClassName = GenericPermission::class,
        string $permissionableEntitiesCollectionInterfaceClassName = GenericPermissionableEntitiesCollection::class,
        string $permissionsCollectionInterfaceClassName = GenericPermissionsCollection::class
    ) {
        
        return new class(
            $permissionableEntityInterfaceClassName, 
            $permissionInterfaceClassName, 
            $permissionableEntitiesCollectionInterfaceClassName, 
            $permissionsCollectionInterfaceClassName
        ) extends SimpleAcl {
            
            public function createEntityCollection(): PermissionableEntitiesCollectionInterface
            {
                return parent::createEntityCollection();
            }
            
            public function createEntity(string $entityId): PermissionableEntityInterface
            {
                return parent::createEntity($entityId);
            }
            
            public function createPermission(string $action, string $resource, bool $allowActionOnResource = true, callable $additionalAssertions = null, ...$argsForCallback): PermissionInterface
            {
                return parent::createPermission($action, $resource, $allowActionOnResource, $additionalAssertions, ...$argsForCallback);
            }
            
            public function createPermissionCollection(): PermissionsCollectionInterface
            {
                return parent::createPermissionCollection();
            }


            /** @noinspection SpellCheckingInspection */
            public function throwInvalidArgExceptionDueToWrongClassName(string $class, string $function, string $wrongClassName, string $expectedIntefaceName, string $positionthParameter) {
                parent::throwInvalidArgExceptionDueToWrongClassName($class, $function, $wrongClassName, $expectedIntefaceName, $positionthParameter);
            }
        };
    }
}
