<?php
declare(strict_types=1);

namespace SimpleAcl\Interfaces;

interface CollectionInterface extends \Countable, \IteratorAggregate
{
    /**
     * 
     * @param string|int $key
     */
    public function removeByKey($key);
    
    /**
     * 
     * @param string|int $key
     * @return bool
     */
    public function keyExists($key): bool;
}