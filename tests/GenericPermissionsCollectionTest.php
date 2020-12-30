<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

use \VersatileAcl\GenericPermission;
use \VersatileAcl\GenericPermissionsCollection;
use \VersatileAcl\Interfaces\PermissionInterface;

/**
 * Description of GenericPermissionsCollectionTest
 *
 * @author Rotimi
 */
class GenericPermissionsCollectionTest extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void { 
        
        parent::setUp();
    }
    
    public function testConstructorWorksAsExpected() {
        
        // no args
        $collection = new GenericPermissionsCollection();
        $this->assertEquals(0, $collection->count());
        
        $permissions = [
            new GenericPermission('action-a', 'resource-a'),
            new GenericPermission('action-b', 'resource-b'),
            new GenericPermission('action-c', 'resource-c'),
        ];
        
        // args with array unpacking
        $collection2 = new GenericPermissionsCollection(...$permissions);
        $this->assertEquals(3, $collection2->count());
        $this->assertTrue($collection2->hasPermission($permissions[0]));
        $this->assertTrue($collection2->hasPermission($permissions[1]));
        $this->assertTrue($collection2->hasPermission($permissions[2]));
        
        // multiple args non-array unpacking
        $collection3 = new GenericPermissionsCollection(
            $permissions[0], $permissions[1], $permissions[2]
        );
        $this->assertEquals(3, $collection3->count());
        $this->assertTrue($collection3->hasPermission($permissions[0]));
        $this->assertTrue($collection3->hasPermission($permissions[1]));
        $this->assertTrue($collection3->hasPermission($permissions[2]));
    }
    
    public function testHasPermissionWorksAsExpected() {
        
        $permissions = [
            new GenericPermission('action-a', 'resource-a'),
            new GenericPermission('action-b', 'resource-b'),
            new GenericPermission('action-c', 'resource-c'),
        ];
        $permission4 = new GenericPermission('action-d', 'resource-d');
        $permission5 = new GenericPermission('action-e', 'resource-e');
        
        // empty collection
        $collection = new GenericPermissionsCollection();
        $this->assertFalse($collection->hasPermission($permissions[0]));
        $this->assertFalse($collection->hasPermission($permissions[1]));
        $this->assertFalse($collection->hasPermission($permissions[2]));
        $this->assertFalse($collection->hasPermission($permission4));
        $this->assertFalse($collection->hasPermission($permission5));
        
        // non-empty collection
        $collection2 = new GenericPermissionsCollection(...$permissions);
        $this->assertEquals(3, $collection2->count());
        $this->assertTrue($collection2->hasPermission($permissions[0]));
        $this->assertTrue($collection2->hasPermission($permissions[1]));
        $this->assertTrue($collection2->hasPermission($permissions[2]));
        $this->assertFalse($collection2->hasPermission($permission4));
        $this->assertFalse($collection2->hasPermission($permission5));
        
        $collection2->add($permission4);
        $collection2->add($permission5);
        $this->assertTrue($collection2->hasPermission($permission4));
        $this->assertTrue($collection2->hasPermission($permission5));
    }
    
    public function testIsAllowedWorksAsExpected() {
        
        $callbackFalseNoArg = function() { return false; };
        $callbackTrueNoArg = function() { return true; };
        $callbackOneArg = function(bool $returnVal) { return $returnVal; };
        $callbackTwoArgs = function(bool $returnVal, bool $returnVal2) { 
            
            return $returnVal && $returnVal2; 
        };
        $callbackThreeArgs = function(bool $returnVal, bool $returnVal2, bool $returnVal3) { 
            
            return $returnVal && $returnVal2 && $returnVal3; 
        };
        
        $permissions = [
            new GenericPermission('action-a', 'resource-a'),
            new GenericPermission('action-b', 'resource-b'),
            new GenericPermission('action-c', 'resource-c'),
        ];
        $permissionNotAllowed = new GenericPermission('action-d', 'resource-d', false);
        $permissionNotAllowedByCallback = new GenericPermission('action-e', 'resource-e', true, $callbackFalseNoArg);
        $permissionAllowedIncludingCallback = new GenericPermission('action-f', 'resource-f', true, $callbackOneArg, true);
        
        // empty collection
        $collection = new GenericPermissionsCollection();
        $this->assertFalse($collection->isAllowed('action', 'resource'));
        
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackFalseNoArg));
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackTrueNoArg));
        
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackOneArg, false)); // 0
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackOneArg, true));  // 1
        
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackTwoArgs, false, false));    // 0
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackTwoArgs, false, true));     // 1
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackTwoArgs, true, false));     // 2
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackTwoArgs, true, true));      // 3

        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackThreeArgs, false, false, false));   // 0
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackThreeArgs, false, false, true));    // 1
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackThreeArgs, false, true, false));    // 2
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackThreeArgs, false, true, true));     // 3
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackThreeArgs, true, false, false));    // 4
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackThreeArgs, true, false, true));     // 5
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackThreeArgs, true, true, false));     // 6
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackThreeArgs, true, true, true));      // 7
        
        // non-empty collection
        $collection->add($permissions[0]);
        $collection->add($permissions[1]);
        $collection->add($permissions[2]);
        $collection->add($permissionNotAllowed);
        $collection->add($permissionNotAllowedByCallback);
        $collection->add($permissionAllowedIncludingCallback);
        
        $this->assertFalse($collection->isAllowed('action', 'resource'));                           // non-existent perm no callback
        $this->assertTrue($collection->isAllowed('action-a', 'resource-a'));                        // existent allowed perm
        $this->assertTrue($collection->isAllowed('action-b', 'resource-b'));                        // existent allowed perm
        $this->assertTrue($collection->isAllowed('action-c', 'resource-c'));                        // existent allowed perm
        $this->assertFalse($collection->isAllowed('action-d', 'resource-d'));                       // existent not allowed perm
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $this->assertFalse($collection->isAllowed('action-e', 'resource-e'));                       // existent not allowed perm by callback injected
                                                                                                    // in permission constructor
        
        $this->assertTrue($collection->isAllowed('action-e', 'resource-e', $callbackTrueNoArg));     // existent not allowed perm by callback injected
                                                                                                    // in permission constructor but now allowed by
                                                                                                    // callback injected in this call which should
                                                                                                    // override the constructor injected callback
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $this->assertTrue($collection->isAllowed('action-f', 'resource-f'));                        // existent allowed perm by callback injected
                                                                                                    // in permission constructor
        
        $this->assertFalse($collection->isAllowed('action-f', 'resource-f', $callbackFalseNoArg));   // existent allowed perm by callback injected
                                                                                                    // in permission constructor but now disallowed by
                                                                                                    // callback injected in this call which should
                                                                                                    // override the constructor injected callback
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        $this->assertFalse($collection->isAllowed('action', 'resource', $callbackTrueNoArg));        // non-existent perm with truthy argless callback
        $this->assertTrue($collection->isAllowed('action-a', 'resource-a', $callbackTrueNoArg));     // existent perm with truthy argless callback
        
        $this->assertFalse($collection->isAllowed('action-a', 'resource-a', $callbackFalseNoArg));    // existent allowed perm now disallowed 
                                                                                                     // with falsy argless callback
        
        $this->assertFalse($collection->isAllowed('action-a', 'resource-a', $callbackOneArg, false)); // existent allowed perm now disallowed 
                                                                                                     // with falsy one arged callback
        
        $this->assertFalse($collection->isAllowed('action-a', 'resource-a', $callbackTwoArgs, true, false)); // existent allowed perm now disallowed 
                                                                                                            // with falsy two arged callback
        
        $this->assertFalse($collection->isAllowed('action-a', 'resource-a', $callbackThreeArgs, true, true, false)); // existent allowed perm now disallowed 
                                                                                                                    // with falsy three arged callback
        /////////////////////////////////////////////////////////
        // test super all actions and all resources permissions
        /////////////////////////////////////////////////////////
        $aPermission = new GenericPermission('action-1', 'resource-1');
        $allActionsOnAllResourcesPermission = new GenericPermission(
            GenericPermission::getAllActionsIdentifier(), GenericPermission::getAllResourcesIdentifier()
        );
        $allActionsOnOneResourcePermission = new GenericPermission(GenericPermission::getAllActionsIdentifier(), 'resource-a');
        $oneActionOnAllResourcesPermission = new GenericPermission('edit', GenericPermission::getAllResourcesIdentifier());
        
        $collectionWithAllActionsOnAllResourcesPermissionAddedLast = new GenericPermissionsCollection(
            $aPermission, $allActionsOnAllResourcesPermission
        );
        
        $collectionWithAllActionsOnOneResourcePermissionAddedLast = new GenericPermissionsCollection(
            $aPermission, $allActionsOnOneResourcePermission
        );
        
        $collectionWithOneActionOnAllResourcesPermissionAddedLast = new GenericPermissionsCollection(
            $aPermission, $oneActionOnAllResourcesPermission
        );
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with no callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource'));
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource'));
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource'));
        
        // existent action and non-existent resource with no callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource'));
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource'));
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource'));
        
        // non-existent action and existent resource with no callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1'));
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1'));
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1'));
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with no arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackFalseNoArg)); // falsy callback
        
        // existent action and non-existent resource with no arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackFalseNoArg)); // falsy callback
        
        // non-existent action and existent resource with no arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackFalseNoArg)); // falsy callback
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with 1 arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackOneArg, false)); // falsy callback
        
        // existent action and non-existent resource with 1 arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackOneArg, false)); // falsy callback
        
        // non-existent action and existent resource with 1 arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackOneArg, false)); // falsy callback
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with 2 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackTwoArgs, false, true)); // falsy callback
        
        // existent action and non-existent resource with 2 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackTwoArgs, false, true)); // falsy callback
        
        // non-existent action and existent resource with 2 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackTwoArgs, false, true)); // falsy callback
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with 3 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $callbackThreeArgs, false, true, true)); // falsy callback
        
        // existent action and non-existent resource with 3 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $callbackThreeArgs, false, true, true)); // falsy callback
        
        // non-existent action and existent resource with 3 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $callbackThreeArgs, false, true, true)); // falsy callback
        
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        $collectionWithAllActionsOnAllResourcesPermissionAddedFirst = new GenericPermissionsCollection(
            $allActionsOnAllResourcesPermission, $aPermission
        );
        
        $collectionWithAllActionsOnOneResourcePermissionAddedFirst = new GenericPermissionsCollection(
            $allActionsOnOneResourcePermission, $aPermission
        );
        
        $collectionWithOneActionOnAllResourcesPermissionAddedFirst = new GenericPermissionsCollection(
            $oneActionOnAllResourcesPermission, $aPermission
        );
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
		
        // non-existent action and non-existent resource with no callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource'));
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource'));
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource'));

        // existent action and non-existent resource with no callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource'));
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource'));
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource'));
        
        // non-existent action and existent resource with no callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1'));
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1'));
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1'));
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with no arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackFalseNoArg)); // falsy callback
        
        // existent action and non-existent resource with no arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackFalseNoArg)); // falsy callback
        
        // non-existent action and existent resource with no arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackFalseNoArg)); // falsy callback

        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with 1 arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackOneArg, false)); // falsy callback
        
        // existent action and non-existent resource with 1 arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackOneArg, false)); // falsy callback
        
        // non-existent action and existent resource with 1 arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackOneArg, false)); // falsy callback
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with 2 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackTwoArgs, false, true)); // falsy callback
        
        // existent action and non-existent resource with 2 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackTwoArgs, false, true)); // falsy callback
        
        // non-existent action and existent resource with 2 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackTwoArgs, false, true)); // falsy callback
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with 3 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $callbackThreeArgs, false, true, true)); // falsy callback
        
        // existent action and non-existent resource with 3 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $callbackThreeArgs, false, true, true)); // falsy callback
        
        // non-existent action and existent resource with 3 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $callbackThreeArgs, false, true, true)); // falsy callback
        
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        $aDeniedPermission = new GenericPermission('action-2', 'resource-2', false);
        
        $collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission = new GenericPermissionsCollection(
            $aDeniedPermission, $allActionsOnAllResourcesPermission, $aPermission
        );
        
        $collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission = new GenericPermissionsCollection(
            $aDeniedPermission, $allActionsOnOneResourcePermission, $aPermission
        );
        
        $collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission = new GenericPermissionsCollection(
            $aDeniedPermission, $oneActionOnAllResourcesPermission, $aPermission
        );
        
        ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////
        // denied perm should override all actions and / or all resources permissions
        ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////
        
        // no callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2'));
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2'));
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2'));
        
        // no arg callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $callbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackTrueNoArg)); // truthy callback
        
        // 1 arged callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $callbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackOneArg, true)); // truthy callback
        
        // 2 arged callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $callbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackTwoArgs, true, true)); // truthy callback
        
        // 3 arged callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $callbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $callbackThreeArgs, true, true, true)); // truthy callback
    }
    
    public function testAddWorksAsExpected() {
        
        $permissions = [
            new GenericPermission('action-a', 'resource-a'),
            new GenericPermission('action-b', 'resource-b'),
            new GenericPermission('action-c', 'resource-c'),
        ];
        
        $permission4 = new GenericPermission('action-d', 'resource-d');
        $permission5 = new GenericPermission('action-e', 'resource-e');
        
        // empty collection
        $collection = new GenericPermissionsCollection();
        
        $this->assertSame($collection, $collection->add($permissions[0]));
        $this->assertSame($collection, $collection->add($permissions[1]));
        $this->assertSame($collection, $collection->add($permissions[2]));
        $this->assertSame($collection, $collection->add($permission4));
        $this->assertSame($collection, $collection->add($permission5));
        
        $this->assertTrue($collection->hasPermission($permissions[0]));
        $this->assertTrue($collection->hasPermission($permissions[1]));
        $this->assertTrue($collection->hasPermission($permissions[2]));
        $this->assertTrue($collection->hasPermission($permission4));
        $this->assertTrue($collection->hasPermission($permission5));
        
        // non-empty collection
        $collection2 = new GenericPermissionsCollection(...$permissions);
        
        $this->assertSame($collection2, $collection2->add($permission4));
        $this->assertSame($collection2, $collection2->add($permission5));
        
        $this->assertTrue($collection2->hasPermission($permission4));
        $this->assertTrue($collection2->hasPermission($permission5));
        
        // test that duplicates are not added
        $expectedCount = $collection2->count();
        $collection2->add($permission5); // add duplicate
        $this->assertCount($expectedCount, $collection2);
    }
    
    public function testPutWorksAsExpected() {
        
        $permissions = [
            new GenericPermission('action-a', 'resource-a'),
            new GenericPermission('action-b', 'resource-b'),
            new GenericPermission('action-c', 'resource-c'),
        ];
        
        $permission4 = new GenericPermission('action-d', 'resource-d');
        $permission5 = new GenericPermission('action-e', 'resource-e');
        
        // empty collection
        $collection = new GenericPermissionsCollection();
        
        $this->assertSame($collection, $collection->put($permissions[0], '1'));
        $this->assertSame($collection, $collection->put($permissions[1], '2'));
        $this->assertSame($collection, $collection->put($permissions[2], '3'));
        $this->assertSame($collection, $collection->put($permission4, '4'));
        $this->assertSame($collection, $collection->put($permission5, '5'));
        
        $this->assertTrue($collection->hasPermission($permissions[0]));
        $this->assertTrue($collection->getKey($permissions[0]).'' === '1');
        
        $this->assertTrue($collection->hasPermission($permissions[1]));
        $this->assertTrue($collection->getKey($permissions[1]).'' === '2');
        
        $this->assertTrue($collection->hasPermission($permissions[2]));
        $this->assertTrue($collection->getKey($permissions[2]).'' === '3');
        
        $this->assertTrue($collection->hasPermission($permission4));
        $this->assertTrue($collection->getKey($permission4).'' === '4');
        
        $this->assertTrue($collection->hasPermission($permission5));
        $this->assertTrue($collection->getKey($permission5).'' === '5');
        
        // non-empty collection
        $collection2 = new GenericPermissionsCollection(...$permissions);
        
        $this->assertSame($collection2, $collection2->put($permission4, '44'));
        $this->assertSame($collection2, $collection2->put($permission5, '55'));
        
        $this->assertTrue($collection2->hasPermission($permission4));
        $this->assertTrue($collection2->getKey($permission4).'' === '44');
        
        $this->assertTrue($collection2->hasPermission($permission5));
        $this->assertTrue($collection2->getKey($permission5).'' === '55');
    }
    
    public function testGetWorksAsExpected() {
        
        $permissions = [
            new GenericPermission('action-a', 'resource-a'),
            new GenericPermission('action-b', 'resource-b'),
            new GenericPermission('action-c', 'resource-c'),
        ];        
        $permission4 = new GenericPermission('action-d', 'resource-d');
        $permission5 = new GenericPermission('action-e', 'resource-e');

        // non-empty collection
        $collection = new GenericPermissionsCollection(...$permissions);
        $collection->add($permission4);
        $collection->add($permission5);
        
        $this->assertSame($collection->get('0'), $permissions[0]);
        $this->assertSame($collection->get('1'), $permissions[1]);
        $this->assertSame($collection->get('2'), $permissions[2]);
        $this->assertSame($collection->get('3'), $permission4);
        $this->assertSame($collection->get('4'), $permission5);
        $this->assertNull($collection->get('777'));
    }
    
    public function testGetKeyWorksAsExpected() {
        
        $permissions = [
            new GenericPermission('action-a', 'resource-a'),
            new GenericPermission('action-b', 'resource-b'),
            new GenericPermission('action-c', 'resource-c'),
        ];        
        $permission4 = new GenericPermission('action-d', 'resource-d');
        $permission5 = new GenericPermission('action-e', 'resource-e');
        $nonExistentPermission = new GenericPermission('action-f', 'resource-f');

        // non-empty collection
        $collection = new GenericPermissionsCollection(...$permissions);
        $collection->add($permission4);
        $collection->add($permission5);
        
        $this->assertEquals(0, $collection->getKey($permissions[0]));
        $this->assertEquals(1, $collection->getKey($permissions[1]));
        $this->assertEquals(2, $collection->getKey($permissions[2]));
        $this->assertEquals(3, $collection->getKey($permission4));
        $this->assertEquals(4, $collection->getKey($permission5));
        $this->assertEquals(null, $collection->getKey($nonExistentPermission));
    }
    
    public function testRemoveWorksAsExpected() {
        
        $permissions = [
            new GenericPermission('action-a', 'resource-a'),
            new GenericPermission('action-b', 'resource-b'),
            new GenericPermission('action-c', 'resource-c'),
        ];        
        $permission4 = new GenericPermission('action-d', 'resource-d');
        $permission5 = new GenericPermission('action-e', 'resource-e');
        $nonExistentPermission = new GenericPermission('action-f', 'resource-f');

        // non-empty collection
        $collection = new GenericPermissionsCollection(...$permissions);
        $collection->add($permission4);
        $collection->add($permission5);
        
        $this->assertEquals(5, $collection->count());
        $collection->remove($nonExistentPermission);
        $this->assertEquals(5, $collection->count());
        
        $this->assertTrue($collection->hasPermission($permissions[0]));
        $this->assertSame($collection->remove($permissions[0]), $collection);
        $this->assertFalse($collection->hasPermission($permissions[0]));
        $this->assertEquals(4, $collection->count());
        
        $this->assertTrue($collection->hasPermission($permissions[1]));
        $this->assertSame($collection->remove($permissions[1]), $collection);
        $this->assertFalse($collection->hasPermission($permissions[1]));
        $this->assertEquals(3, $collection->count());
        
        $this->assertTrue($collection->hasPermission($permissions[2]));
        $this->assertSame($collection->remove($permissions[2]), $collection);
        $this->assertFalse($collection->hasPermission($permissions[2]));
        $this->assertEquals(2, $collection->count());
        
        $this->assertTrue($collection->hasPermission($permission4));
        $this->assertSame($collection->remove($permission4), $collection);
        $this->assertFalse($collection->hasPermission($permission4));
        $this->assertEquals(1, $collection->count());
        
        $this->assertTrue($collection->hasPermission($permission5));
        $this->assertSame($collection->remove($permission5), $collection);
        $this->assertFalse($collection->hasPermission($permission5));
        $this->assertEquals(0, $collection->count());
    }
    
    public function testRemoveAllWorksAsExpected() {
        
        $permissions = [
            new GenericPermission('action-a', 'resource-a'),
            new GenericPermission('action-b', 'resource-b'),
            new GenericPermission('action-c', 'resource-c'),
        ];        
        $permission4 = new GenericPermission('action-d', 'resource-d');
        $permission5 = new GenericPermission('action-e', 'resource-e');

        // non-empty collection
        $collection = new GenericPermissionsCollection(...$permissions);
        $collection->add($permission4);
        $collection->add($permission5);
        
        $this->assertEquals(5, $collection->count());
        $collection->removeAll();
        $this->assertEquals(0, $collection->count());
        
        $this->assertFalse($collection->hasPermission($permissions[0]));
        $this->assertFalse($collection->hasPermission($permissions[1]));
        $this->assertFalse($collection->hasPermission($permissions[2]));
        $this->assertFalse($collection->hasPermission($permission4));
        $this->assertFalse($collection->hasPermission($permission5));
    }
    
    
    /////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////
    // Test methods inherited from \VersatileAcl\GenericBaseCollection
    /////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////
    public function testGetIteratorWorksAsExpected() {
        
        $collection = new GenericPermissionsCollection();
        $this->assertInstanceOf(Traversable::class, $collection->getIterator());
    }

    public function testRemoveByKeyWorksAsExpected() {
        
        $collection = new GenericPermissionsCollection();
        
        $perm1 = new GenericPermission('action-a', 'resource-a');
        $perm2 = new GenericPermission('action-b', 'resource-b');
        $perm3 = new GenericPermission('action-c', 'resource-c');
        
        $collection->add($perm1);
        $collection->add($perm2);
        $collection->add($perm3);
        
        $this->assertNull($collection->removeByKey('non-existent-key'));
        $this->assertEquals($perm1, $collection->removeByKey(0));
        $this->assertEquals($perm2, $collection->removeByKey(1));
        $this->assertEquals($perm3, $collection->removeByKey(2));
    }

    public function testKeyExistsWorksAsExpected() {
        
        $collection = new GenericPermissionsCollection();
        
        $perm1 = new GenericPermission('action-a', 'resource-a');
        $perm2 = new GenericPermission('action-b', 'resource-b');
        $perm3 = new GenericPermission('action-c', 'resource-c');
        
        $collection->add($perm1);
        $collection->add($perm2);
        $collection->add($perm3);
        
        $this->assertFalse($collection->keyExists('non-existent-key'));
        /** @noinspection PhpParamsInspection */
        $this->assertFalse($collection->keyExists([]));
        $this->assertFalse($collection->keyExists(777));
        $this->assertTrue($collection->keyExists(0));
        $this->assertTrue($collection->keyExists(1));
        $this->assertTrue($collection->keyExists(2));
    }

    public function testCountWorksAsExpected() {
        
        $collection = new GenericPermissionsCollection();
        
        $perm1 = new GenericPermission('action-a', 'resource-a');
        $perm2 = new GenericPermission('action-b', 'resource-b');
        $perm3 = new GenericPermission('action-c', 'resource-c');
        
        $this->assertEquals(0, $collection->count());
        
        $collection->add($perm1);
        $collection->add($perm2);
        $collection->add($perm3);
        
        $this->assertEquals(3, $collection->count());
    }
    
    public function testSortWorksAsExpected() {
        
        $perms = new GenericPermissionsCollection(
            new GenericPermission('a-action', 'a-resource', true),
            new GenericPermission('d-action', 'd-resource', true),
            new GenericPermission('c-action', 'c-resource', true),
            new GenericPermission('b-action', 'b-resource', false),
            new GenericPermission('b-action', 'a-resource', true),
            new GenericPermission('c-action', 'a-resource', true),
            new GenericPermission('a-action', 'a-resource', true),
            new GenericPermission('a-action', 'a-resource', true),
            new GenericPermission('a-action', 'a-resource', false)
        );
        
        $sortedPerms = [
            ['action'=>'a-action', 'resource'=>'a-resource', 'allow' =>false ],  
            ['action'=>'a-action' , 'resource'=>'a-resource', 'allow' =>true ],
            ['action'=>'a-action' , 'resource'=>'a-resource', 'allow' =>true ],
            ['action'=>'a-action' , 'resource'=>'a-resource', 'allow' =>true ],
            ['action'=>'b-action' , 'resource'=>'a-resource' , 'allow' =>true ],  
            ['action'=>'c-action' , 'resource'=>'a-resource' , 'allow' =>true ],  
            ['action'=>'b-action' , 'resource'=>'b-resource' , 'allow' =>false ],  
            ['action'=>'c-action' , 'resource'=>'c-resource' , 'allow' =>true ],  
            ['action'=>'d-action' , 'resource'=>'d-resource' , 'allow' =>true ],  
        ];
        
        // default sort test
        $perms->sort();
        
        /** @var GenericPermission $perm */
        foreach ($perms as $perm) {
            
            $sortedPerm = array_shift($sortedPerms);
            $this->assertTrue( $perm->getAction() === $sortedPerm['action'] );
            $this->assertTrue( $perm->getResource() === $sortedPerm['resource'] );
            $this->assertTrue( $perm->getAllowActionOnResource() === $sortedPerm['allow'] );
        }
        
        ////////////////////////////////////////////////////////
        // reverse sort with a specified callback
        ////////////////////////////////////////////////////////
        $perms2 = new GenericPermissionsCollection(
            new GenericPermission('a-action', 'a-resource', true),
            new GenericPermission('b-action', 'b-resource', true),
            new GenericPermission('c-action', 'c-resource', true),
            new GenericPermission('d-action', 'd-resource', true)
        );
        
        $sortedPerms2 = [
            ['action'=>'d-action', 'resource'=>'d-resource', 'allow' =>true ],  
            ['action'=>'c-action' , 'resource'=>'c-resource', 'allow' =>true ],
            ['action'=>'b-action' , 'resource'=>'b-resource', 'allow' =>true ],
            ['action'=>'a-action' , 'resource'=>'a-resource', 'allow' =>true ], 
        ];
        
        $comparator = function(PermissionInterface $a, PermissionInterface $b ) : int {

            if( $a->getResource() < $b->getResource() ) {

                return 1;

            } else if( $a->getResource() === $b->getResource() ) {

                return 0;
            }

            return -1;
        };
        
        // sort with specified callback test
        $perms2->sort($comparator);
        
        /** @var GenericPermission $perm */
        foreach ($perms2 as $perm) {
            
            $sortedPerm = array_shift($sortedPerms2);
            $this->assertTrue( $perm->getAction() === $sortedPerm['action'] );
            $this->assertTrue( $perm->getResource() === $sortedPerm['resource'] );
            $this->assertTrue( $perm->getAllowActionOnResource() === $sortedPerm['allow'] );
        }
    }

    public function testDumpWorksAsExpected() {
        
        $collection = new GenericPermissionsCollection();
        
        $callbackTrueNoArg = function() { return true; };
        $callbackThreeArgs = function(bool $returnVal, bool $returnVal2, bool $returnVal3) { 
            
            return $returnVal && $returnVal2 && $returnVal3; 
        };
        
        $perm1 = new GenericPermission('action-a', 'resource-a');
        $perm2 = new GenericPermission('action-b', 'resource-b', true, $callbackThreeArgs, true, true, true);
        $perm3 = new GenericPermission('action-c', 'resource-c', false, $callbackTrueNoArg);
        
        $haystack = $collection->dump();
        $this->assertStringContainsString('VersatileAcl\GenericPermissionsCollection (', $haystack);
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        
        $collection->add($perm1);
        $collection->add($perm2);
        $collection->add($perm3);
        
        $haystack1 = $collection->dump();
        $this->assertStringContainsString('{', $haystack1);
       
        $this->assertStringContainsString("\titem[0]: VersatileAcl\GenericPermission (", $haystack1);
        $this->assertStringContainsString("\t{", $haystack1);
        $this->assertStringContainsString("\t\taction: `action-a`", $haystack1);
        $this->assertStringContainsString("\t\tresource: `resource-a`", $haystack1);
        $this->assertStringContainsString("\t\tallowActionOnResource: true", $haystack1);
        $this->assertStringContainsString("\t\tadditionalAssertions: NULL", $haystack1);
        $this->assertStringContainsString("\t\targsForCallback: array (", $haystack1);
        $this->assertStringContainsString("\t\t)", $haystack1);
        $this->assertStringContainsString("\t}", $haystack1);
       
        $this->assertStringContainsString("\titem[1]: VersatileAcl\GenericPermission (", $haystack1);
        $this->assertStringContainsString("\t{", $haystack1);
        $this->assertStringContainsString("\t\taction: `action-b`", $haystack1);
        $this->assertStringContainsString("\t\tresource: `resource-b`", $haystack1);
        $this->assertStringContainsString("\t\tallowActionOnResource: true", $haystack1);
        $this->assertStringContainsString("\t\tadditionalAssertions: Closure::__set_state(array(", $haystack1);
        $this->assertStringContainsString("\t\t))", $haystack1);
        $this->assertStringContainsString("\t\targsForCallback: array (", $haystack1);
        $this->assertStringContainsString("\t\t  0 => true,", $haystack1);
        $this->assertStringContainsString("\t\t  1 => true,", $haystack1);
        $this->assertStringContainsString("\t\t  2 => true,", $haystack1);
        $this->assertStringContainsString("\t\t)", $haystack1);
        $this->assertStringContainsString("\t}", $haystack1);
       
        $this->assertStringContainsString("\titem[2]: VersatileAcl\GenericPermission (", $haystack1);
        $this->assertStringContainsString("\t{", $haystack1);
        $this->assertStringContainsString("\t\taction: `action-c`", $haystack1);
        $this->assertStringContainsString("\t\tresource: `resource-c`", $haystack1);
        $this->assertStringContainsString("\t\tallowActionOnResource: false", $haystack1);
        $this->assertStringContainsString("\t\tadditionalAssertions: Closure::__set_state(array(", $haystack1);
        $this->assertStringContainsString("\t\t))", $haystack1);
        $this->assertStringContainsString("\t\targsForCallback: array (", $haystack1);
        $this->assertStringContainsString("\t\t)", $haystack1);
        $this->assertStringContainsString("\t}", $haystack1);
        
        $this->assertStringContainsString('}', $haystack1);
         
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        $haystack2 = $collection->dump(['storage']);
        $this->assertStringContainsString('{', $haystack2);
        $this->assertStringContainsString('}', $haystack2);
        
        $this->assertStringNotContainsString("\titem[0]: VersatileAcl\GenericPermission (", $haystack2);
        $this->assertStringNotContainsString("\t{", $haystack2);
        $this->assertStringNotContainsString("\t\taction: `action-a`", $haystack2);
        $this->assertStringNotContainsString("\t\tresource: `resource-a`", $haystack2);
        $this->assertStringNotContainsString("\t\tallowActionOnResource: true", $haystack2);
        $this->assertStringNotContainsString("\t\tadditionalAssertions: NULL", $haystack2);
        $this->assertStringNotContainsString("\t\targsForCallback: array (", $haystack2);
        $this->assertStringNotContainsString("\t\t)", $haystack2);
        $this->assertStringNotContainsString("\t}", $haystack2);
       
        $this->assertStringNotContainsString("\titem[1]: VersatileAcl\GenericPermission (", $haystack2);
        $this->assertStringNotContainsString("\t{", $haystack2);
        $this->assertStringNotContainsString("\t\taction: `action-b`", $haystack2);
        $this->assertStringNotContainsString("\t\tresource: `resource-b`", $haystack2);
        $this->assertStringNotContainsString("\t\tallowActionOnResource: true", $haystack2);
        $this->assertStringNotContainsString("\t\tadditionalAssertions: Closure::__set_state(array(", $haystack2);
        $this->assertStringNotContainsString("\t\t))", $haystack2);
        $this->assertStringNotContainsString("\t\targsForCallback: array (", $haystack2);
        $this->assertStringNotContainsString("\t\t  0 => true,", $haystack2);
        $this->assertStringNotContainsString("\t\t  1 => true,", $haystack2);
        $this->assertStringNotContainsString("\t\t  2 => true,", $haystack2);
        $this->assertStringNotContainsString("\t\t)", $haystack2);
        $this->assertStringNotContainsString("\t}", $haystack2);
       
        $this->assertStringNotContainsString("\titem[2]: VersatileAcl\GenericPermission (", $haystack2);
        $this->assertStringNotContainsString("\t{", $haystack2);
        $this->assertStringNotContainsString("\t\taction: `action-c`", $haystack2);
        $this->assertStringNotContainsString("\t\tresource: `resource-c`", $haystack2);
        $this->assertStringNotContainsString("\t\tallowActionOnResource: false", $haystack2);
        $this->assertStringNotContainsString("\t\tadditionalAssertions: Closure::__set_state(array(", $haystack2);
        $this->assertStringNotContainsString("\t\t))", $haystack2);
        $this->assertStringNotContainsString("\t\targsForCallback: array (", $haystack2);
        $this->assertStringNotContainsString("\t\t)", $haystack2);
        $this->assertStringNotContainsString("\t}", $haystack2);
    }

    public function test__toStringWorksAsExpected() {
        
        $collection = new GenericPermissionsCollection();
        
        $callbackTrueNoArg = function() { return true; };
        $callbackThreeArgs = function(bool $returnVal, bool $returnVal2, bool $returnVal3) { 
            
            return $returnVal && $returnVal2 && $returnVal3; 
        };
        
        $perm1 = new GenericPermission('action-a', 'resource-a');
        $perm2 = new GenericPermission('action-b', 'resource-b', true, $callbackThreeArgs, true, true, true);
        $perm3 = new GenericPermission('action-c', 'resource-c', false, $callbackTrueNoArg);
        
        $haystack = $collection->__toString();
        $this->assertStringContainsString('VersatileAcl\GenericPermissionsCollection (', $haystack);
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        
        $collection->add($perm1);
        $collection->add($perm2);
        $collection->add($perm3);
        
        $haystack1 = $collection->__toString();
        $this->assertStringContainsString('{', $haystack1);
       
        $this->assertStringContainsString("\titem[0]: VersatileAcl\GenericPermission (", $haystack1);
        $this->assertStringContainsString("\t{", $haystack1);
        $this->assertStringContainsString("\t\taction: `action-a`", $haystack1);
        $this->assertStringContainsString("\t\tresource: `resource-a`", $haystack1);
        $this->assertStringContainsString("\t\tallowActionOnResource: true", $haystack1);
        $this->assertStringContainsString("\t\tadditionalAssertions: NULL", $haystack1);
        $this->assertStringContainsString("\t\targsForCallback: array (", $haystack1);
        $this->assertStringContainsString("\t\t)", $haystack1);
        $this->assertStringContainsString("\t}", $haystack1);
       
        $this->assertStringContainsString("\titem[1]: VersatileAcl\GenericPermission (", $haystack1);
        $this->assertStringContainsString("\t{", $haystack1);
        $this->assertStringContainsString("\t\taction: `action-b`", $haystack1);
        $this->assertStringContainsString("\t\tresource: `resource-b`", $haystack1);
        $this->assertStringContainsString("\t\tallowActionOnResource: true", $haystack1);
        $this->assertStringContainsString("\t\tadditionalAssertions: Closure::__set_state(array(", $haystack1);
        $this->assertStringContainsString("\t\t))", $haystack1);
        $this->assertStringContainsString("\t\targsForCallback: array (", $haystack1);
        $this->assertStringContainsString("\t\t  0 => true,", $haystack1);
        $this->assertStringContainsString("\t\t  1 => true,", $haystack1);
        $this->assertStringContainsString("\t\t  2 => true,", $haystack1);
        $this->assertStringContainsString("\t\t)", $haystack1);
        $this->assertStringContainsString("\t}", $haystack1);
       
        $this->assertStringContainsString("\titem[2]: VersatileAcl\GenericPermission (", $haystack1);
        $this->assertStringContainsString("\t{", $haystack1);
        $this->assertStringContainsString("\t\taction: `action-c`", $haystack1);
        $this->assertStringContainsString("\t\tresource: `resource-c`", $haystack1);
        $this->assertStringContainsString("\t\tallowActionOnResource: false", $haystack1);
        $this->assertStringContainsString("\t\tadditionalAssertions: Closure::__set_state(array(", $haystack1);
        $this->assertStringContainsString("\t\t))", $haystack1);
        $this->assertStringContainsString("\t\targsForCallback: array (", $haystack1);
        $this->assertStringContainsString("\t\t)", $haystack1);
        $this->assertStringContainsString("\t}", $haystack1);
        
        $this->assertStringContainsString('}', $haystack1);
    }
    
    public function testFindOneWorksAsExpected() {
        
        $collection = new GenericPermissionsCollection();
        
        $perm1 = new GenericPermission('action-a', 'resource-a');
        $perm2 = new GenericPermission('action-b', 'resource-b');
        $perm3 = new GenericPermission('action-c', 'resource-c');
        $perm4 = new GenericPermission('action-c', 'resource-a');
        $perm5 = new GenericPermission('action-c', 'resource-b');
        $perm6 = new GenericPermission('action-a', 'resource-c');
        
        $this->assertEquals(0, $collection->count());
        
        // test return null scenarios
        $this->assertNull($collection->findOne());
        $this->assertNull($collection->findOne('', ''));
        $this->assertNull($collection->findOne('non-existent-action', 'non-existent-resource'));
        
        $collection->add($perm1);
        $collection->add($perm2);
        $collection->add($perm3);
        $collection->add($perm4);
        $collection->add($perm5);
        $collection->add($perm6);
        
        $this->assertEquals(6, $collection->count());
        
        // test return null scenarios again
        $this->assertNull($collection->findOne());
        $this->assertNull($collection->findOne('', ''));
        $this->assertNull($collection->findOne('non-existent-action', 'non-existent-resource'));
        $this->assertNull($collection->findOne('action-a', 'non-existent-resource'));
        $this->assertNull($collection->findOne('non-existent-action', 'resource-a'));
        
        $this->assertSame($perm1, $collection->findOne('action-a', 'resource-a'));
        $this->assertSame($perm1, $collection->findOne('action-a', ''));
        $this->assertSame($perm1, $collection->findOne('', 'resource-a'));
        
        $this->assertSame($perm3, $collection->findOne('action-c', 'resource-c'));
        $this->assertSame($perm3, $collection->findOne('action-c', ''));
        $this->assertSame($perm3, $collection->findOne('', 'resource-c'));

        $this->assertSame($perm4, $collection->findOne('action-c', 'resource-a'));
        $this->assertSame($perm5, $collection->findOne('action-c', 'resource-b'));
        $this->assertSame($perm6, $collection->findOne('action-a', 'resource-c'));
        
        // test case insensitivity
        /** @noinspection SpellCheckingInspection */
        $this->assertSame($perm1, $collection->findOne('ActioN-a', 'rEsouRce-A'));
        $this->assertSame($perm1, $collection->findOne('ACTION-A', 'RESOURCE-A'));
    }
    
    public function testFindAllWorksAsExpected() {
        
        $collection = new GenericPermissionsCollection();
        
        $perm1 = new GenericPermission('action-a', 'resource-a');
        $perm2 = new GenericPermission('action-b', 'resource-b');
        $perm3 = new GenericPermission('action-c', 'resource-c');
        $perm4 = new GenericPermission('action-c', 'resource-a');
        $perm5 = new GenericPermission('action-c', 'resource-b');
        $perm6 = new GenericPermission('action-a', 'resource-c');
        
        $this->assertEquals(0, $collection->count());
        
        // test return empty collection scenarios
        $this->assertCount(0, $collection->findAll());
        $this->assertCount(0, $collection->findAll('', ''));
        $this->assertCount(0, $collection->findAll('non-existent-action', 'non-existent-resource'));
        
        $collection->add($perm1);
        $collection->add($perm2);
        $collection->add($perm3);
        $collection->add($perm4);
        $collection->add($perm5);
        $collection->add($perm6);
        
        $this->assertEquals(6, $collection->count());
        
        // test return empty collection scenarios again
        $this->assertCount(0, $collection->findAll());
        $this->assertCount(0, $collection->findAll('', ''));
        $this->assertCount(0, $collection->findAll('non-existent-action', 'non-existent-resource'));
        $this->assertCount(0, $collection->findAll('action-a', 'non-existent-resource'));
        $this->assertCount(0, $collection->findAll('non-existent-action', 'resource-a'));
        
        
        // query by both action and resource
        $this->assertCount(1, $collection->findAll('action-a', 'resource-a'));
        $this->assertCount(1, $collection->findAll('action-b', 'resource-b'));
        $this->assertCount(1, $collection->findAll('action-c', 'resource-c'));
        $this->assertCount(1, $collection->findAll('action-c', 'resource-a'));
        $this->assertCount(1, $collection->findAll('action-c', 'resource-b'));
        $this->assertCount(1, $collection->findAll('action-a', 'resource-c'));
        
        $this->assertTrue( $collection->findAll('action-a', 'resource-a')->hasPermission($perm1));
        $this->assertTrue( $collection->findAll('action-b', 'resource-b')->hasPermission($perm2));
        $this->assertTrue( $collection->findAll('action-c', 'resource-c')->hasPermission($perm3));
        $this->assertTrue( $collection->findAll('action-c', 'resource-a')->hasPermission($perm4));
        $this->assertTrue( $collection->findAll('action-c', 'resource-b')->hasPermission($perm5));
        $this->assertTrue( $collection->findAll('action-a', 'resource-c')->hasPermission($perm6));
        
        // query by one attribute, action or resource alone
        $this->assertCount(2, $collection->findAll('action-a', ''));
        $this->assertTrue($collection->findAll('action-a', '')->hasPermission($perm1));
        $this->assertTrue($collection->findAll('action-a', '')->hasPermission($perm6));
        
        $this->assertCount(1, $collection->findAll('action-b', ''));
        $this->assertTrue($collection->findAll('action-b', '')->hasPermission($perm2));
        
        $this->assertCount(3, $collection->findAll('action-c', ''));
        $this->assertTrue($collection->findAll('action-c', '')->hasPermission($perm3));
        $this->assertTrue($collection->findAll('action-c', '')->hasPermission($perm4));
        $this->assertTrue($collection->findAll('action-c', '')->hasPermission($perm5));
        
        $this->assertCount(2, $collection->findAll('', 'resource-a'));
        $this->assertTrue($collection->findAll('', 'resource-a')->hasPermission($perm1));
        $this->assertTrue($collection->findAll('', 'resource-a')->hasPermission($perm4));
        
        $this->assertCount(2, $collection->findAll('', 'resource-b'));
        $this->assertTrue($collection->findAll('', 'resource-b')->hasPermission($perm2));
        $this->assertTrue($collection->findAll('', 'resource-b')->hasPermission($perm5));
        
        $this->assertCount(2, $collection->findAll('', 'resource-c'));
        $this->assertTrue($collection->findAll('', 'resource-c')->hasPermission($perm3));
        $this->assertTrue($collection->findAll('', 'resource-c')->hasPermission($perm6));
        
        // test case insensitivity
        $this->assertTrue( $collection->findAll('aCTiOn-A', 'ResOUrCE-a')->hasPermission($perm1));
        $this->assertTrue( $collection->findAll('ACTION-A', 'RESOURCE-A')->hasPermission($perm1));
    }
}
