<?php
declare(strict_types=1);
namespace SimpleAcl;

/**
 *
 * Generate a (screen/user)-friendly string representation of a variable.
 *
 * @param mixed $var
 *
 * @return string a (screen / user)-friendly string representation of a variable
 *
 */
function var_to_string($var): string {

    return var_export($var, true);
}

/**
 *
 * Generate a (screen/user)-friendly string representation of a variable and print it out to the screen.
 *
 * @param mixed $var
 *
 * @return void
 *
 */
function dump_var($var): void {

    $line_breaker = (php_sapi_name() === 'cli') ? PHP_EOL : '<br>';
    echo var_to_string($var). $line_breaker . $line_breaker;
}
