<?php
declare(strict_types=1);

use \SimpleAcl\GenericPermissionableEntity;
use \SimpleAcl\GenericPermissionableEntitiesCollection;

/**
 * Description of GenericBaseCollectionTest
 *
 * @author rotimi
 */
class GenericPermissionableEntitiesCollectionTest extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void { 
        
        parent::setUp();
    }
    
    public function testConstructorWorksAsExcpected() {
        
        // no args
        $collection = new GenericPermissionableEntitiesCollection();
        $this->assertEquals($collection->count(), 0);
        
        $entities = [
            new GenericPermissionableEntity('entity-a'),
            new GenericPermissionableEntity('entity-b'),
            new GenericPermissionableEntity('entity-c'),
        ];
        
        // args with array unpacking
        $collection2 = new GenericPermissionableEntitiesCollection(...$entities);
        $this->assertEquals($collection2->count(), 3);
        $this->assertTrue($collection2->hasEntity($entities[0]));
        $this->assertTrue($collection2->hasEntity($entities[1]));
        $this->assertTrue($collection2->hasEntity($entities[2]));
        
        // multiple args non-array unpacking
        $collection3 = new GenericPermissionableEntitiesCollection(
            $entities[0], $entities[1], $entities[2]
        );
        $this->assertEquals($collection3->count(), 3);
        $this->assertTrue($collection3->hasEntity($entities[0]));
        $this->assertTrue($collection3->hasEntity($entities[1]));
        $this->assertTrue($collection3->hasEntity($entities[2]));
    }
    
    public function testHasEntityWorksAsExcpected() {
        
        $entities = [
            new GenericPermissionableEntity('entity-a'),
            new GenericPermissionableEntity('entity-b'),
            new GenericPermissionableEntity('entity-c'),
        ];
        
        $entity4 = new GenericPermissionableEntity('entity-d');
        $entity5 = new GenericPermissionableEntity('entity-e');
        
        // empty collection
        $collection = new GenericPermissionableEntitiesCollection();
        $this->assertFalse($collection->hasEntity($entities[0]));
        $this->assertFalse($collection->hasEntity($entities[1]));
        $this->assertFalse($collection->hasEntity($entities[2]));
        $this->assertFalse($collection->hasEntity($entity4));
        $this->assertFalse($collection->hasEntity($entity5));
        
        // non-empty collection
        $collection2 = new GenericPermissionableEntitiesCollection(...$entities);
        $this->assertEquals($collection2->count(), 3);
        $this->assertTrue($collection2->hasEntity($entities[0]));
        $this->assertTrue($collection2->hasEntity($entities[1]));
        $this->assertTrue($collection2->hasEntity($entities[2]));
        $this->assertFalse($collection2->hasEntity($entity4));
        $this->assertFalse($collection2->hasEntity($entity5));
        $collection2->add($entity4);
        $collection2->add($entity5);
        $this->assertTrue($collection2->hasEntity($entity4));
        $this->assertTrue($collection2->hasEntity($entity5));
    }
    
    public function testAddWorksAsExcpected() {
        
        $entities = [
            new GenericPermissionableEntity('entity-a'),
            new GenericPermissionableEntity('entity-b'),
            new GenericPermissionableEntity('entity-c'),
        ];
        
        $entity4 = new GenericPermissionableEntity('entity-d');
        $entity5 = new GenericPermissionableEntity('entity-e');
        
        // empty collection
        $collection = new GenericPermissionableEntitiesCollection();
        $collection->add($entities[0]);
        $collection->add($entities[1]);
        $collection->add($entities[2]);
        $collection->add($entity4);
        $collection->add($entity5);
        $this->assertTrue($collection->hasEntity($entities[0]));
        $this->assertTrue($collection->hasEntity($entities[1]));
        $this->assertTrue($collection->hasEntity($entities[2]));
        $this->assertTrue($collection->hasEntity($entity4));
        $this->assertTrue($collection->hasEntity($entity5));
        
        // non-empty collection
        $collection2 = new GenericPermissionableEntitiesCollection(...$entities);
        $collection2->add($entity4);
        $collection2->add($entity5);
        $this->assertTrue($collection2->hasEntity($entity4));
        $this->assertTrue($collection2->hasEntity($entity5));
    }
    
    public function testGetKeyWorksAsExcpected() {
        
        $entities = [
            new GenericPermissionableEntity('entity-a'),
            new GenericPermissionableEntity('entity-b'),
            new GenericPermissionableEntity('entity-c'),
        ];        
        $entity4 = new GenericPermissionableEntity('entity-d');
        $entity5 = new GenericPermissionableEntity('entity-e');
        $nonExistentEntity = new GenericPermissionableEntity('entity-f');

        // non-empty collection
        $collection = new GenericPermissionableEntitiesCollection(...$entities);
        $collection->add($entity4);
        $collection->add($entity5);
        
        $this->assertEquals($collection->getKey($entities[0]), 0);
        $this->assertEquals($collection->getKey($entities[1]), 1);
        $this->assertEquals($collection->getKey($entities[2]), 2);
        $this->assertEquals($collection->getKey($entity4), 3);
        $this->assertEquals($collection->getKey($entity5), 4);
        $this->assertEquals($collection->getKey($nonExistentEntity), null);
    }
    
    public function testRemoveWorksAsExcpected() {
        
        $entities = [
            new GenericPermissionableEntity('entity-a'),
            new GenericPermissionableEntity('entity-b'),
            new GenericPermissionableEntity('entity-c'),
        ];        
        $entity4 = new GenericPermissionableEntity('entity-d');
        $entity5 = new GenericPermissionableEntity('entity-e');
        $nonExistentEntity = new GenericPermissionableEntity('entity-f');

        // non-empty collection
        $collection = new GenericPermissionableEntitiesCollection(...$entities);
        $collection->add($entity4);
        $collection->add($entity5);
        
        $this->assertEquals($collection->count(), 5);
        $collection->remove($nonExistentEntity);
        $this->assertEquals($collection->count(), 5);
        
        $this->assertTrue($collection->hasEntity($entities[0]));
        $this->assertSame($collection->remove($entities[0]), $collection);
        $this->assertFalse($collection->hasEntity($entities[0]));
        $this->assertEquals($collection->count(), 4);
        
        $this->assertTrue($collection->hasEntity($entities[1]));
        $this->assertSame($collection->remove($entities[1]), $collection);
        $this->assertFalse($collection->hasEntity($entities[1]));
        $this->assertEquals($collection->count(), 3);
        
        $this->assertTrue($collection->hasEntity($entities[2]));
        $this->assertSame($collection->remove($entities[2]), $collection);
        $this->assertFalse($collection->hasEntity($entities[2]));
        $this->assertEquals($collection->count(), 2);
        
        $this->assertTrue($collection->hasEntity($entity4));
        $this->assertSame($collection->remove($entity4), $collection);
        $this->assertFalse($collection->hasEntity($entity4));
        $this->assertEquals($collection->count(), 1);
        
        $this->assertTrue($collection->hasEntity($entity5));
        $this->assertSame($collection->remove($entity5), $collection);
        $this->assertFalse($collection->hasEntity($entity5));
        $this->assertEquals($collection->count(), 0);
    }
    
    public function testRemoveAllWorksAsExcpected() {
        
        $entities = [
            new GenericPermissionableEntity('entity-a'),
            new GenericPermissionableEntity('entity-b'),
            new GenericPermissionableEntity('entity-c'),
        ];        
        $entity4 = new GenericPermissionableEntity('entity-d');
        $entity5 = new GenericPermissionableEntity('entity-e');

        // non-empty collection
        $collection = new GenericPermissionableEntitiesCollection(...$entities);
        $collection->add($entity4);
        $collection->add($entity5);
        
        $this->assertEquals($collection->count(), 5);
        $collection->removeAll();
        $this->assertEquals($collection->count(), 0);
        
        $this->assertFalse($collection->hasEntity($entities[0]));
        $this->assertFalse($collection->hasEntity($entities[1]));
        $this->assertFalse($collection->hasEntity($entities[2]));
        $this->assertFalse($collection->hasEntity($entity4));
        $this->assertFalse($collection->hasEntity($entity5));
    }
    
    /////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////
    // Test methods inherited from \SimpleAcl\GenericBaseCollection
    /////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////
    public function testGetIteratorWorksAsExcpected() {
        
        $collection = new GenericPermissionableEntitiesCollection();
        $this->assertInstanceOf(\Traversable::class, $collection->getIterator());
    }

    public function testRemoveByKeyWorksAsExcpected() {
        
        $collection = new GenericPermissionableEntitiesCollection();
        
        $entity1 = new GenericPermissionableEntity('one');
        $entity2 = new GenericPermissionableEntity('two');
        $entity3 = new GenericPermissionableEntity('three');
        
        $collection->add($entity1);
        $collection->add($entity2);
        $collection->add($entity3);
        
        $this->assertNull($collection->removeByKey('non-existent-key'));
        $this->assertEquals($entity1, $collection->removeByKey(0));
        $this->assertEquals($entity2, $collection->removeByKey(1));
        $this->assertEquals($entity3, $collection->removeByKey(2));
    }

    public function testKeyExistsWorksAsExcpected() {
        
        $collection = new GenericPermissionableEntitiesCollection();
        
        $entity1 = new GenericPermissionableEntity('one');
        $entity2 = new GenericPermissionableEntity('two');
        $entity3 = new GenericPermissionableEntity('three');
        
        $collection->add($entity1);
        $collection->add($entity2);
        $collection->add($entity3);
        
        $this->assertFalse($collection->keyExists('non-existent-key'));
        $this->assertFalse($collection->keyExists([]));
        $this->assertFalse($collection->keyExists(777));
        $this->assertTrue($collection->keyExists(0));
        $this->assertTrue($collection->keyExists(1));
        $this->assertTrue($collection->keyExists(2));
    }

    public function testCountWorksAsExcpected() {
        
        $collection = new GenericPermissionableEntitiesCollection();
        
        $entity1 = new GenericPermissionableEntity('one');
        $entity2 = new GenericPermissionableEntity('two');
        $entity3 = new GenericPermissionableEntity('three');
        
        $this->assertEquals($collection->count(), 0);
        
        $collection->add($entity1);
        $collection->add($entity2);
        $collection->add($entity3);
        
        $this->assertEquals($collection->count(), 3);
    }

    public function testDumpWorksAsExcpected() {
        
        $collection = new GenericPermissionableEntitiesCollection();
        
        $entity1 = new GenericPermissionableEntity('one');
        $entity2 = new GenericPermissionableEntity('two');
        $entity3 = new GenericPermissionableEntity('three');
        
        $haystack = $collection->dump();
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        
        $collection->add($entity1);
        $collection->add($entity2);
        $collection->add($entity3);
        
        $haystack1 = $collection->dump();
        $this->assertStringContainsString('{', $haystack1);
        
        $this->assertStringContainsString("\titem[0]: SimpleAcl\GenericPermissionableEntity (", $haystack1);
        $this->assertStringContainsString("\t{", $haystack1);
        $this->assertStringContainsString("\t\tid: `one`", $haystack1);
        $this->assertStringContainsString("\t\tparentEntities:", $haystack1);
        $this->assertStringContainsString("\t\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack1);
        $this->assertStringContainsString("\t\t\t{", $haystack1);
        $this->assertStringContainsString("\t\t\t}", $haystack1);
        $this->assertStringContainsString("\t\tpermissions:", $haystack1);
        $this->assertStringContainsString("\t\t\tSimpleAcl\GenericPermissionsCollection (", $haystack1);
        $this->assertStringContainsString("\t\t\t{", $haystack1);
        $this->assertStringContainsString("\t\t\t}", $haystack1);
        $this->assertStringContainsString("\t}", $haystack1);
        
        $this->assertStringContainsString("\titem[1]: SimpleAcl\GenericPermissionableEntity (", $haystack1);
        $this->assertStringContainsString("\t\tid: `two`", $haystack1);
        
        $this->assertStringContainsString("\titem[2]: SimpleAcl\GenericPermissionableEntity (", $haystack1);
        $this->assertStringContainsString("\t\tid: `three`", $haystack1);
        
        $this->assertStringContainsString('}', $haystack1);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        $haystack2 = $collection->dump(['storage']);
        $this->assertStringContainsString('{', $haystack2);
        $this->assertStringContainsString('}', $haystack2);
        
        $this->assertStringNotContainsString("\titem[0]: SimpleAcl\GenericPermissionableEntity (", $haystack2);
        $this->assertStringNotContainsString("\t{", $haystack2);
        $this->assertStringNotContainsString("\t\tid: `one`", $haystack2);
        $this->assertStringNotContainsString("\t\tparentEntities:", $haystack2);
        $this->assertStringNotContainsString("\t\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack2);
        $this->assertStringNotContainsString("\t\t\t{", $haystack2);
        $this->assertStringNotContainsString("\t\t\t}", $haystack2);
        $this->assertStringNotContainsString("\t\tpermissions:", $haystack2);
        $this->assertStringNotContainsString("\t\t\tSimpleAcl\GenericPermissionsCollection (", $haystack2);
        $this->assertStringNotContainsString("\t\t\t{", $haystack2);
        $this->assertStringNotContainsString("\t\t\t}", $haystack2);
        $this->assertStringNotContainsString("\t}", $haystack2);
        
        $this->assertStringNotContainsString("\titem[1]: SimpleAcl\GenericPermissionableEntity (", $haystack2);
        $this->assertStringNotContainsString("\t\tid: `two`", $haystack2);
        
        $this->assertStringNotContainsString("\titem[2]: SimpleAcl\GenericPermissionableEntity (", $haystack2);
        $this->assertStringNotContainsString("\t\tid: `three`", $haystack2);
    }

    public function test__toStringWorksAsExcpected() {
        
        $collection = new GenericPermissionableEntitiesCollection();
        
        $entity1 = new GenericPermissionableEntity('one');
        $entity2 = new GenericPermissionableEntity('two');
        $entity3 = new GenericPermissionableEntity('three');
        
        $haystack = $collection->__toString();
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        
        $collection->add($entity1);
        $collection->add($entity2);
        $collection->add($entity3);
        
        $haystack1 = $collection->__toString();
        $this->assertStringContainsString('{', $haystack1);
        
        $this->assertStringContainsString("\titem[0]: SimpleAcl\GenericPermissionableEntity (", $haystack1);
        $this->assertStringContainsString("\t{", $haystack1);
        $this->assertStringContainsString("\t\tid: `one`", $haystack1);
        $this->assertStringContainsString("\t\tparentEntities:", $haystack1);
        $this->assertStringContainsString("\t\t\tSimpleAcl\GenericPermissionableEntitiesCollection (", $haystack1);
        $this->assertStringContainsString("\t\t\t{", $haystack1);
        $this->assertStringContainsString("\t\t\t}", $haystack1);
        $this->assertStringContainsString("\t\tpermissions:", $haystack1);
        $this->assertStringContainsString("\t\t\tSimpleAcl\GenericPermissionsCollection (", $haystack1);
        $this->assertStringContainsString("\t\t\t{", $haystack1);
        $this->assertStringContainsString("\t\t\t}", $haystack1);
        $this->assertStringContainsString("\t}", $haystack1);
        
        $this->assertStringContainsString("\titem[1]: SimpleAcl\GenericPermissionableEntity (", $haystack1);
        $this->assertStringContainsString("\t\tid: `two`", $haystack1);
        
        $this->assertStringContainsString("\titem[2]: SimpleAcl\GenericPermissionableEntity (", $haystack1);
        $this->assertStringContainsString("\t\tid: `three`", $haystack1);
        
        $this->assertStringContainsString('}', $haystack1);
    }
}
