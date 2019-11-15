<?php
declare(strict_types=1);

class HelperFunctionsTest extends \PHPUnit\Framework\TestCase {
    
    protected function setUp(): void { 
        
        parent::setUp();
    }


//    public function testThatNonIntendedPrivatePropertyAccessVia_get_object_property_value_WorksAsExpected() {
//
//        $this->expectException(\RuntimeException::class);
//        $obj_protected_and_private_props_and_no_magic_methods = new TestValueObject2('John Doe', 47);
//
//        // accessing the private property without passing true as the fourth
//        // argument to get_object_property_value() below should throw an exception
//        get_object_property_value($obj_protected_and_private_props_and_no_magic_methods, 'private_field');
//    }

    
    public function testThat_dump_var_WorksAsExpected() {

        $result = $this->execFuncAndReturnBufferedOutput(
            "\\SimpleAcl\\dump_var", [['Hello World!', 'Boo']]
        );
        
        $this->assertStringContainsString('Hello World!', $result);
        $this->assertStringContainsString('Boo', $result);
    }
    
    protected function execFuncAndReturnBufferedOutput(callable $func, array $args=[]) {
        
        // Capture the output
        ob_start();
        
        call_user_func_array($func, $args);
        
        // Get the captured output and close the buffer and return the captured output
        return ob_get_clean();
    }
}
