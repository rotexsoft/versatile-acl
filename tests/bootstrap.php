<?php

use Psalm\Storage\Assertion\IsGreaterThanOrEqualTo;

error_reporting(E_ALL | E_STRICT);

require_once dirname(__DIR__).DIRECTORY_SEPARATOR.'vendor/autoload.php';

function isRunningOnGreaterThanOrEqualToPhp82() {
    
    return PHP_MAJOR_VERSION >= 8 && PHP_MINOR_VERSION >= 2;
}
