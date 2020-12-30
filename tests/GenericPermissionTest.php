<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

use \VersatileAcl\Interfaces\PermissionsCollectionInterface;
use \VersatileAcl\GenericPermission;
use \VersatileAcl\GenericPermissionsCollection;

/**
 * Description of GenericPermissionTest
 *
 * @author Rotimi
 */
class GenericPermissionTest extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void { 
        
        parent::setUp();
    }
    
    public function testConstructorWorksAsExpected() {

        $callbackThreeArgs = function(bool $returnVal, bool $returnVal2, bool $returnVal3) { 
            
            return $returnVal && $returnVal2 && $returnVal3; 
        };
        
        //////////////////////
        //////////////////////
        // no optional args
        //////////////////////
        //////////////////////
        $permission = new class('action-a', 'resource-a') extends GenericPermission { 
            
            public function getAdditionalAssertions() {
                
                return $this->additionalAssertions;
            }
            
            public function getArgsForCallback() {
                
                return $this->argsForCallback;
            }
        };
        
        // test supplied action and resource values
        $this->assertSame('action-a', $permission->getAction());
        $this->assertSame('resource-a', $permission->getResource());
        
        // test default values for optional args
        $this->assertSame(true, $permission->getAllowActionOnResource());
        $this->assertNull($permission->getAdditionalAssertions());
        $this->assertSame([], $permission->getArgsForCallback());
        
        //////////////////
        //////////////////
        // all args set
        //////////////////
        //////////////////
        $permission2 = new class('action-b', 'resource-b', false, $callbackThreeArgs, ...[true, false, true]) extends GenericPermission { 
            
            public function getAdditionalAssertions() {
                
                return $this->additionalAssertions;
            }
            
            public function getArgsForCallback() {
                
                return $this->argsForCallback;
            }
        };
        
        // test all supplied values
        $this->assertSame('action-b', $permission2->getAction());
        $this->assertSame('resource-b', $permission2->getResource());
        $this->assertSame(false, $permission2->getAllowActionOnResource());
        $this->assertSame($callbackThreeArgs, $permission2->getAdditionalAssertions());
        $this->assertSame([true, false, true], $permission2->getArgsForCallback());
    }
    
    public function testCreateCollectionWorksAsExpected() {
        
        $this->assertInstanceOf(GenericPermissionsCollection::class, GenericPermission::createCollection());
        $this->assertInstanceOf(PermissionsCollectionInterface::class, GenericPermission::createCollection());
        $this->assertEquals(0, GenericPermission::createCollection()->count());
        
        $perm1 = new GenericPermission('d-action', 'd-resource', true);
        $perm2 = new GenericPermission('b-action', 'b-resource', false);
        $perm3 = new GenericPermission('c-action', 'c-resource', true);
        
        $perms = GenericPermission::createCollection(
            $perm1, $perm2, $perm3
        );
        
        $this->assertCount(3, $perms);
        $this->assertTrue($perms->hasPermission($perm1));
        $this->assertTrue($perms->hasPermission($perm2));
        $this->assertTrue($perms->hasPermission($perm3));
        
        $perms = GenericPermission::createCollection(
            ...[$perm1, $perm2, $perm3]
        );
        
        $this->assertCount(3, $perms);
        $this->assertTrue($perms->hasPermission($perm1));
        $this->assertTrue($perms->hasPermission($perm2));
        $this->assertTrue($perms->hasPermission($perm3));
    }
    
    public function testGetActionWorksAsExpected() {
        
        $permission = new GenericPermission('action-d', 'resource-d');
        $this->assertSame('action-d', $permission->getAction());
    }
    
    public function testGetResourceWorksAsExpected() {
        
        $permission = new GenericPermission('action-d', 'resource-d');
        $this->assertSame('resource-d', $permission->getResource());
    }
    
    public function testGetAllowActionOnResourceWorksAsExpected() {
        
        $permission = new GenericPermission('action-a', 'resource-a');
        $this->assertSame(true, $permission->getAllowActionOnResource());
        
        $permission2 = new GenericPermission('action-b', 'resource-b', false);
        $this->assertSame(false, $permission2->getAllowActionOnResource());
    }
    
    public function testSetAllowActionOnResourceWorksAsExpected() {
        
        $permission = new GenericPermission('action-a', 'resource-a');
        $this->assertSame(true, $permission->getAllowActionOnResource());
        
        $this->assertSame($permission, $permission->setAllowActionOnResource(false)); // test fluent return of $this
        $this->assertSame(false, $permission->getAllowActionOnResource());
    }
    
    public function testGetAllActionsIdentifierWorksAsExpected() {
        
        $this->assertSame('*', GenericPermission::getAllActionsIdentifier());
    }
    
    public function testGetAllResourcesIdentifierWorksAsExpected() {
        
        $this->assertSame('*', GenericPermission::getAllResourcesIdentifier());
    }
    
    public function testIsAllowedWorksAsExpected() {
        
        $callbackFalseNoArg = function() { return false; };
        $callbackTrueNoArg = function() { return true; };
        $callbackThreeArgs = function(bool $returnVal, bool $returnVal2, bool $returnVal3) { 
            
            return $returnVal && $returnVal2 && $returnVal3; 
        };
        
        $permissionNotAllowedNoCallback = new GenericPermission('action-d', 'resource-d', false);
        $permissionNotAllowedByArglessCallback = new GenericPermission('action-e', 'resource-e', true, $callbackFalseNoArg);
        $permissionNotAllowedByThreeArgedCallback = new GenericPermission('action-f', 'resource-f', true, $callbackThreeArgs, true, true, false);
        
        $permissionAllowedNoCallback = new GenericPermission('action-g', 'resource-g', true);
        $permissionAllowedByArglessCallback = new GenericPermission('action-h', 'resource-h', true, $callbackTrueNoArg);
        $permissionAllowedByThreeArgedCallback = new GenericPermission('action-i', 'resource-i', true, $callbackThreeArgs, true, true, true);
        
        ////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////
        // specified action and resource and no specified callback and callback args
        ////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////
        
        // no default callback and args from constructor
        $this->assertTrue($permissionAllowedNoCallback->isAllowed('action-g', 'resource-g'));
        $this->assertFalse($permissionAllowedNoCallback->isAllowed('action-z', 'resource-z')); // different action and resource
        
        $this->assertFalse($permissionNotAllowedNoCallback->isAllowed('action-d', 'resource-d'));
        $this->assertFalse($permissionNotAllowedNoCallback->isAllowed('action-z', 'resource-z')); // different action and resource
        
        // default argless callback from constructor
        $this->assertTrue($permissionAllowedByArglessCallback->isAllowed('action-h', 'resource-h'));
        $this->assertFalse($permissionAllowedByArglessCallback->isAllowed('action-z', 'resource-z')); // different action and resource
        
        $this->assertFalse($permissionNotAllowedByArglessCallback->isAllowed('action-e', 'resource-e'));
        $this->assertFalse($permissionNotAllowedByArglessCallback->isAllowed('action-z', 'resource-z')); // different action and resource
        
        // default 3 arged callback from constructor
        $this->assertTrue($permissionAllowedByThreeArgedCallback->isAllowed('action-i', 'resource-i'));
        $this->assertFalse($permissionAllowedByThreeArgedCallback->isAllowed('action-z', 'resource-z')); // different action and resource
        
        $this->assertFalse($permissionNotAllowedByThreeArgedCallback->isAllowed('action-f', 'resource-f'));
        $this->assertFalse($permissionNotAllowedByThreeArgedCallback->isAllowed('action-z', 'resource-z')); // different action and resource
        
        ////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////
        // specified action and resource and specified argless callback 
        ////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////
        
        // no default callback and args from constructor
        $this->assertTrue($permissionAllowedNoCallback->isAllowed('action-g', 'resource-g', $callbackTrueNoArg));
        $this->assertFalse($permissionAllowedNoCallback->isAllowed('action-z', 'resource-z', $callbackTrueNoArg)); // different action and resource
        $this->assertFalse($permissionAllowedNoCallback->isAllowed('action-g', 'resource-g', $callbackFalseNoArg)); // falsy argless callback causes false return
        $this->assertFalse($permissionAllowedNoCallback->isAllowed('action-z', 'resource-z', $callbackFalseNoArg)); // different action and resource
        
        $this->assertFalse($permissionNotAllowedNoCallback->isAllowed('action-d', 'resource-d', $callbackTrueNoArg));
        $this->assertFalse($permissionNotAllowedNoCallback->isAllowed('action-z', 'resource-z', $callbackTrueNoArg)); // different action and resource
        $this->assertFalse($permissionNotAllowedNoCallback->isAllowed('action-d', 'resource-d', $callbackFalseNoArg));
        $this->assertFalse($permissionNotAllowedNoCallback->isAllowed('action-z', 'resource-z', $callbackFalseNoArg)); // different action and resource
        
        // default argless callback from constructor overridden by argless callback
        $this->assertTrue($permissionAllowedByArglessCallback->isAllowed('action-h', 'resource-h', $callbackTrueNoArg));
        $this->assertFalse($permissionAllowedByArglessCallback->isAllowed('action-z', 'resource-z', $callbackTrueNoArg)); // different action and resource
        $this->assertFalse($permissionAllowedByArglessCallback->isAllowed('action-h', 'resource-h', $callbackFalseNoArg)); // falsy argless callback overrides truthy argless default callback
        $this->assertFalse($permissionAllowedByArglessCallback->isAllowed('action-z', 'resource-z', $callbackFalseNoArg)); // different action and resource
        
        $this->assertTrue($permissionNotAllowedByArglessCallback->isAllowed('action-e', 'resource-e', $callbackTrueNoArg)); // truthy argless callback overrides default falsy argless callback
        $this->assertFalse($permissionNotAllowedByArglessCallback->isAllowed('action-z', 'resource-z', $callbackTrueNoArg)); // different action and resource
        $this->assertFalse($permissionNotAllowedByArglessCallback->isAllowed('action-e', 'resource-e', $callbackFalseNoArg)); // falsy argless callback overrides default falsy argless callback
        $this->assertFalse($permissionNotAllowedByArglessCallback->isAllowed('action-z', 'resource-z', $callbackFalseNoArg)); // different action and resource
        
        // default 3 arged callback from constructor overridden by argless callback
        $this->assertTrue($permissionAllowedByThreeArgedCallback->isAllowed('action-i', 'resource-i', $callbackTrueNoArg));
        $this->assertFalse($permissionAllowedByThreeArgedCallback->isAllowed('action-z', 'resource-z', $callbackTrueNoArg)); // different action and resource
        $this->assertFalse($permissionAllowedByThreeArgedCallback->isAllowed('action-i', 'resource-i', $callbackFalseNoArg)); // falsy argless callback overrides default 3 arged truthy callback
        $this->assertFalse($permissionAllowedByThreeArgedCallback->isAllowed('action-z', 'resource-z', $callbackFalseNoArg)); // different action and resource
        
        $this->assertTrue($permissionNotAllowedByThreeArgedCallback->isAllowed('action-f', 'resource-f', $callbackTrueNoArg)); // truthy argless callback overrides default 3 arged falsy callback
        $this->assertFalse($permissionNotAllowedByThreeArgedCallback->isAllowed('action-z', 'resource-z', $callbackTrueNoArg)); // different action and resource
        $this->assertFalse($permissionNotAllowedByThreeArgedCallback->isAllowed('action-f', 'resource-f', $callbackFalseNoArg)); // falsy argless callback overrides default 3 arged falsy callback
        $this->assertFalse($permissionNotAllowedByThreeArgedCallback->isAllowed('action-z', 'resource-z', $callbackFalseNoArg)); // different action and resource
        
        ////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////
        // specified action and resource and specified 3 arged callback with 3 args 
        ////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////
        
        // no default callback and args from constructor
        $this->assertTrue($permissionAllowedNoCallback->isAllowed('action-g', 'resource-g', $callbackThreeArgs, true, true, true)); // truthy 3 arged callback specified
        $this->assertFalse($permissionAllowedNoCallback->isAllowed('action-g', 'resource-g', $callbackThreeArgs, true, true, false)); // falsy 3 arged callback specified
        $this->assertFalse($permissionAllowedNoCallback->isAllowed('action-z', 'resource-z', $callbackThreeArgs, true, true, true)); // different action and resource
        
        $this->assertFalse($permissionNotAllowedNoCallback->isAllowed('action-d', 'resource-d', $callbackThreeArgs, true, true, true)); // truthy 3 arged callback specified
        $this->assertFalse($permissionNotAllowedNoCallback->isAllowed('action-d', 'resource-d', $callbackThreeArgs, true, true, false)); // falsy 3 arged callback specified
        $this->assertFalse($permissionNotAllowedNoCallback->isAllowed('action-z', 'resource-z', $callbackThreeArgs, true, true, true)); // different action and resource
        
        // default argless callback from constructor overridden by specified 3 arged callback with 3 args 
        $this->assertTrue($permissionAllowedByArglessCallback->isAllowed('action-h', 'resource-h', $callbackThreeArgs, true, true, true)); // truthy 3 arged callback specified overrides default truthy argless callback
        $this->assertFalse($permissionAllowedByArglessCallback->isAllowed('action-h', 'resource-h', $callbackThreeArgs, true, true, false)); // falsy 3 arged callback specified overrides default truthy argless callback
        $this->assertFalse($permissionAllowedByArglessCallback->isAllowed('action-z', 'resource-z', $callbackThreeArgs, true, true, true)); // different action and resource
        
        $this->assertTrue($permissionNotAllowedByArglessCallback->isAllowed('action-e', 'resource-e', $callbackThreeArgs, true, true, true)); // truthy 3 arged callback specified overrides default falsy argless callback
        $this->assertFalse($permissionNotAllowedByArglessCallback->isAllowed('action-e', 'resource-e', $callbackThreeArgs, true, true, false)); // falsy 3 arged callback specified overrides default falsy argless callback
        $this->assertFalse($permissionNotAllowedByArglessCallback->isAllowed('action-z', 'resource-z', $callbackThreeArgs, true, true, true)); // different action and resource
        
        // default 3 arged callback from constructor overridden by specified 3 arged callback with 3 args 
        $this->assertTrue($permissionAllowedByThreeArgedCallback->isAllowed('action-i', 'resource-i', $callbackThreeArgs, true, true, true)); // truthy 3 arged callback specified
        $this->assertFalse($permissionAllowedByThreeArgedCallback->isAllowed('action-i', 'resource-i', $callbackThreeArgs, true, true, false)); // falsy 3 arged callback specified
        $this->assertFalse($permissionAllowedByThreeArgedCallback->isAllowed('action-z', 'resource-z', $callbackTrueNoArg)); // different action and resource
        
        $this->assertTrue($permissionNotAllowedByThreeArgedCallback->isAllowed('action-f', 'resource-f', $callbackThreeArgs, true, true, true)); // truthy 3 arged callback specified
        $this->assertFalse($permissionNotAllowedByThreeArgedCallback->isAllowed('action-f', 'resource-f', $callbackThreeArgs, true, true, false)); // falsy 3 arged callback specified
        $this->assertFalse($permissionNotAllowedByThreeArgedCallback->isAllowed('action-z', 'resource-z', $callbackTrueNoArg)); // different action and resource
    }
    
    public function testIsEqualToWorksAsExpected() {
        
        $permissionOther = new GenericPermission('action-a', 'resource-a', true);
        $permissionOther2 = new GenericPermission('action-d', 'resource-a', true);
        $permissionOther3 = new GenericPermission('action-a', 'resource-d', true);
        $permissionAllowed = new GenericPermission('action-d', 'resource-d', true);
        $permissionNotAllowed = new GenericPermission('action-d', 'resource-d', false);
        
        $this->assertTrue($permissionAllowed->isEqualTo($permissionNotAllowed)); // same action and resource even though allowed is true on one and false on the other
        $this->assertTrue($permissionNotAllowed->isEqualTo($permissionAllowed)); // same action and resource even though allowed is true on one and false on the other
        $this->assertFalse($permissionOther->isEqualTo($permissionAllowed)); // different action and resource
        $this->assertFalse($permissionAllowed->isEqualTo($permissionOther)); // different action and resource
        $this->assertFalse($permissionOther->isEqualTo($permissionNotAllowed)); // different action and resource
        $this->assertFalse($permissionNotAllowed->isEqualTo($permissionOther)); // different action and resource
        
        $this->assertFalse($permissionAllowed->isEqualTo($permissionOther2)); // same action and different resource
        $this->assertFalse($permissionOther2->isEqualTo($permissionAllowed)); // same action and different resource
        $this->assertFalse($permissionNotAllowed->isEqualTo($permissionOther2)); // same action and different resource
        $this->assertFalse($permissionOther2->isEqualTo($permissionNotAllowed)); // same action and different resource
        
        $this->assertFalse($permissionAllowed->isEqualTo($permissionOther3)); // different action and same resource
        $this->assertFalse($permissionOther3->isEqualTo($permissionAllowed)); // different action and same resource
        $this->assertFalse($permissionNotAllowed->isEqualTo($permissionOther3)); // different action and same resource
        $this->assertFalse($permissionOther3->isEqualTo($permissionNotAllowed)); // different action and same resource
    }
    
    public function testDumpWorksAsExpected() {
        
        $callbackNoArg = function() { return true; };
        $callbackOneArg = function(bool $returnVal) { return $returnVal; };
        $callbackThreeArgs = function(bool $returnVal, bool $returnVal2, bool $returnVal3) { 
            
            return $returnVal && $returnVal2 && $returnVal3; 
        };

        /** @noinspection SpellCheckingInspection */
        $permissionAllDefaults = new GenericPermission('Action-a', 'resouRce-a');
        
        $permissionTruthyRestDefault = new GenericPermission('action-a', 'resource-a', true);
        $permissionFalsyRestDefault = new GenericPermission('action-a', 'resource-a', false);
        
        $permissionTruthyAndArglessCallbackRestDefault = new GenericPermission('action-a', 'resource-a', true, $callbackNoArg);
        
        $permissionTruthyAndOneArgedFalsyCallback = new GenericPermission('action-a', 'resource-a', true, $callbackOneArg, false);
        $permissionTruthyAndOneArgedTruthyCallback = new GenericPermission('action-a', 'resource-a', true, $callbackOneArg, true);
        
        $permissionTruthyAndThreeArgedFalsyCallback = new GenericPermission('action-a', 'resource-a', true, $callbackThreeArgs, false, true, true);
        $permissionTruthyAndThreeArgedTruthyCallback = new GenericPermission('action-a', 'resource-a', true, $callbackThreeArgs, true, true, true);
        
        //$permissionAllDefaults->dump(); // should generate output below (000000007e431f63000000003db97236 will be different though)
/**        
VersatileAcl\GenericPermission (000000007e431f63000000003db97236)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: true
        additionalAssertions: NULL
        argsForCallback: array (
        )

}
*/
        $haystack = $permissionAllDefaults->dump();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        // Test dump with args
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        $haystack = $permissionAllDefaults->dump(['action']);
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringNotContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
        $haystack = $permissionAllDefaults->dump(['action', 'resource']);
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringNotContainsString("\taction: `action-a`", $haystack);
        $this->assertStringNotContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
        $haystack = $permissionAllDefaults->dump(['action', 'resource', 'allowActionOnResource']);
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringNotContainsString("\taction: `action-a`", $haystack);
        $this->assertStringNotContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringNotContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
        $haystack = $permissionAllDefaults->dump(['action', 'resource', 'allowActionOnResource', 'additionalAssertions']);
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringNotContainsString("\taction: `action-a`", $haystack);
        $this->assertStringNotContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringNotContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringNotContainsString("\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
        $haystack = $permissionAllDefaults->dump(['action', 'resource', 'allowActionOnResource', 'additionalAssertions', 'argsForCallback']);
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringNotContainsString("\taction: `action-a`", $haystack);
        $this->assertStringNotContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringNotContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringNotContainsString("\tadditionalAssertions: NULL", $haystack);
        $this->assertStringNotContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringNotContainsString("\t)", $haystack);
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        // End of testing dump with args
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        
        //$permissionTruthyRestDefault->dump(); // should generate output below (0000000066367b3e000000006ec99fdf will be different though)
/**        
VersatileAcl\GenericPermission (0000000066367b3e000000006ec99fdf)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: true
        additionalAssertions: NULL
        argsForCallback: array (
        )

}
*/
        $haystack = $permissionTruthyRestDefault->dump();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
        //$permissionFalsyRestDefault->dump(); // should generate output below (0000000066367b3e000000006ec99fdf will be different though)
/**        
VersatileAcl\GenericPermission (0000000066367b3e000000006ec99fdf)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: false
        additionalAssertions: NULL
        argsForCallback: array (
        )

}
*/
        $haystack = $permissionFalsyRestDefault->dump();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: false", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
       
        //$permissionTruthyAndArglessCallbackRestDefault->dump(); // should generate output below (0000000010c6e6c2000000002e73da65 will be different though)
/**
VersatileAcl\GenericPermission (0000000010c6e6c2000000002e73da65)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: true
        additionalAssertions: Closure::__set_state(array(
        ))
        argsForCallback: array (
        )

}
*/
        $haystack = $permissionTruthyAndArglessCallbackRestDefault->dump();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: Closure::__set_state(array(", $haystack);
        $this->assertStringContainsString("\t))", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
    //$permissionTruthyAndOneArgedFalsyCallback->dump(); // should generate output below (0000000078ef9ee800000000588db454 will be different though)
/**
VersatileAcl\GenericPermission (0000000078ef9ee800000000588db454)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: true
        additionalAssertions: Closure::__set_state(array(
        ))
        argsForCallback: array (
          0 => false,
        )

}
*/
        $haystack = $permissionTruthyAndOneArgedFalsyCallback->dump();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: Closure::__set_state(array(", $haystack);
        $this->assertStringContainsString("\t))", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t  0 => false,", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
    //$permissionTruthyAndOneArgedTruthyCallback->dump(); // should generate output below (0000000078ef9ee800000000588db454 will be different though)
/**
VersatileAcl\GenericPermission (0000000078ef9ee800000000588db454)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: true
        additionalAssertions: Closure::__set_state(array(
        ))
        argsForCallback: array (
          0 => true,
        )

}
*/
        $haystack = $permissionTruthyAndOneArgedTruthyCallback->dump();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: Closure::__set_state(array(", $haystack);
        $this->assertStringContainsString("\t))", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t  0 => true,", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
    //$permissionTruthyAndThreeArgedFalsyCallback->dump(); // should generate output below (0000000030c2abd70000000053ea6caf will be different though)
/**
VersatileAcl\GenericPermission (0000000030c2abd70000000053ea6caf)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: true
        additionalAssertions: Closure::__set_state(array(
        ))
        argsForCallback: array (
          0 => false,
          1 => true,
          2 => true,
        )
}
*/
        $haystack = $permissionTruthyAndThreeArgedFalsyCallback->dump();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: Closure::__set_state(array(", $haystack);
        $this->assertStringContainsString("\t))", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t  0 => false,", $haystack);
        $this->assertStringContainsString("\t  1 => true,", $haystack);
        $this->assertStringContainsString("\t  2 => true,", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
    //$permissionTruthyAndThreeArgedTruthyCallback->dump(); // should generate output below (0000000030c2abd70000000053ea6caf will be different though)
/**
VersatileAcl\GenericPermission (0000000030c2abd70000000053ea6caf)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: true
        additionalAssertions: Closure::__set_state(array(
        ))
        argsForCallback: array (
          0 => true,
          1 => true,
          2 => true,
        )
}
*/
        $haystack = $permissionTruthyAndThreeArgedTruthyCallback->dump();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: Closure::__set_state(array(", $haystack);
        $this->assertStringContainsString("\t))", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t  0 => true,", $haystack);
        $this->assertStringContainsString("\t  1 => true,", $haystack);
        $this->assertStringContainsString("\t  2 => true,", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
    }
    
    public function test__toStringWorksAsExpected() {
        
        $callbackNoArg = function() { return true; };
        $callbackOneArg = function(bool $returnVal) { return $returnVal; };
        $callbackThreeArgs = function(bool $returnVal, bool $returnVal2, bool $returnVal3) { 
            
            return $returnVal && $returnVal2 && $returnVal3; 
        };

        /** @noinspection SpellCheckingInspection */
        $permissionAllDefaults = new GenericPermission('Action-a', 'resouRce-a');
        
        $permissionTruthyRestDefault = new GenericPermission('action-a', 'resource-a', true);
        $permissionFalsyRestDefault = new GenericPermission('action-a', 'resource-a', false);
        
        $permissionTruthyAndArglessCallbackRestDefault = new GenericPermission('action-a', 'resource-a', true, $callbackNoArg);
        
        $permissionTruthyAndOneArgedFalsyCallback = new GenericPermission('action-a', 'resource-a', true, $callbackOneArg, false);
        $permissionTruthyAndOneArgedTruthyCallback = new GenericPermission('action-a', 'resource-a', true, $callbackOneArg, true);
        
        $permissionTruthyAndThreeArgedFalsyCallback = new GenericPermission('action-a', 'resource-a', true, $callbackThreeArgs, false, true, true);
        $permissionTruthyAndThreeArgedTruthyCallback = new GenericPermission('action-a', 'resource-a', true, $callbackThreeArgs, true, true, true);
        
        //$permissionAllDefaults->__toString(); // should generate output below (000000007e431f63000000003db97236 will be different though)
/**        
VersatileAcl\GenericPermission (000000007e431f63000000003db97236)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: true
        additionalAssertions: NULL
        argsForCallback: array (
        )

}
*/
        $haystack = $permissionAllDefaults->__toString();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t)", $haystack);

        //$permissionTruthyRestDefault->__toString(); // should generate output below (0000000066367b3e000000006ec99fdf will be different though)
/**        
VersatileAcl\GenericPermission (0000000066367b3e000000006ec99fdf)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: true
        additionalAssertions: NULL
        argsForCallback: array (
        )

}
*/
        $haystack = $permissionTruthyRestDefault->__toString();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
        //$permissionFalsyRestDefault->__toString(); // should generate output below (0000000066367b3e000000006ec99fdf will be different though)
/**        
VersatileAcl\GenericPermission (0000000066367b3e000000006ec99fdf)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: false
        additionalAssertions: NULL
        argsForCallback: array (
        )

}
*/
        $haystack = $permissionFalsyRestDefault->__toString();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: false", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: NULL", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
       
        //$permissionTruthyAndArglessCallbackRestDefault->__toString(); // should generate output below (0000000010c6e6c2000000002e73da65 will be different though)
/**
VersatileAcl\GenericPermission (0000000010c6e6c2000000002e73da65)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: true
        additionalAssertions: Closure::__set_state(array(
        ))
        argsForCallback: array (
        )

}
*/
        $haystack = $permissionTruthyAndArglessCallbackRestDefault->__toString();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: Closure::__set_state(array(", $haystack);
        $this->assertStringContainsString("\t))", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
    //$permissionTruthyAndOneArgedFalsyCallback->__toString(); // should generate output below (0000000078ef9ee800000000588db454 will be different though)
/**
VersatileAcl\GenericPermission (0000000078ef9ee800000000588db454)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: true
        additionalAssertions: Closure::__set_state(array(
        ))
        argsForCallback: array (
          0 => false,
        )

}
*/
        $haystack = $permissionTruthyAndOneArgedFalsyCallback->__toString();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: Closure::__set_state(array(", $haystack);
        $this->assertStringContainsString("\t))", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t  0 => false,", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
    //$permissionTruthyAndOneArgedTruthyCallback->__toString(); // should generate output below (0000000078ef9ee800000000588db454 will be different though)
/**
VersatileAcl\GenericPermission (0000000078ef9ee800000000588db454)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: true
        additionalAssertions: Closure::__set_state(array(
        ))
        argsForCallback: array (
          0 => true,
        )

}
*/
        $haystack = $permissionTruthyAndOneArgedTruthyCallback->__toString();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: Closure::__set_state(array(", $haystack);
        $this->assertStringContainsString("\t))", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t  0 => true,", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
    //$permissionTruthyAndThreeArgedFalsyCallback->__toString(); // should generate output below (0000000030c2abd70000000053ea6caf will be different though)
/**
VersatileAcl\GenericPermission (0000000030c2abd70000000053ea6caf)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: true
        additionalAssertions: Closure::__set_state(array(
        ))
        argsForCallback: array (
          0 => false,
          1 => true,
          2 => true,
        )
}
*/
        $haystack = $permissionTruthyAndThreeArgedFalsyCallback->__toString();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: Closure::__set_state(array(", $haystack);
        $this->assertStringContainsString("\t))", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t  0 => false,", $haystack);
        $this->assertStringContainsString("\t  1 => true,", $haystack);
        $this->assertStringContainsString("\t  2 => true,", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
        
    //$permissionTruthyAndThreeArgedTruthyCallback->__toString(); // should generate output below (0000000030c2abd70000000053ea6caf will be different though)
/**
VersatileAcl\GenericPermission (0000000030c2abd70000000053ea6caf)
{
        action: `action-a`
        resource: `resource-a`
        allowActionOnResource: true
        additionalAssertions: Closure::__set_state(array(
        ))
        argsForCallback: array (
          0 => true,
          1 => true,
          2 => true,
        )
}
*/
        $haystack = $permissionTruthyAndThreeArgedTruthyCallback->__toString();
        $this->assertStringContainsString('VersatileAcl\GenericPermission (', $haystack);
        
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        $this->assertStringContainsString("\taction: `action-a`", $haystack);
        $this->assertStringContainsString("\tresource: `resource-a`", $haystack);
        $this->assertStringContainsString("\tallowActionOnResource: true", $haystack);
        $this->assertStringContainsString("\tadditionalAssertions: Closure::__set_state(array(", $haystack);
        $this->assertStringContainsString("\t))", $haystack);
        $this->assertStringContainsString("\targsForCallback: array (", $haystack);
        $this->assertStringContainsString("\t  0 => true,", $haystack);
        $this->assertStringContainsString("\t  1 => true,", $haystack);
        $this->assertStringContainsString("\t  2 => true,", $haystack);
        $this->assertStringContainsString("\t)", $haystack);
    }
}
