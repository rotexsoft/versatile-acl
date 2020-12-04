<?php /** @noinspection PhpUnusedLocalVariableInspection */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

use \SimpleAcl\Interfaces\PermissionableEntitiesCollectionInterface;

use \SimpleAcl\GenericPermission;
use \SimpleAcl\GenericPermissionsCollection;

use \SimpleAcl\GenericPermissionableEntity;
use \SimpleAcl\GenericPermissionableEntitiesCollection;

use \SimpleAcl\Exceptions\EmptyEntityIdException;
use \SimpleAcl\Exceptions\ParentCannotBeChildException;

/**
 * Description of GenericPermissionableEntityTest
 *
 * @author Rotimi
 */
class GenericPermissionableEntityTest extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void { 
        
        parent::setUp();
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testConstructorWorksAsExpected() {
    
        $entity = new GenericPermissionableEntity("C");
        
        $this->assertEquals('c', $entity->getId()); // case-insensitivity
        
        $this->assertInstanceOf(GenericPermissionsCollection::class, $entity->getDirectPermissions());
        $this->assertInstanceOf(GenericPermissionableEntitiesCollection::class, $entity->getDirectParents());
        
        $this->assertEquals(0, $entity->getDirectPermissions()->count());
        $this->assertEquals(0, $entity->getDirectParents()->count());
        
        $parentEntities = new GenericPermissionableEntitiesCollection(
            new GenericPermissionableEntity("A"), 
            new GenericPermissionableEntity("B")
        );
        
        $permissions = new GenericPermissionsCollection(
            new GenericPermission('add', 'comment'),
            new GenericPermission('edit', 'comment')
        );
        
        $entityWithInjectedCollections = new GenericPermissionableEntity(
            "D", $permissions, $parentEntities
        );
        
        $this->assertEquals('d', $entityWithInjectedCollections->getId()); // case-insensitivity
        
        $this->assertSame($permissions, $entityWithInjectedCollections->getDirectPermissions());
        $this->assertSame($parentEntities, $entityWithInjectedCollections->getDirectParents());
    }
    
    public function testConstructorWorksAsExpected1() {
     
        $this->expectException(EmptyEntityIdException::class);
        $entity = new GenericPermissionableEntity("\t"); // throws exception
    }
    
    public function testConstructorWorksAsExpected2() {
     
        $this->expectException(EmptyEntityIdException::class);
        $entity = new GenericPermissionableEntity("\n"); // throws exception
    }
    
    public function testConstructorWorksAsExpected3() {
     
        $this->expectException(EmptyEntityIdException::class);
        $entity = new GenericPermissionableEntity("\r"); // throws exception
    }
    
    public function testConstructorWorksAsExpected4() {
     
        $this->expectException(EmptyEntityIdException::class);
        $entity = new GenericPermissionableEntity("\0"); // throws exception
    }
    
    public function testConstructorWorksAsExpected5() {
     
        $this->expectException(EmptyEntityIdException::class);
        $entity = new GenericPermissionableEntity("\x0B"); // throws exception
    }
    
    public function testConstructorWorksAsExpected6() {
     
        $this->expectException(EmptyEntityIdException::class);
        $entity = new GenericPermissionableEntity(" "); // throws exception
    }
    
    public function testConstructorWorksAsExpected7() {
     
        $this->expectException(EmptyEntityIdException::class);
        $entity = new GenericPermissionableEntity(" \t\n\r\0\x0B"); // throws exception
    }
    
    public function testConstructorWorksAsExpected8() {
     
        $this->expectException(EmptyEntityIdException::class);
        $entity = new GenericPermissionableEntity(''); // throws exception
    }
    
    public function testCreateCollectionWorksAsExpected() {
        
        $this->assertInstanceOf(GenericPermissionableEntitiesCollection::class, GenericPermissionableEntity::createCollection());
        $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, GenericPermissionableEntity::createCollection());
        $this->assertEquals(0, GenericPermissionableEntity::createCollection()->count());
        
        $e1 = new GenericPermissionableEntity('d');
        $e2 = new GenericPermissionableEntity('b');
        $e3 = new GenericPermissionableEntity('c');
        
        $entities = GenericPermissionableEntity::createCollection(
            $e1, $e2, $e3
        );
        
        $this->assertCount(3, $entities);
        $this->assertTrue($entities->has($e1));
        $this->assertTrue($entities->has($e2));
        $this->assertTrue($entities->has($e3));
        
        $entities = GenericPermissionableEntity::createCollection(
            ...[$e1, $e2, $e3]
        );
        
