<?php
declare(strict_types=1);

namespace SimpleAcl;

use SimpleAcl\Interfaces\PermissionInterface;
use SimpleAcl\Interfaces\PermissionsCollectionInterface;

class GenericPermission implements PermissionInterface
{
    /**
     * @var string
     */
    protected $action = '';

    /**
     * @var string
     */
    protected $resource = '';

    /**
     * @var bool
     */
    protected $allowActionOnResource = true;

    /**
     * @var callable
     */
    protected $additionalAssertions = null;

    /**
     * @var array
     */
    protected $argsForCallback = [];

    /**
     * PermissionInterface constructor.
     *
     * Both $allowActionOnResource and the return value of $additionalAssertions (if specified) must be true
     * in order for the specified action to be regarded as performable on the resource.
     *
     * @param string $action a string representing an action that can be performed on a resource in the system
     * @param string $resource a string representing a resource in the system
     * @param bool $allowActionOnResource a boolean flag indicating whether or not the specified action can be performed on the resource
     * @param callable|null $additionalAssertions an optional callback function that must return a boolean further indicating whether or not an action can be performed on the resource.
     * @param mixed ...$argsForCallback zero or more arguments to be used to invoke $additionalAssertions
     */
    public function __construct(string $action, string $resource, bool $allowActionOnResource = true, callable $additionalAssertions = null, ...$argsForCallback)
    {
        $this->action = $action;
        $this->resource = $resource;
        $this->allowActionOnResource = $allowActionOnResource;
        $this->additionalAssertions = $additionalAssertions;
        $this->argsForCallback = $argsForCallback;
    }

    /**
     * Create a new and empty collection that is meant to house one or more instances of PermissionInterface
     *
     * @return PermissionsCollectionInterface a new and empty collection that is meant to house one or more instances of PermissionInterface
     */
    public static function createCollection(): PermissionsCollectionInterface
    {
        return new GenericPermissionsCollection();
    }

    /**
     * Get the string representing an action that can be performed on a resource in the system.
     *
     * Its value should be retrieved from the first argument passed to the constructor.
     *
     * @return string a string representing an action that can be performed on a resource in the system
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Get a string value that represents all actions that can be performed on all resources in the system.
     *
     * It should have a unique value that does not conflict with other strings representing existing actions in the system / your application.
     *
     * This should be a wild-card value and it is up to the implementer of this interface to choose what that value should be.
     * The wild-card `*` character is recommended.
     *
     * @return string a string value that represents all actions that can be performed on all resources in the system.
     */
    public static function getAllActionsIdentifier(): string
    {
        return '*';
    }

    /**
     * Get the string representing a resources in the system.
     *
     * Its value should be retrieved from the second argument passed to the constructor.
     *
     * @return string a string representing a resource in the system
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * Get a string value that represents all resources in the system.
     *
     * It should have a unique value that does not conflict with other strings representing existing resources in the system / your application.
     *
     * This should be a wild-card value and it is up to the implementer of this interface to choose what that value should be.
     * The wild-card `*` character is recommended.
     *
     * @return string a string value that represents all resources in the system.
     */
    public static function getAllResoucesIdentifier(): string
    {
        return '*';
    }

    /**
     * Get the boolean value indicating whether or not an instance of this interface signifies that an action can be performed on a resource.
     *
     * The value should be retrieved from the third argument passed to the constructor or the value subsequently set via calls to setAllowActionOnResource(..).
     *
     * @return bool a boolean value indicating whether or not an instance of this interface signifies that an action can be performed on a resource.
     */
    public function getAllowActionOnResource(): bool
    {
        $this->allowActionOnResource;
    }

    /**
     * Set the boolean value indicating whether or not an instance of this interface signifies that an action can be performed on a resource to true or false.
     *
     * @param bool $allowActionOnResource a boolean value indicating whether or not an instance of this interface signifies that an action can be performed on a resource
     * @return $this
     */
    public function setAllowActionOnResource(bool $allowActionOnResource): PermissionInterface
    {
        $this->allowActionOnResource = $allowActionOnResource;

        return $this;
    }

    /**
     * Calculates and returns a boolean value indicating whether or not an instance of this interface signifies that a specified action can be performed on a specified resource.
     *
     * This method should return true (signifying that the specified action is regarded as performable on the specified resource) only if:
     *  - $this->getAction() === $action
     *  - and $this->getResource() === $resource
     *  - and $this->getAllowActionOnResource() === true
     *  - and if supplied, executing $additionalAssertions with $argsForCallback as arguments must also return true
     *
     * @param string $action an action to check whether or not it can be performed on the specified resource
     * @param string $resource a resource we are testing whether an action can be performed on
     * @param callable|null $additionalAssertions an optional callback function with additional tests to check whether the specified action can be performed on the specified resource.
     *                                            The callback must return true if the specified action can be performed on the specified resource.
     * @param mixed ...$argsForCallback optional arguments that may be required by the $additionalAssertions callback
     * @return bool return true if an instance of this interface signifies that a specified action can be performed on a specified resource, or false otherwise
     */
    public function isActionAllowedOnResource(string $action, string $resource, callable $additionalAssertions = null, ...$argsForCallback): bool
    {
        return $this->getAction() === $action
            && $this->getResource() === $resource
            && $this->getAllowActionOnResource() === true
            && ( (is_null($additionalAssertions)) ? true : (call_user_func_array($additionalAssertions, $argsForCallback) === true) );
    }

    /**
     * Checks whether the specified permission object has an equal value to the current instance.
     *
     * It is up to the implementer of this method to define what criteria makes two permission objects equal.
     * This implementation considers 2 instances ($x and $y) of PermissionInterface as equal if
     * $x->getAction() === $y->getAction() && $x->getResource() === $y->getResource()
     *
     * @param PermissionInterface $permission
     * @return bool
     */
    public function isEqualTo(PermissionInterface $permission): bool
    {
        return $this->getAction() === $permission->getAction()
                && $this->getResource() === $permission->getResource();
    }
}
