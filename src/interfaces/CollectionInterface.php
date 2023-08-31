<?php
declare(strict_types=1);

namespace VersatileAcl\Interfaces;

use Countable;
use IteratorAggregate;

/**
 * @psalm-suppress MissingTemplateParam
 */
interface CollectionInterface extends Countable, IteratorAggregate {
    
    /**
     * Remove an item with the specified key from the collection if it exists and return the removed item or null if it doesn't exist.
     *
     * @param string|int $key
     *
     * @return mixed the removed item
     */
    public function removeByKey($key);
    
    /**
     * Check if specified key exists in the collection.
     *
     * @param string|int $key
     */
    public function keyExists($key): bool;

    /**
     * Return string representation of an instance of this interface
     */
    public function __toString(): string;
    
    public function count(): int;
    
    public function getIterator(): \Traversable;
}