        $this->assertCount(3, $entities);
        $this->assertTrue($entities->has($e1));
        $this->assertTrue($entities->has($e2));
        $this->assertTrue($entities->has($e3));
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testAddParentEntityWorksAsExpected() {
    
        $childEntity = new GenericPermissionableEntity("C");
        
        $parentEntity1 = new GenericPermissionableEntity("A");
        $parentEntity2 = new GenericPermissionableEntity("B");
        
        $this->assertEquals(0, $childEntity->getDirectParents()->count());
        
        // add parent entities and make sure the instance of the object the 
        // addParentEntity method was called on is exactly what is returned
        $this->assertSame($childEntity, $childEntity->addParent($parentEntity1));
        $this->assertEquals(1, $childEntity->getDirectParents()->count());
        
        $this->assertSame($childEntity, $childEntity->addParent($parentEntity2));
        $this->assertEquals(2, $childEntity->getDirectParents()->count());
        
        $this->assertSame($childEntity, $childEntity->addParent($parentEntity1)); // should have no effect trying to add an existing parent
        $this->assertEquals(2, $childEntity->getDirectParents()->count());
        
        $this->assertSame($childEntity, $childEntity->addParent($parentEntity2)); // should have no effect trying to add an existing parent
        $this->assertEquals(2, $childEntity->getDirectParents()->count());
        
        $this->assertTrue($childEntity->getDirectParents()->has($parentEntity1));
        $this->assertTrue($childEntity->getDirectParents()->has($parentEntity2));
        
        /////////////////////////////////////
        // Test ParentCannotBeChildException
        /////////////////////////////////////
        $childEntitysChild = new GenericPermissionableEntity("D");
        $childEntitysChild->addParent($childEntity);
        $exceptionMsg = "Cannot make Entity with id `{$childEntitysChild->getId()}`"
                         . " a parent to Entity with id `{$childEntity->getId()}`."
                         . " Child cannot be parent.";
        $this->expectException(ParentCannotBeChildException::class);
        $this->expectExceptionMessage($exceptionMsg);
        $childEntity->addParent($childEntitysChild); // should throw exception
        
        /////////////////////////////////////////////////////////
        // Test that the correct parents collection gets updated
        // when another parent entity equal to one of the existing 
        // parents is added
        /////////////////////////////////////////////////////////
        $grandParentEntity1 = new GenericPermissionableEntity("AAA");
        $grandParentEntity2 = new GenericPermissionableEntity("BBB");
        
        $parentEntity1->addParent($grandParentEntity1);
        $parentEntity1->addParent($grandParentEntity2);
        
        $parentEntity2->addParent($grandParentEntity1);
        $parentEntity2->addParent($grandParentEntity2);
        
        // At this point, $childEntity has 
        // 2 direct parents:
        //      - $parentEntity1
        //      - $parentEntity2
        //      
        //  2 inherited parents ($childEntity->getAllParentEntities() returns unique parents no duplicates)
        //      - $grandParentEntity1
        //      - $grandParentEntity2
        //
        $this->assertEquals(2, $childEntity->getDirectParents()->count());
        $this->assertEquals(4, $childEntity->getAllParents()->count());
        
        $grandParentEntity1Replacement = new GenericPermissionableEntity("AAA"); // same ID as $grandParentEntity1 but different instance
        $grandParentEntity2Replacement = new GenericPermissionableEntity("BBB"); // same ID as $grandParentEntity2 but different instance
        
        // the two lines below should replace 
        // $grandParentEntity1 & $grandParentEntity2 in both
        // $parentEntity1->getDirectParents() and
        // $parentEntity2->getDirectParents()
        $childEntity->addParent($grandParentEntity1Replacement);
        $childEntity->addParent($grandParentEntity2Replacement);
        
        $keyForGp1InP1 = $parentEntity1->getDirectParents()->getKey($grandParentEntity1Replacement);
        $keyForGp2InP1 = $parentEntity1->getDirectParents()->getKey($grandParentEntity2Replacement);
        
        $keyForGp1InP2 = $parentEntity2->getDirectParents()->getKey($grandParentEntity1Replacement);
        $keyForGp2InP2 = $parentEntity2->getDirectParents()->getKey($grandParentEntity2Replacement);
        
        $this->assertNotNull($keyForGp1InP1);
        $this->assertNotNull($keyForGp2InP1);
        $this->assertNotNull($keyForGp1InP2);
        $this->assertNotNull($keyForGp2InP2);
        
        if ( $keyForGp1InP1 !== null ) {
            
            $this->assertSame($grandParentEntity1Replacement, $parentEntity1->getDirectParents()->get($keyForGp1InP1));
        }
        
        if ( $keyForGp2InP1 !== null ) {
            
            $this->assertSame($grandParentEntity2Replacement, $parentEntity1->getDirectParents()->get($keyForGp2InP1));
        }
        
        if ( $keyForGp1InP2 !== null ) {
            
            $this->assertSame($grandParentEntity1Replacement, $parentEntity2->getDirectParents()->get($keyForGp1InP2));
        }
        
        if ( $keyForGp2InP2 !== null ) {
            
            $this->assertSame($grandParentEntity2Replacement, $parentEntity2->getDirectParents()->get($keyForGp2InP2));
        }
        
        // verify that $grandParentEntity1 & $grandParentEntity2 are 
        // no longer parents of $parentEntity1 and $parentEntity2
        foreach ($parentEntity1->getAllParents() as $pe) {
            
            $this->assertFalse($pe === $grandParentEntity1);
            $this->assertFalse($pe === $grandParentEntity2);
        }
        
        foreach ($parentEntity2->getAllParents() as $pe) {
            
            $this->assertFalse($pe === $grandParentEntity1);
            $this->assertFalse($pe === $grandParentEntity2);
        }
        
        foreach ($parentEntity1->getDirectParents() as $pe) {
            
            $this->assertFalse($pe === $grandParentEntity1);
            $this->assertFalse($pe === $grandParentEntity2);
        }
        
        foreach ($parentEntity2->getDirectParents() as $pe) {
            
            $this->assertFalse($pe === $grandParentEntity1);
            $this->assertFalse($pe === $grandParentEntity2);
        }
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testAddParentEntitiesWorksAsExpected() {
    
        $childEntity = new GenericPermissionableEntity("C");
        
        $parentEntity1 = new GenericPermissionableEntity("A");
        $parentEntity2 = new GenericPermissionableEntity("B");
        $parentEntities = new GenericPermissionableEntitiesCollection(
            $parentEntity1, $parentEntity2
        );
        
        $this->assertEquals(0, $childEntity->getDirectParents()->count());
        
        // add parent entities and make sure the instance of the object the 
        // addParentEntities method was called on is exactly what is returned
        $this->assertSame($childEntity, $childEntity->addParents($parentEntities));
        $this->assertEquals(2, $childEntity->getDirectParents()->count());
                
        $this->assertSame($childEntity, $childEntity->addParents($parentEntities)); // should have no effect trying to add an existing parents
        $this->assertEquals(2, $childEntity->getDirectParents()->count());
        
        $this->assertTrue($childEntity->getDirectParents()->has($parentEntity1));
        $this->assertTrue($childEntity->getDirectParents()->has($parentEntity2));
        
        /////////////////////////////////////
        // Test ParentCannotBeChildException
        /////////////////////////////////////
        $childEntitysChild = new GenericPermissionableEntity("D");
        $parentEntities2 = new GenericPermissionableEntitiesCollection(
            $childEntitysChild
        );
        $childEntitysChild->addParent($childEntity);
        $exceptionMsg = "Cannot make Entity with id `{$childEntitysChild->getId()}`"
                         . " a parent to Entity with id `{$childEntity->getId()}`."
                         . " Child cannot be parent.";
        $this->expectException(ParentCannotBeChildException::class);
        $this->expectExceptionMessage($exceptionMsg);
        $childEntity->addParents($parentEntities2); // should throw exception
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testGetAllParentEntitiesWorksAsExpected() {
    
        $greatGrandFatherEntity = new GenericPermissionableEntity("A");
        $greatGrandMotherEntity = new GenericPermissionableEntity("B");
    
        $grandFatherEntity = new GenericPermissionableEntity("C");
        $grandMotherEntity = new GenericPermissionableEntity("D");
        
        $grandFatherEntity->addParent($greatGrandFatherEntity)
                          ->addParent($greatGrandMotherEntity);
        $grandMotherEntity->addParent($greatGrandFatherEntity)
                          ->addParent($greatGrandMotherEntity);
    
        $fatherEntity = new GenericPermissionableEntity("E");
        $motherEntity = new GenericPermissionableEntity("F");
        
        $fatherEntity->addParent($grandFatherEntity)
                     ->addParent($grandMotherEntity);        
        $motherEntity->addParent($grandFatherEntity)
                     ->addParent($grandMotherEntity);
        
        $childEntity = new GenericPermissionableEntity("G");
        
        $childEntity->addParent($fatherEntity)
                    ->addParent($motherEntity);
        
        $allParentEntities = $childEntity->getAllParents();
        
        $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $allParentEntities);
        $this->assertInstanceOf(GenericPermissionableEntitiesCollection::class, $allParentEntities);
        $this->assertEquals(6, $allParentEntities->count());
        $this->assertFalse($allParentEntities->has($childEntity));
        $this->assertTrue($allParentEntities->has($greatGrandFatherEntity));
        $this->assertTrue($allParentEntities->has($greatGrandMotherEntity));
        $this->assertTrue($allParentEntities->has($grandFatherEntity));
        $this->assertTrue($allParentEntities->has($grandMotherEntity));
        $this->assertTrue($allParentEntities->has($fatherEntity));
        $this->assertTrue($allParentEntities->has($motherEntity));
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testIsChildOfWorksAsExpected() {
    
        $greatGrandFatherEntity = new GenericPermissionableEntity("A");
        $greatGrandMotherEntity = new GenericPermissionableEntity("B");
    
        $grandFatherEntity = new GenericPermissionableEntity("C");
        $grandMotherEntity = new GenericPermissionableEntity("D");
        
        $grandFatherEntity->addParent($greatGrandFatherEntity)
                          ->addParent($greatGrandMotherEntity);
        $grandMotherEntity->addParent($greatGrandFatherEntity)
                          ->addParent($greatGrandMotherEntity);
    
        $fatherEntity = new GenericPermissionableEntity("E");
        $motherEntity = new GenericPermissionableEntity("F");
        
        $fatherEntity->addParent($grandFatherEntity)
                     ->addParent($grandMotherEntity);        
        $motherEntity->addParent($grandFatherEntity)
                     ->addParent($grandMotherEntity);
        
        $childEntity = new GenericPermissionableEntity("G");
        
        $childEntity->addParent($fatherEntity)
                    ->addParent($motherEntity);
        
        $this->assertFalse($childEntity->isChildOf($childEntity));
        $this->assertTrue($childEntity->isChildOf($greatGrandFatherEntity));
        $this->assertTrue($childEntity->isChildOf($greatGrandMotherEntity));
        $this->assertTrue($childEntity->isChildOf($grandFatherEntity));
        $this->assertTrue($childEntity->isChildOf($grandMotherEntity));
        $this->assertTrue($childEntity->isChildOf($fatherEntity));
        $this->assertTrue($childEntity->isChildOf($motherEntity));
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testIsChildOfEntityWithIdWorksAsExpected() {
    
        $greatGrandFatherEntity = new GenericPermissionableEntity("A");
        $greatGrandMotherEntity = new GenericPermissionableEntity("B");
    
        $grandFatherEntity = new GenericPermissionableEntity("C");
        $grandMotherEntity = new GenericPermissionableEntity("D");
        
        $grandFatherEntity->addParent($greatGrandFatherEntity)
                          ->addParent($greatGrandMotherEntity);
        $grandMotherEntity->addParent($greatGrandFatherEntity)
                          ->addParent($greatGrandMotherEntity);
    
        $fatherEntity = new GenericPermissionableEntity("E");
        $motherEntity = new GenericPermissionableEntity("F");
        
        $fatherEntity->addParent($grandFatherEntity)
                     ->addParent($grandMotherEntity);        
        $motherEntity->addParent($grandFatherEntity)
                     ->addParent($grandMotherEntity);
        
        $childEntity = new GenericPermissionableEntity("G");
        
        $childEntity->addParent($fatherEntity)
                    ->addParent($motherEntity);
        
        $this->assertFalse($childEntity->isChildOfEntityWithId('G'));
        $this->assertFalse($childEntity->isChildOfEntityWithId('g'));
        
        $this->assertTrue($childEntity->isChildOfEntityWithId('A'));
        $this->assertTrue($childEntity->isChildOfEntityWithId('a'));
        $this->assertTrue($childEntity->isChildOfEntityWithId('B'));
        $this->assertTrue($childEntity->isChildOfEntityWithId('b'));
        $this->assertTrue($childEntity->isChildOfEntityWithId('C'));
        $this->assertTrue($childEntity->isChildOfEntityWithId('c'));
        $this->assertTrue($childEntity->isChildOfEntityWithId('D'));
        $this->assertTrue($childEntity->isChildOfEntityWithId('d'));
        $this->assertTrue($childEntity->isChildOfEntityWithId('E'));
        $this->assertTrue($childEntity->isChildOfEntityWithId('e'));
        $this->assertTrue($childEntity->isChildOfEntityWithId('F'));
        $this->assertTrue($childEntity->isChildOfEntityWithId('f'));
    }
    
    public function testGetIdWorksAsExpected() {
    
        $entity1 = new GenericPermissionableEntity("A");
        $entity2 = new GenericPermissionableEntity("B");
        
        $this->assertEquals('a', $entity1->getId());
        $this->assertNotEquals('A', $entity1->getId());
        
        $this->assertEquals('b', $entity2->getId());
        $this->assertNotEquals('B', $entity2->getId());
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testGetDirectParentsWorksAsExpected() {
    
        $greatGrandFatherEntity = new GenericPermissionableEntity("A");
        $greatGrandMotherEntity = new GenericPermissionableEntity("B");
    
        $grandFatherEntity = new GenericPermissionableEntity("C");
        $grandMotherEntity = new GenericPermissionableEntity("D");
        
        $grandFatherEntity->addParent($greatGrandFatherEntity)
                          ->addParent($greatGrandMotherEntity);
        $grandMotherEntity->addParent($greatGrandFatherEntity)
                          ->addParent($greatGrandMotherEntity);
    
        $fatherEntity = new GenericPermissionableEntity("E");
        $motherEntity = new GenericPermissionableEntity("F");
        
        $fatherEntity->addParent($grandFatherEntity)
                     ->addParent($grandMotherEntity);        
        $motherEntity->addParent($grandFatherEntity)
                     ->addParent($grandMotherEntity);
        
        $childEntity = new GenericPermissionableEntity("G");
        
        $childEntity->addParent($fatherEntity)
                    ->addParent($motherEntity);
        
        $directParentEntities = $childEntity->getDirectParents();
        
        $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $directParentEntities);
        $this->assertInstanceOf(GenericPermissionableEntitiesCollection::class, $directParentEntities);
        $this->assertEquals(2, $directParentEntities->count());
        $this->assertFalse($directParentEntities->has($childEntity));
        $this->assertFalse($directParentEntities->has($greatGrandFatherEntity));
        $this->assertFalse($directParentEntities->has($greatGrandMotherEntity));
        $this->assertFalse($directParentEntities->has($grandFatherEntity));
        $this->assertFalse($directParentEntities->has($grandMotherEntity));
        $this->assertTrue($directParentEntities->has($fatherEntity));
        $this->assertTrue($directParentEntities->has($motherEntity));
    }
    
    public function testIsEqualToWorksAsExpected() {
    
        $entityOne = new GenericPermissionableEntity("A");
        $entityOneSame = new GenericPermissionableEntity("A");
        $entityTwo = new GenericPermissionableEntity("B");
        $entityTwoSame = new GenericPermissionableEntity("B");
        
        $this->assertTrue( $entityOne->isEqualTo($entityOne));
        $this->assertTrue( $entityOne->isEqualTo($entityOneSame));
        
        $this->assertTrue( $entityOneSame->isEqualTo($entityOne));
        $this->assertTrue( $entityOneSame->isEqualTo($entityOneSame));
        
        $this->assertTrue( $entityTwo->isEqualTo($entityTwo));
        $this->assertTrue( $entityTwo->isEqualTo($entityTwoSame));
        
        $this->assertTrue( $entityTwoSame->isEqualTo($entityTwo));
        $this->assertTrue( $entityTwoSame->isEqualTo($entityTwoSame));
        
        
        $this->assertFalse( $entityOne->isEqualTo($entityTwo));
        $this->assertFalse( $entityOne->isEqualTo($entityTwoSame));
        
        $this->assertFalse( $entityOneSame->isEqualTo($entityTwo));
        $this->assertFalse( $entityOneSame->isEqualTo($entityTwoSame));
        
        $this->assertFalse( $entityTwo->isEqualTo($entityOne));
        $this->assertFalse( $entityTwo->isEqualTo($entityOneSame));
        
        $this->assertFalse( $entityTwoSame->isEqualTo($entityOne));
        $this->assertFalse( $entityTwoSame->isEqualTo($entityOneSame));
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testRemoveParentIfExistsWorksAsExpected() {
    
        $fatherEntity = new GenericPermissionableEntity("A");
        $motherEntity = new GenericPermissionableEntity("B");
        $stepFatherEntity = new GenericPermissionableEntity("C");
        $stepMotherEntity = new GenericPermissionableEntity("D");
        $childEntity = new GenericPermissionableEntity("E");
        
        $childEntity->addParent($fatherEntity)
                    ->addParent($motherEntity)
                    ->addParent($stepFatherEntity)
                    ->addParent($stepMotherEntity);
        
        $directParentEntities = $childEntity->getDirectParents();
        
        $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $directParentEntities);
        $this->assertInstanceOf(GenericPermissionableEntitiesCollection::class, $directParentEntities);
        $this->assertEquals(4, $directParentEntities->count());
        
        $this->assertSame($childEntity, $childEntity->removeParentIfExists($fatherEntity));
        $this->assertEquals(3, $directParentEntities->count());
        $this->assertFalse($directParentEntities->has($fatherEntity));
        
        $this->assertSame($childEntity, $childEntity->removeParentIfExists($fatherEntity)); // no effect
        $this->assertEquals(3, $directParentEntities->count());
        $this->assertFalse($directParentEntities->has($fatherEntity));
        
        $this->assertSame($childEntity, $childEntity->removeParentIfExists($motherEntity));
        $this->assertEquals(2, $directParentEntities->count());
        $this->assertFalse($directParentEntities->has($motherEntity));
        
        $this->assertSame($childEntity, $childEntity->removeParentIfExists($motherEntity)); // no effect
        $this->assertEquals(2, $directParentEntities->count());
        $this->assertFalse($directParentEntities->has($motherEntity));
        
        $this->assertSame($childEntity, $childEntity->removeParentIfExists($stepFatherEntity));
        $this->assertEquals(1, $directParentEntities->count());
        $this->assertFalse($directParentEntities->has($stepFatherEntity));
        
        $this->assertSame($childEntity, $childEntity->removeParentIfExists($stepFatherEntity)); // no effect
        $this->assertEquals(1, $directParentEntities->count());
        $this->assertFalse($directParentEntities->has($stepFatherEntity));
        
        $this->assertSame($childEntity, $childEntity->removeParentIfExists($stepMotherEntity));
        $this->assertEquals(0, $directParentEntities->count());
        $this->assertFalse($directParentEntities->has($stepMotherEntity));
        
        $this->assertSame($childEntity, $childEntity->removeParentIfExists($stepMotherEntity)); // no effect
        $this->assertEquals(0, $directParentEntities->count());
        $this->assertFalse($directParentEntities->has($stepMotherEntity));
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testRemoveParentsThatExistWorksAsExpected() {
    
        $nonParentEntity1 = new GenericPermissionableEntity("Y");
        $nonParentEntity2 = new GenericPermissionableEntity("Z");
        $fatherEntity = new GenericPermissionableEntity("A");
        $motherEntity = new GenericPermissionableEntity("B");
        $stepFatherEntity = new GenericPermissionableEntity("C");
        $stepMotherEntity = new GenericPermissionableEntity("D");
        $childEntity = new GenericPermissionableEntity("E");
        
        $fatherMotherCollection = new GenericPermissionableEntitiesCollection(
            $fatherEntity, $motherEntity
        );
        
        $stepFatherStepMotherCollection = new GenericPermissionableEntitiesCollection(
            $stepFatherEntity, $stepMotherEntity
        );
        
        $nonParentEntitiesCollection = new GenericPermissionableEntitiesCollection(
            $nonParentEntity1, $nonParentEntity2
        );
        
        $childEntity->addParent($fatherEntity)
                    ->addParent($motherEntity)
                    ->addParent($stepFatherEntity)
                    ->addParent($stepMotherEntity);
        
        $directParentEntities = $childEntity->getDirectParents();
        
        $this->assertInstanceOf(PermissionableEntitiesCollectionInterface::class, $directParentEntities);
        $this->assertInstanceOf(GenericPermissionableEntitiesCollection::class, $directParentEntities);
        $this->assertEquals(4, $directParentEntities->count());
        
        $this->assertSame($childEntity, $childEntity->removeParentsThatExist($nonParentEntitiesCollection)); // no effect
        $this->assertEquals(4, $directParentEntities->count());
        $this->assertTrue($directParentEntities->has($fatherEntity));
        $this->assertTrue($directParentEntities->has($motherEntity));
        $this->assertTrue($directParentEntities->has($stepFatherEntity));
        $this->assertTrue($directParentEntities->has($stepMotherEntity));
        
        $this->assertSame($childEntity, $childEntity->removeParentsThatExist($fatherMotherCollection));
        $this->assertEquals(2, $directParentEntities->count());
        $this->assertFalse($directParentEntities->has($fatherEntity));
        $this->assertFalse($directParentEntities->has($motherEntity));
        $this->assertTrue($directParentEntities->has($stepFatherEntity));
        $this->assertTrue($directParentEntities->has($stepMotherEntity));
        
        $this->assertSame($childEntity, $childEntity->removeParentsThatExist($fatherMotherCollection)); // no effect
        $this->assertEquals(2, $directParentEntities->count());
        $this->assertFalse($directParentEntities->has($fatherEntity));
        $this->assertFalse($directParentEntities->has($motherEntity));
        $this->assertTrue($directParentEntities->has($stepFatherEntity));
        $this->assertTrue($directParentEntities->has($stepMotherEntity));
        
        $this->assertSame($childEntity, $childEntity->removeParentsThatExist($stepFatherStepMotherCollection));
        $this->assertEquals(0, $directParentEntities->count());
        $this->assertFalse($directParentEntities->has($fatherEntity));
        $this->assertFalse($directParentEntities->has($motherEntity));
        $this->assertFalse($directParentEntities->has($stepFatherEntity));
        $this->assertFalse($directParentEntities->has($stepMotherEntity));
        
        $this->assertSame($childEntity, $childEntity->removeParentsThatExist($stepFatherStepMotherCollection)); // no effect
        $this->assertEquals(0, $directParentEntities->count());
        $this->assertFalse($directParentEntities->has($fatherEntity));
        $this->assertFalse($directParentEntities->has($motherEntity));
        $this->assertFalse($directParentEntities->has($stepFatherEntity));
        $this->assertFalse($directParentEntities->has($stepMotherEntity));
    }
    
    public function testAddPermissionWorksAsExpected() {
    
        $entity = new GenericPermissionableEntity("A");
        $permission1 = new GenericPermission('action-1', 'resource-1');
        $permission2 = new GenericPermission('action-2', 'resource-2');
        
        $this->assertEquals(0, $entity->getDirectPermissions()->count());
        $this->assertFalse($entity->getDirectPermissions()->hasPermission($permission1));
        $this->assertFalse($entity->getDirectPermissions()->hasPermission($permission2));
        
        $this->assertSame($entity, $entity->addPermission($permission1)); //test fluency
        $this->assertEquals(1, $entity->getDirectPermissions()->count());
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($permission1));
        $this->assertFalse($entity->getDirectPermissions()->hasPermission($permission2));
        
        $this->assertSame($entity, $entity->addPermission($permission2)); //test fluency
        $this->assertEquals(2, $entity->getDirectPermissions()->count());
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($permission1));
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($permission2));
        
        $this->assertSame($entity, $entity->addPermission($permission1)); // no effect
        $this->assertEquals(2, $entity->getDirectPermissions()->count());
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($permission1));
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($permission2));
        
