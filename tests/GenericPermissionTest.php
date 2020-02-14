<?php
declare(strict_types=1);

use \SimpleAcl\Interfaces\PermissionsCollectionInterface;
use \SimpleAcl\GenericPermission;
use \SimpleAcl\GenericPermissionsCollection;

/**
 * Description of GenericPermissionTest
 *
 * @author Rotimi
 */
class GenericPermissionTest extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void { 
        
        parent::setUp();
    }
    
    public function testConstructorWorksAsExcpected() {

        $calbackThreeArgs = function(bool $returnVal, bool $returnVal2, bool $returnVal3) { 
            
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
        $permission2 = new class('action-b', 'resource-b', false, $calbackThreeArgs, ...[true, false, true]) extends GenericPermission { 
            
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
        $this->assertSame($calbackThreeArgs, $permission2->getAdditionalAssertions());
        $this->assertSame([true, false, true], $permission2->getArgsForCallback());
    }
    
    public function testCreateCollectionWorksAsExcpected() {
        
        $this->assertInstanceOf(GenericPermissionsCollection::class, GenericPermission::createCollection());
        $this->assertInstanceOf(PermissionsCollectionInterface::class, GenericPermission::createCollection());
    }
    
    public function testGetActionWorksAsExcpected() {
        
        $permission = new GenericPermission('action-d', 'resource-d');
        $this->assertSame('action-d', $permission->getAction());
    }
    
    public function testGetResourceWorksAsExcpected() {
        
        $permission = new GenericPermission('action-d', 'resource-d');
        $this->assertSame('resource-d', $permission->getResource());
    }
    
    public function testGetAllowActionOnResourceWorksAsExcpected() {
        
        $permission = new GenericPermission('action-a', 'resource-a');
        $this->assertSame(true, $permission->getAllowActionOnResource());
        
        $permission2 = new GenericPermission('action-b', 'resource-b', false);
        $this->assertSame(false, $permission2->getAllowActionOnResource());
    }
    
    public function testSetAllowActionOnResourceWorksAsExcpected() {
        
        $permission = new GenericPermission('action-a', 'resource-a');
        $this->assertSame(true, $permission->getAllowActionOnResource());
        
        $this->assertSame($permission, $permission->setAllowActionOnResource(false)); // test fluent return of $this
        $this->assertSame(false, $permission->getAllowActionOnResource());
    }
    
    public function testGetAllActionsIdentifierWorksAsExcpected() {
        
        $this->assertSame('*', GenericPermission::getAllActionsIdentifier());
    }
    
    public function testGetAllResoucesIdentifierWorksAsExcpected() {
        
        $this->assertSame('*', GenericPermission::getAllResoucesIdentifier());
    }
}
