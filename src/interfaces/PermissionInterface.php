<?php
declare(strict_types=1);

namespace SimpleAcl;


interface PermissionInterface
{
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
    public function __construct(string $action, string $resource, bool $allowActionOnResource=true, callable $additionalAssertions=null, ...$argsForCallback);
}
