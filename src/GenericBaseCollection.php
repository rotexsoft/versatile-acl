<?php
declare(strict_types=1);

namespace SimpleAcl;

use SimpleAcl\Interfaces\CollectionInterface;
use Traversable;

abstract class GenericBaseCollection implements CollectionInterface {

    /**
     * @var array
     */
    protected $storage = [];
    
    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing Iterator or Traversable
     */
    public function getIterator() {
        
        return new \ArrayIterator($this->storage);
    }

    /**
     * 
     * @param string|int $key
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
     * 
     * @param string|int $key
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
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *
     * The return value is cast to an integer.
     */
    public function count(): int {
        
        return (int)count($this->storage);
    }
    
    
    public function __toString() {
        
        $objAsStr = static::class .' ('. spl_object_hash($this) . ')' . PHP_EOL . '{' . PHP_EOL;
        
        foreach ($this->storage as $key => $item) {

            $objAsStr .= "\t"."item[{$key}]: " . str_replace(PHP_EOL, PHP_EOL."\t", ''.$item)  . PHP_EOL;
        }

        $objAsStr .= PHP_EOL . "}" . PHP_EOL;
        
        return $objAsStr;
    }
}
