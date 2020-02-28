<?php
declare(strict_types=1);

namespace SimpleAcl\Interfaces;


interface PermissionsCollectionInterface extends CollectionInterface
{
    /**
     * Constructor.
     * 
     * @param mixed ...$permissions zero or more instances of PermissionInterface to be added to this collection
     *
     */
    public function __construct(PermissionInterface ...$permissions);
    
    /**
     * Adds an instance of PermissionInterface to an instance of this interface.
     * 
     * @param \SimpleAcl\Interfaces\PermissionInterface $permission
     * 
     * @return $this
     */
    public function add(PermissionInterface $permission): self;
    
    /**
     * Adds an instance of PermissionInterface to an instance of this interface with the specified key.
     * 
     * @param \SimpleAcl\Interfaces\PermissionInterface $permission
     * @param string $key specified key for $permission in the collection
     * 
     * @return $this
     */
    public function put(PermissionInterface $permission, string $key): self;
    
    /**
     * Removes an instance of PermissionInterface from an instance of this interface.
     * 
     * @param \SimpleAcl\Interfaces\PermissionInterface $permission
     * 
     * @return $this
     */
    public function remove(PermissionInterface $permission): self;
    
    /**
     * Remove all items in the collection and return $this
     * 
     * @return $this
     */
    public function removeAll(): self;
    
    /**
     * Retrieves the permission in the collection associated with the specified key.
     * If the key is not present in the collection, NULL should be returned
     * 
     * @param string $key
     * 
     * @return \SimpleAcl\Interfaces\PermissionInterface|null
     */
    public function get(string $key): ?PermissionInterface;
    
    /**
     * Retrieves the key in the collection associated with the specified permission object.
     * If the object is not present in the collection, NULL should be returned
     * 
     * @param \SimpleAcl\Interfaces\PermissionInterface $permission
     * 
     * @return string|int|null
     */
    public function getKey(PermissionInterface $permission);
    
    /**
     * Checks whether or not a permission exists in the current instance.
     *
     * `$perm` is present in the current instance if there is another permission `$x`
     * in the current instance where $x->isEqualTo($perm) === true.
     *
     * @param PermissionInterface $perm
     * 
     * @return bool true if there is another permission `$x` in the current instance where $x->isEqualTo($perm) === true, otherwise return false
     */
    public function hasPermission(PermissionInterface $perm): bool;

    /**
     * Calculates and returns a boolean value indicating whether or not one or more items in an instance of this interface
     * signifies that a specified action can be performed on a specified resource.
     *
     * This method should return true (signifying that the specified action is regarded as performable on the specified resource) only if:
     *  - an item `$x` exists in an instance of this interface
     *  - and $x->getAction() === $action
     *  - and $x->getResource() === $resource
     *  - and $x->getAllowActionOnResource() === true
     *  - and if supplied, executing $additionalAssertions with $argsForCallback as arguments must also return true
     *
     * @param string $action an action to check whether or not it can be performed on the specified resource
     * @param string $resource a resource we are testing whether an action can be performed on
     * @param callable|null $additionalAssertions an optional callback function with additional tests to check whether the specified action can be performed on the specified resource.
     *                                            The callback must return true if the specified action can be performed on the specified resource.
     * @param mixed ...$argsForCallback optional arguments that may be required by the $additionalAssertions callback
     * 
     * @return bool return true if one or more items in an instance of this interface signifies that a specified action can be performed on a specified resource, or false otherwise
     */
    public function isAllowed(string $action, string $resource, callable $additionalAssertions=null, ...$argsForCallback): bool;
    
    /**
     * Sort the collection. 
     * If specified, use the callback to compare items in the collection when sorting or 
     * sort according to some default criteria (up to the implementer of this method to
     * specify what that criteria is).
     * 
     * @param callable $comparator has the following signature:
     *                  function( PermissionInterface $a, PermissionInterface $b ) : int
     *                      The comparison function must return an integer less than, 
     *                      equal to, or greater than zero if the first argument is 
     *                      considered to be respectively less than, equal to, 
     *                      or greater than the second. 
     *                  
     * @return $this
     */
    public function sort(callable $comparator=null): self;
}
