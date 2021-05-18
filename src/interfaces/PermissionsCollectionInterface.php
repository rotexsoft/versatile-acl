<?php
declare(strict_types=1);

namespace VersatileAcl\Interfaces;


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
     * Duplicate PermissionInterface instances should not be allowed in the same instance of this interface.
     *
     *
     */
    public function add(PermissionInterface $permission): self;
    
    /**
     * Adds an instance of PermissionInterface to an instance of this interface with the specified key.
     *
     * @param string $key specified key for $permission in the collection
     *
     */
    public function put(PermissionInterface $permission, string $key): self;
    
    /**
     * Removes an instance of PermissionInterface from an instance of this interface.
     *
     *
     */
    public function remove(PermissionInterface $permission): self;
    
    /**
     * Remove all items in the collection and return $this
     */
    public function removeAll(): self;
    
    /**
     * Retrieves the permission in the collection associated with the specified collection key.
     * If the key is not present in the collection, NULL should be returned
     *
     * @param string $key a key in the collection instance this method is being called on
     */
    public function get(string $key): ?PermissionInterface;
    
    /**
     * Retrieves the key in the collection associated with the specified permission object.
     * If the object is not present in the collection, NULL should be returned
     *
     *
     * @return string|int|null
     */
    public function getKey(PermissionInterface $permission);
    
    /**
     * Checks whether or not a permission exists in the current instance.
     *
     * `$permission` is present in the current instance if there is another permission `$x`
     * in the current instance where $x->isEqualTo($permission) === true.
     *
     *
     * @return bool true if there is another permission `$x` in the current instance where $x->isEqualTo($permission) === true, otherwise return false
     */
    public function hasPermission(PermissionInterface $permission): bool;

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
     * @param callable|null $comparator has the following signature:
     *                  function( PermissionInterface $a, PermissionInterface $b ) : int
     *                      The comparison function must return an integer less than,
     *                      equal to, or greater than zero if the first argument is
     *                      considered to be respectively less than, equal to,
     *                      or greater than the second.
     */
    public function sort(callable $comparator=null): self;
    
    /**
     * Find and return the first permission in the collection that matches the specified $action and / or $resource.
     * NULL should be returned if there was no match.
     * The match should be case-insensitive
     * If $action has an empty string value, do not use it for the match
     * If $resource has an empty string value, do not use it for the match
     * If both $action & $resource have empty string values, return NULL
     *
     *
     */
    public function findOne(string $action='', string $resource=''): ?PermissionInterface;
    
    /**
     * Find and return the all permissions in the collection that match the specified $action and / or $resource.
     * An empty collection should be returned if there was no match.
     * The match should be case-insensitive
     * If $action has an empty string value, do not use it for the match
     * If $resource has an empty string value, do not use it for the match
     * If both $action & $resource have empty string values, return an empty collection
     *
     *
     */
    public function findAll(string $action='', string $resource=''): PermissionsCollectionInterface;
}
