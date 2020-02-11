<?php
declare(strict_types=1);

use \SimpleAcl\GenericBaseCollection;

/**
 * Description of GenericBaseCollectionTest
 *
 * @author rotimi
 */
class GenericBaseCollectionTest extends \PHPUnit\Framework\TestCase {
    
    protected function setUp(): void { 
        
        parent::setUp();
    }
    
    public function testGetIteratorWorksAsExcpected() {
        
        $collection = new class extends GenericBaseCollection { };
        $this->assertInstanceOf(\Traversable::class, $collection->getIterator());
    }

    public function testRemoveByKeyWorksAsExcpected() {
        
        $collection = new class extends GenericBaseCollection { 
            
            public function add($item) {
                
                $this->storage[] = $item;
            }
        };
        
        $collection->add('one');
        $collection->add('two');
        $collection->add('three');
        
        $this->assertNull($collection->removeByKey('non-existent-key'));
        $this->assertEquals('one', $collection->removeByKey(0));
        $this->assertEquals('two', $collection->removeByKey(1));
        $this->assertEquals('three', $collection->removeByKey(2));
    }

    public function testKeyExistsWorksAsExcpected() {
        
        $collection = new class extends GenericBaseCollection { 
            
            public function add($item) {
                
                $this->storage[] = $item;
            }
        };
        
        $collection->add('one');
        $collection->add('two');
        $collection->add('three');
        
        $this->assertFalse($collection->keyExists('non-existent-key'));
        $this->assertFalse($collection->keyExists([]));
        $this->assertFalse($collection->keyExists(777));
        $this->assertTrue($collection->keyExists(0));
        $this->assertTrue($collection->keyExists(1));
        $this->assertTrue($collection->keyExists(2));
    }

    public function testCountWorksAsExcpected() {
        
        $collection = new class extends GenericBaseCollection { 
            
            public function add($item) {
                
                $this->storage[] = $item;
            }
        };
        
        $this->assertEquals($collection->count(), 0);
        
        $collection->add('one');
        $collection->add('two');
        $collection->add('three');
        
        $this->assertEquals($collection->count(), 3);
    }

    public function testDumpWorksAsExcpected() {
        
        $collection = new class extends GenericBaseCollection { 

            public function add($item) {

                $this->storage[] = $item;
            }
        };
        
        $haystack = $collection->dump();
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        
        $collection->add('one');
        $collection->add('two');
        $collection->add('three');
        
        $haystack1 = $collection->dump();
        $this->assertStringContainsString('{', $haystack1);
        $this->assertStringContainsString('}', $haystack1);
        $this->assertStringContainsString("\titem[0]: one", $haystack1);
        $this->assertStringContainsString("\titem[1]: two", $haystack1);
        $this->assertStringContainsString("\titem[2]: three", $haystack1);
        
        $haystack2 = $collection->dump(['storage']);
        $this->assertStringContainsString('{', $haystack2);
        $this->assertStringContainsString('}', $haystack2);
        $this->assertStringNotContainsString("\titem[0]: one", $haystack2);
        $this->assertStringNotContainsString("\titem[1]: two", $haystack2);
        $this->assertStringNotContainsString("\titem[2]: three", $haystack2);
    }

    public function test__toStringWorksAsExcpected() {
        
        $collection = new class extends GenericBaseCollection { 

            public function add($item) {

                $this->storage[] = $item;
            }
        };
        
        $haystack = $collection->__toString();
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        
        $collection->add('one');
        $collection->add('two');
        $collection->add('three');
        
        $haystack1 = $collection->__toString();
        
        $this->assertStringContainsString('{', $haystack1);
        $this->assertStringContainsString('}', $haystack1);
        $this->assertStringContainsString("\titem[0]: one", $haystack1);
        $this->assertStringContainsString("\titem[1]: two", $haystack1);
        $this->assertStringContainsString("\titem[2]: three", $haystack1);
    }
}
