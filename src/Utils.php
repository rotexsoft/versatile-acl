<?php
declare(strict_types=1);
namespace SimpleAcl;

/**
 * Description of Utils
 *
 * @author rotimi
 */
class Utils {

    /**
     * 
     * @param callable $callable
     * @return \Closure
     */
    public static function getClosureFromCallable(callable $callable): \Closure {

        return ($callable instanceof \Closure)? $callable : \Closure::fromCallable($callable);
    }

    /**
     * 
     * @param \Closure $closure
     * @param object $newthis
     * @return \Closure
     * @throws \InvalidArgumentException
     */
    public static function bindObjectAndScopeToClosure(\Closure $closure, object $newthis): \Closure {

        try {
            $new_closure = \Closure::bind($closure, $newthis);
            
            return $new_closure;
            
        } catch (\Exception $ex) {
            
            $function = __FUNCTION__;
            $class = static::class;
            $msg = "Error [{$class}::{$function}(...)]: Could not bind \$newthis to the supplied closure"
                . PHP_EOL . PHP_EOL . static::getExceptionAsStr($ex);

            // The bind failed
            throw new \InvalidArgumentException($msg);
        }
    }
    
    /**
     * 
     * @param \Throwable $e
     * @param string $eol
     * @return string
     */
    public static function getThrowableAsStr(\Throwable $e, string $eol=PHP_EOL): string {

        $previous_throwable = $e;
        $message = '';

        do {
            $message .= "Exception / Error Code: {$previous_throwable->getCode()}"
                . $eol . "Exception / Error Class: " . get_class($previous_throwable)
                . $eol . "File: {$previous_throwable->getFile()}"
                . $eol . "Line: {$previous_throwable->getLine()}"
                . $eol . "Message: {$previous_throwable->getMessage()}" . $eol
                . $eol . "Trace: {$eol}{$previous_throwable->getTraceAsString()}{$eol}{$eol}";
                
            $previous_throwable = $previous_throwable->getPrevious();
        } while( $previous_throwable instanceof \Throwable );
        
        return $message;
    }

    /**
     * 
     * @param array $array
     * @return mixed
     */
    public static function array_key_first(array $array) {

        if( function_exists('array_key_first') ) {
            
            return \array_key_first($array);
        }
        
        // polyfill
        if( $array === [] ) { return null; }

        foreach($array as $key => $value) { return $key; }
    }

    /**
     * 
     * @param array $array
     * @return mixed
     */
    public static function array_key_last(array $array) {

        if( function_exists('array_key_last') ) {

            return \array_key_last($array);
        }
        
        // polyfill
        if( $array === [] ) { return null; }

        return static::array_key_first(array_slice($array, -1, null, true));
    }
    
    /**
     * 
     * @param string $str
     * @return string
     */
    public static function strtolower(string $str): string {
          
        if( function_exists('mb_strtolower') ) {

            return mb_strtolower($str, 'UTF-8');
        }

        return strtolower($str);
    }
}
