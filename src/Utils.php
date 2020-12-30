<?php
declare(strict_types=1);
namespace SimpleAcl;

use Closure;
use function function_exists;
use function strtolower;


/**
 * Description of Utils
 *
 * @author Rotimi
 */
class Utils {

    /**
     * 
     * @param callable $callable
     * 
     * @return Closure
     */
    public static function getClosureFromCallable(callable $callable): Closure {

        return ($callable instanceof Closure)? $callable : Closure::fromCallable($callable);
    }
    
    /**
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
    
    public static function strSame(string $str1, string  $str2, bool $caseSensitiveComp=true): bool {
        
        return $caseSensitiveComp 
                    ? (\strcmp($str1, $str2) === 0 ) 
                    : (\strcmp(static::strToLower($str1), static::strToLower($str2)) === 0 );
    }
    
    public static function strSameIgnoreCase(string $str1, string  $str2): bool {
        
        return static::strSame($str1, $str2, false);
    }
}
