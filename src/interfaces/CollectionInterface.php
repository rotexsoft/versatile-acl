<?php
declare(strict_types=1);

namespace SimpleAcl;


interface CollectionInterface extends \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * CollectionInterface constructor.
     * @param mixed ...$items the items to be contained in an instance of this interface. Implementers must enforce that items are of the same type.
     *
     * @throws \SimpleAcl\Exceptions\InvalidItemTypeException if items are not all of the same type
     */
    public function __construct(...$items);
}