        $this->assertSame($entity, $entity->addPermission($permission2)); // no effect
        $this->assertEquals(2, $entity->getDirectPermissions()->count());
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($permission1));
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($permission2));
        
        /////////////////////////////////////////////////////////////////
        // Test that permissions get updated when new instances that are 
        // equal in value to existing permissions get added
        /////////////////////////////////////////////////////////////////
        $permission1Replacement = new GenericPermission('action-1', 'resource-1');
        $permission2Replacement = new GenericPermission('action-2', 'resource-2');
        
        $entity->addPermission($permission1Replacement); // should replace $permission1
        $entity->addPermission($permission2Replacement); // should replace $permission2
        
        $key1 = $entity->getDirectPermissions()->getKey($permission1Replacement);
        $key2 = $entity->getDirectPermissions()->getKey($permission2Replacement);
        
        $this->assertNotNull($key1);
        $this->assertNotNull($key2);
        
        if( $key1 !== null ) {
            
            $this->assertSame($permission1Replacement, $entity->getDirectPermissions()->get(''.$key1));
        }
        
        if( $key2 !== null ) {
            
            $this->assertSame($permission2Replacement, $entity->getDirectPermissions()->get(''.$key2));
        }
        
        foreach ($entity->getDirectPermissions() as $perm) {
            
            $this->assertFalse($perm === $permission1);
            $this->assertFalse($perm === $permission2);
        }
    }
    
    public function testAddPermissionsWorksAsExpected() {
    
        $entity = new GenericPermissionableEntity("A");
        $permission1 = new GenericPermission('action-1', 'resource-1');
        $permission2 = new GenericPermission('action-2', 'resource-2');
        $permissions = new GenericPermissionsCollection($permission1, $permission2);
        
        $this->assertEquals(0, $entity->getDirectPermissions()->count());
        $this->assertFalse($entity->getDirectPermissions()->hasPermission($permission1));
        $this->assertFalse($entity->getDirectPermissions()->hasPermission($permission2));
        
        $this->assertSame($entity, $entity->addPermissions($permissions)); //test fluency
        $this->assertEquals(2, $entity->getDirectPermissions()->count());
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($permission1));
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($permission2));
        
        $this->assertSame($entity, $entity->addPermissions($permissions)); // no effect
        $this->assertEquals(2, $entity->getDirectPermissions()->count());
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($permission1));
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($permission2));
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testGetDirectPermissionsWorksAsExpected() {
    
        $permission1 = new GenericPermission('action-1', 'resource-1');
        $permission2 = new GenericPermission('action-2', 'resource-2');
        $permissions = new GenericPermissionsCollection($permission1, $permission2);
        
        // direct permissions injected via constructor 
        $entity = new GenericPermissionableEntity("A", $permissions);
        
        $this->assertSame($permissions, $entity->getDirectPermissions());
        $this->assertEquals(2, $entity->getDirectPermissions()->count());
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($permission1));
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($permission2));
        
        // direct permissions injected via setter method addPermissions
        $entity2 = new GenericPermissionableEntity("B");
        $entity2->addPermissions($permissions);
        
        //$this->assertSame($permissions, $entity2->getDirectPermissions()); // They can't be same because default constructor 
                                                                             // first creates an empty permissions collection
        $this->assertEquals(2, $entity2->getDirectPermissions()->count());
        $this->assertTrue($entity2->getDirectPermissions()->hasPermission($permission1));
        $this->assertTrue($entity2->getDirectPermissions()->hasPermission($permission2));
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testGetInheritedPermissionsWorksAsExpected() {
    
        $greatGrandParentPermission = new GenericPermission(
            'action-greatGrandParentPermission', 'resource-greatGrandParentPermission'
        );
        $grandParentPermission = new GenericPermission(
            'action-grandParentPermission', 'resource-grandParentPermission'
        );
        $parentPermission = new GenericPermission(
            'action-parentPermission', 'resource-parentPermission'
        );
    
        $greatGrandParent = new GenericPermissionableEntity('greatGrandParent');
        $grandParent = new GenericPermissionableEntity('grandParent');
        $parent = new GenericPermissionableEntity('parent');
        
        $greatGrandParent->addPermission($greatGrandParentPermission);
        $grandParent->addPermission($grandParentPermission);
        $parent->addPermission($parentPermission);
        
        $grandParent->addParent($greatGrandParent);
        $parent->addParent($grandParent);
        
        $myPermission1 = new GenericPermission('action-1', 'resource-1');
        $myPermission2 = new GenericPermission('action-2', 'resource-2');
        $permissions = new GenericPermissionsCollection($myPermission1, $myPermission2);
        $entity = new GenericPermissionableEntity("A", $permissions);
        $entity->addParent($parent);
        
        $this->assertSame($permissions, $entity->getDirectPermissions());
        $this->assertEquals(2, $entity->getDirectPermissions()->count());
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($myPermission1));
        $this->assertTrue($entity->getDirectPermissions()->hasPermission($myPermission2));
        
        $inheritedPermissions = $entity->getInheritedPermissions();
        $this->assertEquals(3, $inheritedPermissions->count());
        $this->assertTrue($inheritedPermissions->hasPermission($greatGrandParentPermission));
        $this->assertTrue($inheritedPermissions->hasPermission($grandParentPermission));
        $this->assertTrue($inheritedPermissions->hasPermission($parentPermission));
        $this->assertFalse($inheritedPermissions->hasPermission($myPermission1));
        $this->assertFalse($inheritedPermissions->hasPermission($myPermission2));
        
        // test injected collection
        $inheritedPermissionsToInject = new GenericPermissionsCollection();
        $inheritedPermissions = $entity->getInheritedPermissions($inheritedPermissionsToInject);
        $this->assertSame($inheritedPermissionsToInject, $inheritedPermissions);
        $this->assertEquals(3, $inheritedPermissions->count());
        $this->assertEquals(3, $inheritedPermissionsToInject->count());
        $this->assertTrue($inheritedPermissions->hasPermission($greatGrandParentPermission));
        $this->assertTrue($inheritedPermissions->hasPermission($grandParentPermission));
        $this->assertTrue($inheritedPermissions->hasPermission($parentPermission));
        $this->assertFalse($inheritedPermissions->hasPermission($myPermission1));
        $this->assertFalse($inheritedPermissions->hasPermission($myPermission2));
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testGetAllPermissionsWorksAsExpected() {
    
        $greatGrandParentPermission = new GenericPermission(
            'action-greatGrandParentPermission', 'resource-greatGrandParentPermission'
        );
        $grandParentPermission = new GenericPermission(
            'action-grandParentPermission', 'resource-grandParentPermission'
        );
        $parentPermission = new GenericPermission(
            'action-parentPermission', 'resource-parentPermission'
        );
    
        $greatGrandParent = new GenericPermissionableEntity('greatGrandParent');
        $grandParent = new GenericPermissionableEntity('grandParent');
        $parent = new GenericPermissionableEntity('parent');
        
        $greatGrandParent->addPermission($greatGrandParentPermission);
        $grandParent->addPermission($grandParentPermission);
        $parent->addPermission($parentPermission);
        
        $grandParent->addParent($greatGrandParent);
        $parent->addParent($grandParent);
        
        $myPermission1 = new GenericPermission('action-1', 'resource-1');
        $myPermission2 = new GenericPermission('action-2', 'resource-2');
        $permissions = new GenericPermissionsCollection($myPermission1, $myPermission2);
        $entity = new GenericPermissionableEntity("A", $permissions);
        $entity->addParent($parent);
                
        $allPermissions = $entity->getAllPermissions();
        $this->assertEquals(5, $allPermissions->count());
        $this->assertTrue($allPermissions->hasPermission($greatGrandParentPermission));
        $this->assertTrue($allPermissions->hasPermission($grandParentPermission));
        $this->assertTrue($allPermissions->hasPermission($parentPermission));
        $this->assertTrue($allPermissions->hasPermission($myPermission1));
        $this->assertTrue($allPermissions->hasPermission($myPermission2));
        
        // test injected collection
        $inheritedPermissionsToInject = new GenericPermissionsCollection();
        $allPermissions = $entity->getAllPermissions(true, $inheritedPermissionsToInject);
        $this->assertSame($inheritedPermissionsToInject, $allPermissions);
        $this->assertEquals(5, $allPermissions->count());
        $this->assertEquals(5, $inheritedPermissionsToInject->count());
        $this->assertTrue($allPermissions->hasPermission($greatGrandParentPermission));
        $this->assertTrue($allPermissions->hasPermission($grandParentPermission));
        $this->assertTrue($allPermissions->hasPermission($parentPermission));
        $this->assertTrue($allPermissions->hasPermission($myPermission1));
        $this->assertTrue($allPermissions->hasPermission($myPermission2));
        
        // test that direct permissions come first and inherited after
        $this->assertEquals(0, $allPermissions->getKey($myPermission1));
        $this->assertEquals(1, $allPermissions->getKey($myPermission2));
        $this->assertEquals(2, $allPermissions->getKey($parentPermission));
        $this->assertEquals(3, $allPermissions->getKey($grandParentPermission));
        $this->assertEquals(4, $allPermissions->getKey($greatGrandParentPermission));
        
        // test that inherited permissions come first and direct after
        $allPermissions = $entity->getAllPermissions(false);
        $this->assertEquals(3, $allPermissions->getKey($myPermission1));
        $this->assertEquals(4, $allPermissions->getKey($myPermission2));
        $this->assertEquals(0, $allPermissions->getKey($parentPermission));
        $this->assertEquals(1, $allPermissions->getKey($grandParentPermission));
        $this->assertEquals(2, $allPermissions->getKey($greatGrandParentPermission));
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testRemovePermissionIfExistsWorksAsExpected() {
    
        $notMyPermission = new GenericPermission('action-NotMyPermission', 'resource-NotMyPermission');
        $myPermission1 = new GenericPermission('action-1', 'resource-1');
        $myPermission2 = new GenericPermission('action-2', 'resource-2');
        $permissions = new GenericPermissionsCollection($myPermission1, $myPermission2);
        $entity = new GenericPermissionableEntity("A", $permissions);
        
        $this->assertEquals(2, $entity->getDirectPermissions()->count());
        $this->assertEquals(2, $permissions->count());
        
        $this->assertSame($entity, $entity->removePermissionIfExists($notMyPermission)); // no effect
        $this->assertEquals(2, $entity->getDirectPermissions()->count());
        $this->assertEquals(2, $permissions->count());
        
        $this->assertSame($entity, $entity->removePermissionIfExists($myPermission1)); // fluency test
        $this->assertEquals(1, $entity->getDirectPermissions()->count());
        $this->assertEquals(1, $permissions->count());
        
        $this->assertSame($entity, $entity->removePermissionIfExists($myPermission2)); // fluency test
        $this->assertEquals(0, $entity->getDirectPermissions()->count());
        $this->assertEquals(0, $permissions->count());
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testRemovePermissionsThatExistWorksAsExpected() {
    
        $notMyPermission = new GenericPermission('action-NotMyPermission', 'resource-NotMyPermission');
        $notMyPermission2 = new GenericPermission('action-NotMyPermission2', 'resource-NotMyPermission2');
        $notMyPermissions = new GenericPermissionsCollection($notMyPermission, $notMyPermission2);
        
        $myPermission1 = new GenericPermission('action-1', 'resource-1');
        $myPermission2 = new GenericPermission('action-2', 'resource-2');
        $myPermissions = new GenericPermissionsCollection($myPermission1, $myPermission2);
        $entity = new GenericPermissionableEntity("A", $myPermissions);
        
        $this->assertEquals(2, $entity->getDirectPermissions()->count());
        $this->assertEquals(2, $myPermissions->count());
        
        $this->assertSame($entity, $entity->removePermissionsThatExist($notMyPermissions)); // no effect
        $this->assertEquals(2, $entity->getDirectPermissions()->count());
        $this->assertEquals(2, $myPermissions->count());
                
        $this->assertSame($entity, $entity->removePermissionsThatExist($myPermissions)); // fluency test
        $this->assertEquals(0, $entity->getDirectPermissions()->count());
        $this->assertEquals(0, $myPermissions->count());
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function testDumpWorksAsExpected() {
        
        $entityWithDefaultParams = new GenericPermissionableEntity("Z");
        // $entityWithDefaultParams->dump() will display the following except for 000000003bbac6d6000000000fb066f4 & the likes which will vary:
/**
SimpleAcl\GenericPermissionableEntity (000000003bbac6d6000000000fb066f4)
{
        id: `z`
        parentEntities:
                SimpleAcl\GenericPermissionableEntitiesCollection (000000003bbac6d1000000000fb066f4)
                {

                }

        permissions:
                SimpleAcl\GenericPermissionsCollection (000000003bbac6d7000000000fb066f4)
                {

                }


}
*/  
        $haystack = $entityWithDefaultParams->dump();
        $this->assertStringContainsString('SimpleAcl\GenericPermissionableEntity (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        
        $this->assertStringContainsString("\tid: `z`", $haystack);
        
        $this->assertStringContainsString("\tparentEntities:", $haystack);
        $this->assertStringContainsString("\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack);
        $this->assertStringContainsString("\t\t{", $haystack);
        $this->assertStringContainsString("\t\t}", $haystack);
        
        $this->assertStringContainsString("\tpermissions:", $haystack);
        $this->assertStringContainsString("\t\tSimpleAcl\GenericPermissionsCollection (", $haystack);
        $this->assertStringContainsString("\t\t{", $haystack);
        $this->assertStringContainsString("\t\t}", $haystack);
        
        //////////////////////////////////////////////////////////////////////////
        // Test with entity with injected perms and parents with their own perms
        //////////////////////////////////////////////////////////////////////////
        $permissionsForParentA = new GenericPermissionsCollection(
            new GenericPermission('add', 'comment'),
            new GenericPermission('edit', 'comment')
        );
        
        $permissionsForParentB = new GenericPermissionsCollection(
            new GenericPermission('read', 'comment'),
            new GenericPermission('delete', 'comment')
        );
        
        $parentEntities = new GenericPermissionableEntitiesCollection(
            new GenericPermissionableEntity("A", $permissionsForParentA), 
            new GenericPermissionableEntity("B", $permissionsForParentB)
        );
        
        $permissions = new GenericPermissionsCollection(
            new GenericPermission('search', 'comment'),
            new GenericPermission('browse-all', 'comment')
        );
        
        $entityWithInjectedCollections = new GenericPermissionableEntity(
            "D", $permissions, $parentEntities
        );
        
        // $entityWithInjectedCollections->dump() will display the following except for 000000007ded34b9000000007f76b4ca & the likes which will vary:
/**
SimpleAcl\GenericPermissionableEntity (000000007ded34b9000000007f76b4ca)
{
	id: `d`
	parentEntities: 
		SimpleAcl\GenericPermissionableEntitiesCollection (000000007ded34a1000000007f76b4ca)
		{
			item[0]: SimpleAcl\GenericPermissionableEntity (000000007ded34a0000000007f76b4ca)
			{
				id: `a`
				parentEntities: 
					SimpleAcl\GenericPermissionableEntitiesCollection (000000007ded34a3000000007f76b4ca)
					{
					
					}
					
				permissions: 
					SimpleAcl\GenericPermissionsCollection (000000007ded34aa000000007f76b4ca)
					{
						item[0]: SimpleAcl\GenericPermission (000000007ded34ab000000007f76b4ca)
						{
							action: `add`
							resource: `comment`
							allowActionOnResource: true
							additionalAssertions: NULL
							argsForCallback: array (
							)
						
						}
						
						item[1]: SimpleAcl\GenericPermission (000000007ded34ad000000007f76b4ca)
						{
							action: `edit`
							resource: `comment`
							allowActionOnResource: true
							additionalAssertions: NULL
							argsForCallback: array (
							)
						
						}
						
					
					}
					
			
			}
			
			item[1]: SimpleAcl\GenericPermissionableEntity (000000007ded34a2000000007f76b4ca)
			{
				id: `b`
				parentEntities: 
					SimpleAcl\GenericPermissionableEntitiesCollection (000000007ded34a5000000007f76b4ca)
					{
					
					}
					
				permissions: 
					SimpleAcl\GenericPermissionsCollection (000000007ded34ac000000007f76b4ca)
					{
						item[0]: SimpleAcl\GenericPermission (000000007ded34af000000007f76b4ca)
						{
							action: `read`
							resource: `comment`
							allowActionOnResource: true
							additionalAssertions: NULL
							argsForCallback: array (
							)
						
						}
						
						item[1]: SimpleAcl\GenericPermission (000000007ded34ae000000007f76b4ca)
						{
							action: `delete`
							resource: `comment`
							allowActionOnResource: true
							additionalAssertions: NULL
							argsForCallback: array (
							)
						
						}
						
					
					}
					
			
			}
			
		
		}
		
	permissions: 
		SimpleAcl\GenericPermissionsCollection (000000007ded34a4000000007f76b4ca)
		{
			item[0]: SimpleAcl\GenericPermission (000000007ded34a7000000007f76b4ca)
			{
				action: `search`
				resource: `comment`
				allowActionOnResource: true
				additionalAssertions: NULL
				argsForCallback: array (
				)
			
			}
			
			item[1]: SimpleAcl\GenericPermission (000000007ded34a6000000007f76b4ca)
			{
				action: `browse-all`
				resource: `comment`
				allowActionOnResource: true
				additionalAssertions: NULL
				argsForCallback: array (
				)
			
			}
			
		
		}
		

}
*/
        $haystack = $entityWithInjectedCollections->dump();
        $this->assertStringContainsString('SimpleAcl\GenericPermissionableEntity (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        
        $this->assertStringContainsString("\tid: `d`", $haystack);
        $this->assertStringContainsString("\tparentEntities:", $haystack);
        $this->assertStringContainsString("\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack);
        $this->assertStringContainsString("\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\titem[0]: SimpleAcl\GenericPermissionableEntity (", $haystack);
        $this->assertStringContainsString("\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\tid: `a`", $haystack);
        $this->assertStringContainsString("\t\t\t\tparentEntities:", $haystack);
        $this->assertStringContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t".PHP_EOL, $haystack);
        $this->assertStringContainsString("\t\t\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t\t\tpermissions:", $haystack);
        $this->assertStringContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionsCollection (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\titem[0]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\taction: `add`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\t)", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\titem[1]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\taction: `edit`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\t)", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t\titem[1]: SimpleAcl\GenericPermissionableEntity (", $haystack);
        $this->assertStringContainsString("\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\tid: `b`", $haystack);
        $this->assertStringContainsString("\t\t\t\tparentEntities:", $haystack);
        $this->assertStringContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t".PHP_EOL, $haystack);
        $this->assertStringContainsString("\t\t\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t\t\tpermissions:", $haystack);
        $this->assertStringContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionsCollection (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t{", $haystack);
        
        $this->assertStringContainsString("\t\t\t\t\t\titem[0]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\taction: `read`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\t)", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t}", $haystack);
        
        $this->assertStringContainsString("\t\t\t\t\t\titem[1]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\taction: `delete`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\t)", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t}", $haystack);
        
        $this->assertStringContainsString("\t\t\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t}", $haystack);
        
        $this->assertStringContainsString("\tpermissions:", $haystack);
        $this->assertStringContainsString("\t\tSimpleAcl\GenericPermissionsCollection (", $haystack);
        $this->assertStringContainsString("\t\t{", $haystack);
        
        $this->assertStringContainsString("\t\t\titem[0]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringContainsString("\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\taction: `search`", $haystack);
        $this->assertStringContainsString("\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringContainsString("\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t\t\t\t)", $haystack);
        $this->assertStringContainsString("\t\t\t}", $haystack);
        
        $this->assertStringContainsString("\t\t\titem[1]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringContainsString("\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\taction: `browse-all`", $haystack);
        $this->assertStringContainsString("\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringContainsString("\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t\t\t\t)", $haystack);
        $this->assertStringContainsString("\t\t\t}", $haystack);
        
        $this->assertStringContainsString("\t\t}", $haystack);
        
        //// without id
        $haystack = $entityWithInjectedCollections->dump(['id']);
        $this->assertStringNotContainsString("\tid: `d`", $haystack);
        
        //// without parentEntities
        $haystack = $entityWithInjectedCollections->dump(['parentEntities']);
        $this->assertStringNotContainsString("\tparentEntities:", $haystack);
        $this->assertStringNotContainsString("\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack);
        $this->assertStringNotContainsString("\t\t\titem[0]: SimpleAcl\GenericPermissionableEntity (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tid: `a`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tparentEntities:", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tpermissions:", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionsCollection (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\titem[0]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\taction: `add`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\t)", $haystack);

        $this->assertStringNotContainsString("\t\t\t\t\t\titem[1]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\taction: `edit`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\t)", $haystack);
        $this->assertStringNotContainsString("\t\t\titem[1]: SimpleAcl\GenericPermissionableEntity (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tid: `b`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tparentEntities:", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tpermissions:", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionsCollection (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t{", $haystack);
        
        $this->assertStringNotContainsString("\t\t\t\t\t\titem[0]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\taction: `read`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\t)", $haystack);
        
        $this->assertStringNotContainsString("\t\t\t\t\t\titem[1]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\taction: `delete`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\t)", $haystack);
        
        //// without permissions at the root level
        $haystack = $entityWithInjectedCollections->dump(['permissions']);
        
        $this->assertStringNotContainsString(PHP_EOL."\tpermissions:", $haystack);
        $this->assertStringNotContainsString(PHP_EOL."\t\tSimpleAcl\GenericPermissionsCollection (", $haystack);
        
        $this->assertStringNotContainsString(PHP_EOL."\t\t\titem[0]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\taction: `search`", $haystack);
        $this->assertStringNotContainsString(PHP_EOL."\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringNotContainsString(PHP_EOL."\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringNotContainsString(PHP_EOL."\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringNotContainsString(PHP_EOL."\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringNotContainsString(PHP_EOL."\t\t\t\t)", $haystack);
        
        $this->assertStringNotContainsString(PHP_EOL."\t\t\titem[1]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\taction: `browse-all`", $haystack);
        $this->assertStringNotContainsString(PHP_EOL."\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringNotContainsString(PHP_EOL."\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringNotContainsString(PHP_EOL."\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringNotContainsString(PHP_EOL."\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringNotContainsString(PHP_EOL."\t\t\t\t)", $haystack);
        
        //// without 'id','permissions' and 'parentEntities'
        $haystack = $entityWithInjectedCollections->dump(['id','permissions','parentEntities']);
        $this->assertStringContainsString('SimpleAcl\GenericPermissionableEntity (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        
        $this->assertStringNotContainsString("\tid: `d`", $haystack);
        $this->assertStringNotContainsString("\tparentEntities:", $haystack);
        $this->assertStringNotContainsString("\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack);
        $this->assertStringNotContainsString("\t\t{", $haystack);
        $this->assertStringNotContainsString("\t\t\titem[0]: SimpleAcl\GenericPermissionableEntity (", $haystack);
        $this->assertStringNotContainsString("\t\t\t{", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tid: `a`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tparentEntities:", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t{", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t".PHP_EOL, $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t}", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tpermissions:", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionsCollection (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t{", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\titem[0]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t{", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\taction: `add`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\t)", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t}", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\titem[1]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t{", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\taction: `edit`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\t)", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t}", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t}", $haystack);
        $this->assertStringNotContainsString("\t\t\t}", $haystack);
        $this->assertStringNotContainsString("\t\t\titem[1]: SimpleAcl\GenericPermissionableEntity (", $haystack);
        $this->assertStringNotContainsString("\t\t\t{", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tid: `b`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tparentEntities:", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t{", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t".PHP_EOL, $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t}", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tpermissions:", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionsCollection (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t{", $haystack);
        
        $this->assertStringNotContainsString("\t\t\t\t\t\titem[0]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t{", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\taction: `read`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\t)", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t}", $haystack);
        
        $this->assertStringNotContainsString("\t\t\t\t\t\titem[1]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t{", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\taction: `delete`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t\t)", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t\t\t}", $haystack);
        
        $this->assertStringNotContainsString("\t\t\t\t\t}", $haystack);
        $this->assertStringNotContainsString("\t\t\t}", $haystack);
        $this->assertStringNotContainsString("\t\t}", $haystack);
        
        $this->assertStringNotContainsString("\tpermissions:", $haystack);
        $this->assertStringNotContainsString("\t\tSimpleAcl\GenericPermissionsCollection (", $haystack);
        $this->assertStringNotContainsString("\t\t{", $haystack);
        
        $this->assertStringNotContainsString("\t\t\titem[0]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringNotContainsString("\t\t\t{", $haystack);
        $this->assertStringNotContainsString("\t\t\t\taction: `search`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringNotContainsString("\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t)", $haystack);
        $this->assertStringNotContainsString("\t\t\t}", $haystack);
        
        $this->assertStringNotContainsString("\t\t\titem[1]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringNotContainsString("\t\t\t{", $haystack);
        $this->assertStringNotContainsString("\t\t\t\taction: `browse-all`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringNotContainsString("\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringNotContainsString("\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringNotContainsString("\t\t\t\t)", $haystack);
        $this->assertStringNotContainsString("\t\t\t}", $haystack);
        
        $this->assertStringNotContainsString("\t\t}", $haystack);
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function test__toStringWorksAsExpected() {
        
        $entityWithDefaultParams = new GenericPermissionableEntity("Z");
        // $entityWithDefaultParams->__toString() will display the following except for 000000003bbac6d6000000000fb066f4 & the likes which will vary:
/**
SimpleAcl\GenericPermissionableEntity (000000003bbac6d6000000000fb066f4)
{
        id: `z`
        parentEntities:
                SimpleAcl\GenericPermissionableEntitiesCollection (000000003bbac6d1000000000fb066f4)
                {

                }

        permissions:
                SimpleAcl\GenericPermissionsCollection (000000003bbac6d7000000000fb066f4)
                {

                }


}
*/  
        $haystack = $entityWithDefaultParams->__toString();
        $this->assertStringContainsString('SimpleAcl\GenericPermissionableEntity (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        
        $this->assertStringContainsString("\tid: `z`", $haystack);
        
        $this->assertStringContainsString("\tparentEntities:", $haystack);
        $this->assertStringContainsString("\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack);
        $this->assertStringContainsString("\t\t{", $haystack);
        $this->assertStringContainsString("\t\t}", $haystack);
        
        $this->assertStringContainsString("\tpermissions:", $haystack);
        $this->assertStringContainsString("\t\tSimpleAcl\GenericPermissionsCollection (", $haystack);
        $this->assertStringContainsString("\t\t{", $haystack);
        $this->assertStringContainsString("\t\t}", $haystack);
        
        //////////////////////////////////////////////////////////////////////////
        // Test with entity with injected perms and parents with their own perms
        //////////////////////////////////////////////////////////////////////////
        $permissionsForParentA = new GenericPermissionsCollection(
            new GenericPermission('add', 'comment'),
            new GenericPermission('edit', 'comment')
        );
        
        $permissionsForParentB = new GenericPermissionsCollection(
            new GenericPermission('read', 'comment'),
            new GenericPermission('delete', 'comment')
        );
        
        $parentEntities = new GenericPermissionableEntitiesCollection(
            new GenericPermissionableEntity("A", $permissionsForParentA), 
            new GenericPermissionableEntity("B", $permissionsForParentB)
        );
        
        $permissions = new GenericPermissionsCollection(
            new GenericPermission('search', 'comment'),
            new GenericPermission('browse-all', 'comment')
        );
        
        $entityWithInjectedCollections = new GenericPermissionableEntity(
            "D", $permissions, $parentEntities
        );
        
        // $entityWithInjectedCollections->__toString() will display the following except for 000000007ded34b9000000007f76b4ca & the likes which will vary:
/**
SimpleAcl\GenericPermissionableEntity (000000007ded34b9000000007f76b4ca)
{
	id: `d`
	parentEntities: 
		SimpleAcl\GenericPermissionableEntitiesCollection (000000007ded34a1000000007f76b4ca)
		{
			item[0]: SimpleAcl\GenericPermissionableEntity (000000007ded34a0000000007f76b4ca)
			{
				id: `a`
				parentEntities: 
					SimpleAcl\GenericPermissionableEntitiesCollection (000000007ded34a3000000007f76b4ca)
					{
					
					}
					
				permissions: 
					SimpleAcl\GenericPermissionsCollection (000000007ded34aa000000007f76b4ca)
					{
						item[0]: SimpleAcl\GenericPermission (000000007ded34ab000000007f76b4ca)
						{
							action: `add`
							resource: `comment`
							allowActionOnResource: true
							additionalAssertions: NULL
							argsForCallback: array (
							)
						
						}
						
						item[1]: SimpleAcl\GenericPermission (000000007ded34ad000000007f76b4ca)
						{
							action: `edit`
							resource: `comment`
							allowActionOnResource: true
							additionalAssertions: NULL
							argsForCallback: array (
							)
						
						}
						
					
					}
					
			
			}
			
			item[1]: SimpleAcl\GenericPermissionableEntity (000000007ded34a2000000007f76b4ca)
			{
				id: `b`
				parentEntities: 
					SimpleAcl\GenericPermissionableEntitiesCollection (000000007ded34a5000000007f76b4ca)
					{
					
					}
					
				permissions: 
					SimpleAcl\GenericPermissionsCollection (000000007ded34ac000000007f76b4ca)
					{
						item[0]: SimpleAcl\GenericPermission (000000007ded34af000000007f76b4ca)
						{
							action: `read`
							resource: `comment`
							allowActionOnResource: true
							additionalAssertions: NULL
							argsForCallback: array (
							)
						
						}
						
						item[1]: SimpleAcl\GenericPermission (000000007ded34ae000000007f76b4ca)
						{
							action: `delete`
							resource: `comment`
							allowActionOnResource: true
							additionalAssertions: NULL
							argsForCallback: array (
							)
						
						}
						
					
					}
					
			
			}
			
		
		}
		
	permissions: 
		SimpleAcl\GenericPermissionsCollection (000000007ded34a4000000007f76b4ca)
		{
			item[0]: SimpleAcl\GenericPermission (000000007ded34a7000000007f76b4ca)
			{
				action: `search`
				resource: `comment`
				allowActionOnResource: true
				additionalAssertions: NULL
				argsForCallback: array (
				)
			
			}
			
			item[1]: SimpleAcl\GenericPermission (000000007ded34a6000000007f76b4ca)
			{
				action: `browse-all`
				resource: `comment`
				allowActionOnResource: true
				additionalAssertions: NULL
				argsForCallback: array (
				)
			
			}
			
		
		}
		

}
*/
        $haystack = $entityWithInjectedCollections->__toString();
        $this->assertStringContainsString('SimpleAcl\GenericPermissionableEntity (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        
        $this->assertStringContainsString("\tid: `d`", $haystack);
        $this->assertStringContainsString("\tparentEntities:", $haystack);
        $this->assertStringContainsString("\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack);
        $this->assertStringContainsString("\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\titem[0]: SimpleAcl\GenericPermissionableEntity (", $haystack);
        $this->assertStringContainsString("\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\tid: `a`", $haystack);
        $this->assertStringContainsString("\t\t\t\tparentEntities:", $haystack);
        $this->assertStringContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t".PHP_EOL, $haystack);
        $this->assertStringContainsString("\t\t\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t\t\tpermissions:", $haystack);
        $this->assertStringContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionsCollection (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\titem[0]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\taction: `add`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\t)", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\titem[1]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\taction: `edit`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\t)", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t\titem[1]: SimpleAcl\GenericPermissionableEntity (", $haystack);
        $this->assertStringContainsString("\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\tid: `b`", $haystack);
        $this->assertStringContainsString("\t\t\t\tparentEntities:", $haystack);
        $this->assertStringContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t".PHP_EOL, $haystack);
        $this->assertStringContainsString("\t\t\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t\t\tpermissions:", $haystack);
        $this->assertStringContainsString("\t\t\t\t\tSimpleAcl\GenericPermissionsCollection (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t{", $haystack);
        
        $this->assertStringContainsString("\t\t\t\t\t\titem[0]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\taction: `read`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\t)", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t}", $haystack);
        
        $this->assertStringContainsString("\t\t\t\t\t\titem[1]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\taction: `delete`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t\t)", $haystack);
        $this->assertStringContainsString("\t\t\t\t\t\t}", $haystack);
        
        $this->assertStringContainsString("\t\t\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t\t}", $haystack);
        $this->assertStringContainsString("\t\t}", $haystack);
        
        $this->assertStringContainsString("\tpermissions:", $haystack);
        $this->assertStringContainsString("\t\tSimpleAcl\GenericPermissionsCollection (", $haystack);
        $this->assertStringContainsString("\t\t{", $haystack);
        
        $this->assertStringContainsString("\t\t\titem[0]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringContainsString("\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\taction: `search`", $haystack);
        $this->assertStringContainsString("\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringContainsString("\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t\t\t\t)", $haystack);
        $this->assertStringContainsString("\t\t\t}", $haystack);
        
        $this->assertStringContainsString("\t\t\titem[1]: SimpleAcl\GenericPermission (", $haystack);
        $this->assertStringContainsString("\t\t\t{", $haystack);
        $this->assertStringContainsString("\t\t\t\taction: `browse-all`", $haystack);
        $this->assertStringContainsString("\t\t\t\tresource: `comment`", $haystack);
        $this->assertStringContainsString("\t\t\t\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\t\t\t\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\t\t\t\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t\t\t\t)", $haystack);
        $this->assertStringContainsString("\t\t\t}", $haystack);
        
        $this->assertStringContainsString("\t\t}", $haystack);
    }
}
