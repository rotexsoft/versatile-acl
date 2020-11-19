<?php
declare(strict_types=1);

namespace SimpleAcl;

use ArrayIterator;
use SimpleAcl\Interfaces\CollectionInterface;
use Traversable;

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
     * 
     * @return Traversable An instance of an object implementing Iterator or Traversable
     */
    public function getIterator() {
        
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
     * @return bool
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
        
        return (int)count($this->storage);
    }   
    
    public function __toString(): string {
        
        return $this->dump();
    }
    
    public function dump(array $propertiesToExcludeFromDump=[]): string {
        
        static $propertiesToExcludeFromDumpAcrossAllInstances;
        
        if(!$propertiesToExcludeFromDumpAcrossAllInstances) {
            
            $propertiesToExcludeFromDumpAcrossAllInstances = [];
        }
        
        if(
            !isset($propertiesToExcludeFromDumpAcrossAllInstances[static::class]) // first call
            || 
            (
                $propertiesToExcludeFromDumpAcrossAllInstances[static::class] !== $propertiesToExcludeFromDump
            ) // handle multiple calls with different values of $propertiesToExcludeFromDump
        ) {
            $propertiesToExcludeFromDumpAcrossAllInstances[static::class] = $propertiesToExcludeFromDump;
        }
        
        $propertiesToExcludeFromThisCall = $propertiesToExcludeFromDumpAcrossAllInstances[static::class];

        $objAsStr = static::class .' ('. spl_object_hash($this) . ')' . PHP_EOL . '{' . PHP_EOL;
        
        if( !in_array('storage', $propertiesToExcludeFromThisCall) ) {
            foreach ($this->storage as $key => $item) {

                $objAsStr .=  "\t"."item[{$key}]: " . str_replace(PHP_EOL, PHP_EOL."\t", ''.$item)  . PHP_EOL;
            }
        }
        
        $objAsStr .= PHP_EOL . "}" . PHP_EOL;
        
        return $objAsStr;
    }
}
