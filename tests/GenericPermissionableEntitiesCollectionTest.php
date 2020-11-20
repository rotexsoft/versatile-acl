<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

use \SimpleAcl\GenericPermissionableEntity;
use \SimpleAcl\GenericPermissionableEntitiesCollection;
use \SimpleAcl\Interfaces\PermissionableEntityInterface;

/**
 * Description of GenericBaseCollectionTest
 *
 * @author rotimi
 */
class GenericPermissionableEntitiesCollectionTest extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void { 
        
        parent::setUp();
    }
    
    public function testConstructorWorksAsExpected() {
        
        // no args
        $collection = new GenericPermissionableEntitiesCollection();
        $this->assertEquals(0, $collection->count());
        
        $entities = [
            new GenericPermissionableEntity('entity-a'),
            new GenericPermissionableEntity('entity-b'),
            new GenericPermissionableEntity('entity-c'),
        ];
        
        // args with array unpacking
        $collection2 = new GenericPermissionableEntitiesCollection(...$entities);
        $this->assertEquals(3, $collection2->count());
        $this->assertTrue($collection2->hasEntity($entities[0]));
        $this->assertTrue($collection2->hasEntity($entities[1]));
        $this->assertTrue($collection2->hasEntity($entities[2]));
        
        // multiple args non-array unpacking
        $collection3 = new GenericPermissionableEntitiesCollection(
            $entities[0], $entities[1], $entities[2]
        );
        $this->assertEquals(3, $collection3->count());
        $this->assertTrue($collection3->hasEntity($entities[0]));
        $this->assertTrue($collection3->hasEntity($entities[1]));
        $this->assertTrue($collection3->hasEntity($entities[2]));
    }
    
    public function testHasEntityWorksAsExpected() {
        
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
        $this->assertEquals(3, $collection2->count());
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
    
    public function testAddWorksAsExpected() {
        
        $entities = [
            new GenericPermissionableEntity('entity-a'),
            new GenericPermissionableEntity('entity-b'),
            new GenericPermissionableEntity('entity-c'),
        ];
        
        $entity4 = new GenericPermissionableEntity('entity-d');
        $entity5 = new GenericPermissionableEntity('entity-e');
        
        // empty collection
        $collection = new GenericPermissionableEntitiesCollection();
        
        $this->assertSame($collection, $collection->add($entities[0])); // test fluent return
        $this->assertSame($collection, $collection->add($entities[1])); // test fluent return
        $this->assertSame($collection, $collection->add($entities[2])); // test fluent return
        $this->assertSame($collection, $collection->add($entity4)); // test fluent return
        $this->assertSame($collection, $collection->add($entity5)); // test fluent return
        
        $this->assertTrue($collection->hasEntity($entities[0]));
        $this->assertTrue($collection->hasEntity($entities[1]));
        $this->assertTrue($collection->hasEntity($entities[2]));
        $this->assertTrue($collection->hasEntity($entity4));
        $this->assertTrue($collection->hasEntity($entity5));
        
        // non-empty collection
        $collection2 = new GenericPermissionableEntitiesCollection(...$entities);
        
        $this->assertSame($collection2, $collection2->add($entity4)); // test fluent return
        $this->assertSame($collection2, $collection2->add($entity5)); // test fluent return
        
        $this->assertTrue($collection2->hasEntity($entity4));
        $this->assertTrue($collection2->hasEntity($entity5));
        
        // test that duplicates are not added
        $expectedCount = $collection2->count();
        $collection2->add($entity5); // add duplicate
        $this->assertCount($expectedCount, $collection2);
    }
    
    public function testPutWorksAsExpected() {
        
        $entities = [
            new GenericPermissionableEntity('entity-a'),
            new GenericPermissionableEntity('entity-b'),
            new GenericPermissionableEntity('entity-c'),
        ];
        
        $entity4 = new GenericPermissionableEntity('entity-d');
        $entity5 = new GenericPermissionableEntity('entity-e');
        
        // empty collection
        $collection = new GenericPermissionableEntitiesCollection();
        
        $this->assertSame($collection, $collection->put($entities[0], '1')); // test fluent return
        $this->assertSame($collection, $collection->put($entities[1], '2')); // test fluent return
        $this->assertSame($collection, $collection->put($entities[2], '3')); // test fluent return
        $this->assertSame($collection, $collection->put($entity4, '4')); // test fluent return
        $this->assertSame($collection, $collection->put($entity5, '5')); // test fluent return
        
        $this->assertTrue($collection->hasEntity($entities[0]));
        $this->assertTrue($collection->getKey($entities[0]).'' === '1' );
        
        $this->assertTrue($collection->hasEntity($entities[1]));
        $this->assertTrue($collection->getKey($entities[1]).'' === '2' );
        
        $this->assertTrue($collection->hasEntity($entities[2]));
        $this->assertTrue($collection->getKey($entities[2]).'' === '3' );
        
        $this->assertTrue($collection->hasEntity($entity4));
        $this->assertTrue($collection->getKey($entity4).'' === '4' );
        
        $this->assertTrue($collection->hasEntity($entity5));
        $this->assertTrue($collection->getKey($entity5).'' === '5' );
        
        // non-empty collection
        $collection2 = new GenericPermissionableEntitiesCollection(...$entities);
        
        $this->assertSame($collection2, $collection2->put($entity4, '44')); // test fluent return
        $this->assertSame($collection2, $collection2->put($entity5, '55')); // test fluent return
        
        $this->assertTrue($collection2->hasEntity($entity4));
        $this->assertTrue($collection2->getKey($entity4).'' === '44' );
        
        $this->assertTrue($collection2->hasEntity($entity5));
        $this->assertTrue($collection2->getKey($entity5).'' === '55' );
    }
    
    public function testGetWorksAsExpected() {
        
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
        
        $this->assertSame($collection->get('0'), $entities[0]);
        $this->assertSame($collection->get('1'), $entities[1]);
        $this->assertSame($collection->get('2'), $entities[2]);
        $this->assertSame($collection->get('3'), $entity4);
        $this->assertSame($collection->get('4'), $entity5);
        $this->assertNull($collection->get('777'));
    }
    
    public function testGetKeyWorksAsExpected() {
        
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
        
        $this->assertEquals(0, $collection->getKey($entities[0]));
        $this->assertEquals(1, $collection->getKey($entities[1]));
        $this->assertEquals(2, $collection->getKey($entities[2]));
        $this->assertEquals(3, $collection->getKey($entity4));
        $this->assertEquals(4, $collection->getKey($entity5));
        $this->assertEquals(null, $collection->getKey($nonExistentEntity));
    }
    
    public function testRemoveWorksAsExpected() {
        
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
        
        $this->assertEquals(5, $collection->count());
        $collection->remove($nonExistentEntity);
        $this->assertEquals(5, $collection->count());
        
        $this->assertTrue($collection->hasEntity($entities[0]));
        $this->assertSame($collection->remove($entities[0]), $collection);
        $this->assertFalse($collection->hasEntity($entities[0]));
        $this->assertEquals(4, $collection->count());
        
        $this->assertTrue($collection->hasEntity($entities[1]));
        $this->assertSame($collection->remove($entities[1]), $collection);
        $this->assertFalse($collection->hasEntity($entities[1]));
        $this->assertEquals(3, $collection->count());
        
        $this->assertTrue($collection->hasEntity($entities[2]));
        $this->assertSame($collection->remove($entities[2]), $collection);
        $this->assertFalse($collection->hasEntity($entities[2]));
        $this->assertEquals(2, $collection->count());
        
        $this->assertTrue($collection->hasEntity($entity4));
        $this->assertSame($collection->remove($entity4), $collection);
        $this->assertFalse($collection->hasEntity($entity4));
        $this->assertEquals(1, $collection->count());
        
        $this->assertTrue($collection->hasEntity($entity5));
        $this->assertSame($collection->remove($entity5), $collection);
        $this->assertFalse($collection->hasEntity($entity5));
        $this->assertEquals(0, $collection->count());
    }
    
    public function testRemoveAllWorksAsExpected() {
        
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
        
        $this->assertEquals(5, $collection->count());
        $collection->removeAll();
        $this->assertEquals(0, $collection->count());
        
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
    public function testGetIteratorWorksAsExpected() {
        
        $collection = new GenericPermissionableEntitiesCollection();
        $this->assertInstanceOf(Traversable::class, $collection->getIterator());
    }

    public function testRemoveByKeyWorksAsExpected() {
        
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

    public function testKeyExistsWorksAsExpected() {
        
        $collection = new GenericPermissionableEntitiesCollection();
        
        $entity1 = new GenericPermissionableEntity('one');
        $entity2 = new GenericPermissionableEntity('two');
        $entity3 = new GenericPermissionableEntity('three');
        
        $collection->add($entity1);
        $collection->add($entity2);
        $collection->add($entity3);
        
        $this->assertFalse($collection->keyExists('non-existent-key'));
        /** @noinspection PhpParamsInspection */
        $this->assertFalse($collection->keyExists([]));
        $this->assertFalse($collection->keyExists(777));
        $this->assertTrue($collection->keyExists(0));
        $this->assertTrue($collection->keyExists(1));
        $this->assertTrue($collection->keyExists(2));
    }

    public function testCountWorksAsExpected() {
        
        $collection = new GenericPermissionableEntitiesCollection();
        
        $entity1 = new GenericPermissionableEntity('one');
        $entity2 = new GenericPermissionableEntity('two');
        $entity3 = new GenericPermissionableEntity('three');
        
        $this->assertEquals(0, $collection->count());
        
        $collection->add($entity1);
        $collection->add($entity2);
        $collection->add($entity3);
        
        $this->assertEquals(3, $collection->count());
    }

    public function testSortWorksAsExpected() {
        
        $entities = new GenericPermissionableEntitiesCollection(
            new GenericPermissionableEntity('c'),
            new GenericPermissionableEntity('b'),
            new GenericPermissionableEntity('a'), 
            new GenericPermissionableEntity('a') 
        );
        $sortedIds = ['a', 'a', 'b', 'c'];
        
        // default sort test
        $entities->sort();
        
        foreach ($entities as $entity) {
            
            $this->assertTrue( $entity->getId() === array_shift($sortedIds) );
        }
        
        ////////////////////////////////////////////////////////
        // reverse sort with a specified callback
        ////////////////////////////////////////////////////////
        $entities2 = new GenericPermissionableEntitiesCollection(
            new GenericPermissionableEntity('a'),
            new GenericPermissionableEntity('a'),
            new GenericPermissionableEntity('b'), 
            new GenericPermissionableEntity('c') 
        );
        $sortedIds2 = ['c', 'b', 'a', 'a'];
        
        $comparator = function( PermissionableEntityInterface $a, PermissionableEntityInterface $b ) : int {

            if( $a->getId() < $b->getId() ) {

                return 1;

            } else if( $a->getId() === $b->getId() ) {

                return 0;
            }

            return -1;
        };
        
        // sort with specified callback test
        $entities2->sort($comparator);
        
        foreach ($entities2 as $entity) {
            
            $this->assertTrue( $entity->getId() === array_shift($sortedIds2) );
        }
    }

    public function testDumpWorksAsExpected() {
        
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

    public function test__toStringWorksAsExpected() {
        
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
