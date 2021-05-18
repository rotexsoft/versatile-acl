<?php
declare(strict_types=1);

namespace VersatileAcl;

use ArrayIterator;
use VersatileAcl\Interfaces\CollectionInterface;
use Traversable;
use function array_key_exists;
use function count;
use function in_array;
use function is_int;
use function is_string;
use function spl_object_hash;
use function str_replace;

abstract class GenericBaseCollection implements CollectionInterface {

    /**
     * 
     * @var array
     * 
     */
    protected $storage = [];
    
    /**
     * Retrieve an external iterator
     *
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     */
    public function getIterator(): Traversable {
        
        return new ArrayIterator($this->storage);
    }

    /**
     * Remove an item with the specified key from the collection if it exists and return the removed item or null if it doesn't exist.
     *
     * @param string|int $key
     *
     * @return mixed the removed item
     */
    public function removeByKey($key) {
        
        $item = null;
        
        if($this->keyExists($key)) {
            
            $item = $this->storage[$key];
            unset($this->storage[$key]);
        }
        return $item;
    }
    
    /**
     * Check if specified key exists in the collection.
     *
     * @param string|int $key
     *
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    public function keyExists($key): bool {
        
        if(is_int($key) || is_string($key)){
            
            return array_key_exists($key, $this->storage);
        }
        return false;
    }

    /**
     * Count elements of an object
     * 
     * @link https://php.net/manual/en/countable.count.php
     * 
     * @return int The custom count as an integer.
     */
    public function count(): int {
        
        return count($this->storage);
    }   
    
    public function __toString(): string {
        
        return $this->dump();
    }
    
    public function dump(array $propertiesToExcludeFromDump=[]): string {

        $objAsStr = static::class .' ('. spl_object_hash($this) . ')' . PHP_EOL . '{' . PHP_EOL;
        
        if( !in_array('storage', $propertiesToExcludeFromDump) ) {
            foreach ($this->storage as $key => $item) {
                // $item will either be a permission or entity object which both have __toString()
                $objAsStr .=  "\t"."item[{$key}]: " . str_replace(PHP_EOL, PHP_EOL."\t", ((string)$item))  . PHP_EOL;
            }
        }
        
        return $objAsStr . (PHP_EOL . "}" . PHP_EOL);
    }
}
