<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);
use SimpleAcl\Utils;

/**
 * Description of ArraysCollectionTest
 *
 * @author aadegbam
 */
class UtilsTest extends \PHPUnit\Framework\TestCase{
    
    protected function setUp(): void { 
        
        parent::setUp();
    }
    
    public function testThatGetClosureFromCallableWorksAsExpected() {
        
        $this->assertTrue(
            Utils::getClosureFromCallable('my_callback_function') instanceof Closure
        ); // from non-anonymous & non-class function
        
        $this->assertTrue(
            Utils::getClosureFromCallable([Ancestor::class, 'who']) instanceof Closure
        ); // static method call on a class
        
        $this->assertTrue(
            Utils::getClosureFromCallable([ (new Descendant() ), 'echoOut']) instanceof Closure
        ); // instance method call on a class instance
        
        $this->assertTrue(
            Utils::getClosureFromCallable(Descendant::class.'::who') instanceof Closure
        ); // static method call string syntax
        
        $this->assertTrue(
            Utils::getClosureFromCallable([Descendant::class, 'parent::who']) instanceof Closure
        ); // parent class' static method call
        
        $this->assertTrue(
            Utils::getClosureFromCallable( (new Descendant()) ) instanceof Closure
        ); // instance of class that has __invoke()
        
        $anon_func = function($a) {
            return $a * 2;
        };
        $this->assertTrue(
            Utils::getClosureFromCallable($anon_func) instanceof Closure
        ); // anonymous function a.k.a Closure
    }
    
    public function testThatStrtolowerWorksAsExpected() {
        
        $this->assertStringContainsString(Utils::strToLower('Base Thrown'), 'base thrown');
        $this->assertStringContainsString(Utils::strToLower('777'), '777');
    }
    
    public function testThatStrSameWorksAsExpected() {
        
        $this->assertTrue(Utils::strSame('Bob', 'Bob')); // case-sensitive
        $this->assertTrue(Utils::strSame('777', '777')); // case-sensitive
        $this->assertTrue(Utils::strSame('Bob', 'bOb', false)); // case-insensitive
        
        $this->assertFalse(Utils::strSame('Bob', 'boB')); // case-sensitive
        $this->assertFalse(Utils::strSame('boB', 'Bob')); // case-sensitive
        $this->assertFalse(Utils::strSame('boB', 'BoOb', false)); // case-insensitive
    }
    
    public function testThatStrSameIgnoreCaseWorksAsExpected() {
        
        $this->assertTrue(Utils::strSameIgnoreCase('Bob', 'bOb'));
        $this->assertTrue(Utils::strSameIgnoreCase('bOb', 'Bob'));
        $this->assertFalse(Utils::strSameIgnoreCase('boB', 'B0b'));
        $this->assertFalse(Utils::strSameIgnoreCase('boB', 'BoOb'));
    }
}

function my_callback_function() { echo 'hello world!'; }
