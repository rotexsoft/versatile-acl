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
    public function getIterator()
    {
        return new \ArrayIterator($this->storage);
    }

    /**
     * 
     * @param string|int $key
     */
    public function removeByKey($key) {
        
        if($this->keyExists($key)) {
            
            $this->storage[$key] = null;
            unset($this->storage[$key]);
        }
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
    public function count(): int
    {
        return (int)count($this->storage);
    }
}
