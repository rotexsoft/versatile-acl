<?php
declare(strict_types=1);

namespace SimpleAcl;

use SimpleAcl\Interfaces\CollectionInterface;
use Traversable;
use VersatileCollections\StrictlyTypedCollectionInterface;
use VersatileCollections\StrictlyTypedCollectionInterfaceImplementationTrait;

abstract class GenericBaseCollection implements CollectionInterface {

    /**
     * @var \VersatileCollections\StrictlyTypedCollectionInterface
     */
    protected $storage = null;
}
