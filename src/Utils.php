<?php
declare(strict_types=1);
namespace SimpleAcl;

use Closure;
use function function_exists;
use function strtolower;

/**
 * A bunch of static helper methods used by this package
 *
 * @author Rotimi
 */
class Utils {

    /**
     * Converts a callable to an instance of \Closure
     * 
     * @param callable $callable
     * 
     * @return Closure
     */
    public static function getClosureFromCallable(callable $callable): Closure {

        return ($callable instanceof Closure)? $callable : Closure::fromCallable($callable);
    }
    
    /**
     * Lowers the case of all characters in a string. Uses \mb_strtolower if available in UTF-8 mode
     * 
     * @param string $str
     * 
     * @return string
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function strToLower(string $str): string {
          
        if( function_exists('mb_strtolower') ) {

            return \mb_strtolower($str, 'UTF-8');
        }

        return strtolower($str);
    }
    
    /**
     * Checks if two strings have the same value via case-sensitive or case-insensitive comparison
     * 
     * @param string $str1
     * @param string $str2
     * @param bool $caseSensitiveComp true to perform case-sensitive comparison, false to perform case-insensitive comparison
     * 
     * @return bool
     */
    public static function strSame(string $str1, string  $str2, bool $caseSensitiveComp=true): bool {
        
        return $caseSensitiveComp 
                    ? (\strcmp($str1, $str2) === 0 ) 
                    : (\strcmp(static::strToLower($str1), static::strToLower($str2)) === 0 );
    }
    
    /**
     * Checks if two strings have the same value via case-insensitive comparison
     * 
     * @param string $str1
     * @param string $str2
     * 
     * @return bool true if they have the same value, false otherwise
     */
    public static function strSameIgnoreCase(string $str1, string  $str2): bool {
        
        return static::strSame($str1, $str2, false);
    }
}
