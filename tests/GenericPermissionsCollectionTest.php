<?php
declare(strict_types=1);

use \SimpleAcl\GenericPermission;
use \SimpleAcl\GenericPermissionsCollection;
use \SimpleAcl\Interfaces\PermissionInterface;

/**
 * Description of GenericPermissionsCollectionTest
 *
 * @author Rotimi
 */
class GenericPermissionsCollectionTest extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void { 
        
        parent::setUp();
    }
    
    public function testConstructorWorksAsExcpected() {
        
        // no args
        $collection = new GenericPermissionsCollection();
        $this->assertEquals($collection->count(), 0);
        
        $permissions = [
            new GenericPermission('action-a', 'resource-a'),
            new GenericPermission('action-b', 'resource-b'),
            new GenericPermission('action-c', 'resource-c'),
        ];
        
        // args with array unpacking
        $collection2 = new GenericPermissionsCollection(...$permissions);
        $this->assertEquals($collection2->count(), 3);
        $this->assertTrue($collection2->hasPermission($permissions[0]));
        $this->assertTrue($collection2->hasPermission($permissions[1]));
        $this->assertTrue($collection2->hasPermission($permissions[2]));
        
        // multiple args non-array unpacking
        $collection3 = new GenericPermissionsCollection(
            $permissions[0], $permissions[1], $permissions[2]
        );
        $this->assertEquals($collection3->count(), 3);
        $this->assertTrue($collection3->hasPermission($permissions[0]));
        $this->assertTrue($collection3->hasPermission($permissions[1]));
        $this->assertTrue($collection3->hasPermission($permissions[2]));
    }
    
    public function testHasPermissionWorksAsExcpected() {
        
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
        $this->assertEquals($collection2->count(), 3);
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
    
    public function testIsAllowedWorksAsExcpected() {
        
        $calbackFalseNoArg = function() { return false; };
        $calbackTrueNoArg = function() { return true; };
        $calbackOneArg = function(bool $returnVal) { return $returnVal; };
        $calbackTwoArgs = function(bool $returnVal, bool $returnVal2) { 
            
            return $returnVal && $returnVal2; 
        };
        $calbackThreeArgs = function(bool $returnVal, bool $returnVal2, bool $returnVal3) { 
            
            return $returnVal && $returnVal2 && $returnVal3; 
        };
        
        $permissions = [
            new GenericPermission('action-a', 'resource-a'),
            new GenericPermission('action-b', 'resource-b'),
            new GenericPermission('action-c', 'resource-c'),
        ];
        $permissionNotAllowed = new GenericPermission('action-d', 'resource-d', false);
        $permissionNotAllowedByCallback = new GenericPermission('action-e', 'resource-e', true, $calbackFalseNoArg);
        $permissionAllowedIncludingCallback = new GenericPermission('action-f', 'resource-f', true, $calbackOneArg, true);
        
        // empty collection
        $collection = new GenericPermissionsCollection();
        $this->assertFalse($collection->isAllowed('action', 'resource'));
        
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackFalseNoArg));
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackTrueNoArg));
        
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackOneArg, false)); // 0
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackOneArg, true));  // 1
        
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackTwoArgs, false, false));    // 0
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackTwoArgs, false, true));     // 1
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackTwoArgs, true, false));     // 2
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackTwoArgs, true, true));      // 3

        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackThreeArgs, false, false, false));   // 0
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackThreeArgs, false, false, true));    // 1
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackThreeArgs, false, true, false));    // 2
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackThreeArgs, false, true, true));     // 3
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackThreeArgs, true, false, false));    // 4
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackThreeArgs, true, false, true));     // 5
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackThreeArgs, true, true, false));     // 6
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackThreeArgs, true, true, true));      // 7
        
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
        
        $this->assertTrue($collection->isAllowed('action-e', 'resource-e', $calbackTrueNoArg));     // existent not allowed perm by callback injected
                                                                                                    // in permission constructor but now allowed by
                                                                                                    // callback injected in this call which should
                                                                                                    // override the constructor injected callback
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $this->assertTrue($collection->isAllowed('action-f', 'resource-f'));                        // existent allowed perm by callback injected
                                                                                                    // in permission constructor
        
        $this->assertFalse($collection->isAllowed('action-f', 'resource-f', $calbackFalseNoArg));   // existent allowed perm by callback injected
                                                                                                    // in permission constructor but now disallowed by
                                                                                                    // callback injected in this call which should
                                                                                                    // override the constructor injected callback
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        $this->assertFalse($collection->isAllowed('action', 'resource', $calbackTrueNoArg));        // non-existent perm with truthy argless callback
        $this->assertTrue($collection->isAllowed('action-a', 'resource-a', $calbackTrueNoArg));     // existent perm with truthy argless callback
        
        $this->assertFalse($collection->isAllowed('action-a', 'resource-a', $calbackFalseNoArg));    // existent allowed perm now disallowed 
                                                                                                     // with falsy argless callback
        
        $this->assertFalse($collection->isAllowed('action-a', 'resource-a', $calbackOneArg, false)); // existent allowed perm now disallowed 
                                                                                                     // with falsy one arged callback
        
        $this->assertFalse($collection->isAllowed('action-a', 'resource-a', $calbackTwoArgs, true, false)); // existent allowed perm now disallowed 
                                                                                                            // with falsy two arged callback
        
        $this->assertFalse($collection->isAllowed('action-a', 'resource-a', $calbackThreeArgs, true, true, false)); // existent allowed perm now disallowed 
                                                                                                                    // with falsy three arged callback
        /////////////////////////////////////////////////////////
        // test super all actions and all resources permissions
        /////////////////////////////////////////////////////////
        $aPermission = new GenericPermission('action-1', 'resource-1');
        $allActionsOnAllResourcesPermission = new GenericPermission(
            GenericPermission::getAllActionsIdentifier(), GenericPermission::getAllResoucesIdentifier()
        );
        $allActionsOnOneResourcePermission = new GenericPermission(GenericPermission::getAllActionsIdentifier(), 'resource-a');
        $oneActionOnAllResourcesPermission = new GenericPermission('edit', GenericPermission::getAllResoucesIdentifier());
        
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
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackFalseNoArg)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $calbackFalseNoArg)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackFalseNoArg)); // falsy calback
        
        // existent action and non-existent resource with no arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackFalseNoArg)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $calbackFalseNoArg)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackFalseNoArg)); // falsy calback
        
        // non-existent action and existent resource with no arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackFalseNoArg)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $calbackFalseNoArg)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackFalseNoArg)); // falsy calback
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with 1 arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackOneArg, false)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $calbackOneArg, false)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackOneArg, false)); // falsy calback
        
        // existent action and non-existent resource with 1 arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackOneArg, false)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $calbackOneArg, false)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackOneArg, false)); // falsy calback
        
        // non-existent action and existent resource with 1 arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackOneArg, false)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $calbackOneArg, false)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackOneArg, false)); // falsy calback
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with 2 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackTwoArgs, false, true)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $calbackTwoArgs, false, true)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackTwoArgs, false, true)); // falsy calback
        
        // existent action and non-existent resource with 2 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackTwoArgs, false, true)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $calbackTwoArgs, false, true)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackTwoArgs, false, true)); // falsy calback
        
        // non-existent action and existent resource with 2 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackTwoArgs, false, true)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $calbackTwoArgs, false, true)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackTwoArgs, false, true)); // falsy calback
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with 3 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackThreeArgs, false, true, true)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource', $calbackThreeArgs, false, true, true)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource', $calbackThreeArgs, false, true, true)); // falsy calback
        
        // existent action and non-existent resource with 3 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackThreeArgs, false, true, true)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action-1', 'resource', $calbackThreeArgs, false, true, true)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action-1', 'resource', $calbackThreeArgs, false, true, true)); // falsy calback
        
        // non-existent action and existent resource with 3 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackThreeArgs, false, true, true)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedLast->isAllowed('action', 'resource-1', $calbackThreeArgs, false, true, true)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedLast->isAllowed('action', 'resource-1', $calbackThreeArgs, false, true, true)); // falsy calback
        
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
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackFalseNoArg)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $calbackFalseNoArg)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackFalseNoArg)); // falsy calback
        
        // existent action and non-existent resource with no arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackFalseNoArg)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $calbackFalseNoArg)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackFalseNoArg)); // falsy calback
        
        // non-existent action and existent resource with no arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackTrueNoArg)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackFalseNoArg)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $calbackFalseNoArg)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackFalseNoArg)); // falsy calback

        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with 1 arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackOneArg, false)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $calbackOneArg, false)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackOneArg, false)); // falsy calback
        
        // existent action and non-existent resource with 1 arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackOneArg, false)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $calbackOneArg, false)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackOneArg, false)); // falsy calback
        
        // non-existent action and existent resource with 1 arg callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackOneArg, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackOneArg, false)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $calbackOneArg, false)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackOneArg, false)); // falsy calback
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with 2 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackTwoArgs, false, true)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $calbackTwoArgs, false, true)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackTwoArgs, false, true)); // falsy calback
        
        // existent action and non-existent resource with 2 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackTwoArgs, false, true)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $calbackTwoArgs, false, true)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackTwoArgs, false, true)); // falsy calback
        
        // non-existent action and existent resource with 2 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackTwoArgs, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackTwoArgs, false, true)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $calbackTwoArgs, false, true)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackTwoArgs, false, true)); // falsy calback
        
        ////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////
        
        // non-existent action and non-existent resource with 3 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackThreeArgs, false, true, true)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource', $calbackThreeArgs, false, true, true)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource', $calbackThreeArgs, false, true, true)); // falsy calback
        
        // existent action and non-existent resource with 3 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackThreeArgs, false, true, true)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action-1', 'resource', $calbackThreeArgs, false, true, true)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action-1', 'resource', $calbackThreeArgs, false, true, true)); // falsy calback
        
        // non-existent action and existent resource with 3 args callback
        $this->assertTrue($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackThreeArgs, true, true, true)); // truthy calback
        $this->assertFalse($collectionWithAllActionsOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackThreeArgs, false, true, true)); // falsy calback
        $this->assertFalse($collectionWithAllActionsOnOneResourcePermissionAddedFirst->isAllowed('action', 'resource-1', $calbackThreeArgs, false, true, true)); // falsy calback
        $this->assertFalse($collectionWithOneActionOnAllResourcesPermissionAddedFirst->isAllowed('action', 'resource-1', $calbackThreeArgs, false, true, true)); // falsy calback
        
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
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $calbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackFalseNoArg)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $calbackTrueNoArg)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackTrueNoArg)); // truthy callback
        
        // 1 arged callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $calbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackOneArg, false)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $calbackOneArg, true)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackOneArg, true)); // truthy callback
        
        // 2 arged callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $calbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackTwoArgs, false, true)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $calbackTwoArgs, true, true)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackTwoArgs, true, true)); // truthy callback
        
        // 3 arged callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $calbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackThreeArgs, false, true, true)); // falsy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeAllActionsOnOneResourcePermission->isAllowed('action-2', 'resource-2', $calbackThreeArgs, true, true, true)); // truthy callback
        $this->assertFalse($collectionWithDeniedPermissionBeforeOneActionOnAllResourcesPermission->isAllowed('action-2', 'resource-2', $calbackThreeArgs, true, true, true)); // truthy callback
    }
    
    public function testAddWorksAsExcpected() {
        
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
    }
    
    public function testPutWorksAsExcpected() {
        
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
    
    public function testGetWorksAsExcpected() {
        
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
    
    public function testGetKeyWorksAsExcpected() {
        
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
        
        $this->assertEquals($collection->getKey($permissions[0]), 0);
        $this->assertEquals($collection->getKey($permissions[1]), 1);
        $this->assertEquals($collection->getKey($permissions[2]), 2);
        $this->assertEquals($collection->getKey($permission4), 3);
        $this->assertEquals($collection->getKey($permission5), 4);
        $this->assertEquals($collection->getKey($nonExistentPermission), null);
    }
    
    public function testRemoveWorksAsExcpected() {
        
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
        
        $this->assertEquals($collection->count(), 5);
        $collection->remove($nonExistentPermission);
        $this->assertEquals($collection->count(), 5);
        
        $this->assertTrue($collection->hasPermission($permissions[0]));
        $this->assertSame($collection->remove($permissions[0]), $collection);
        $this->assertFalse($collection->hasPermission($permissions[0]));
        $this->assertEquals($collection->count(), 4);
        
        $this->assertTrue($collection->hasPermission($permissions[1]));
        $this->assertSame($collection->remove($permissions[1]), $collection);
        $this->assertFalse($collection->hasPermission($permissions[1]));
        $this->assertEquals($collection->count(), 3);
        
        $this->assertTrue($collection->hasPermission($permissions[2]));
        $this->assertSame($collection->remove($permissions[2]), $collection);
        $this->assertFalse($collection->hasPermission($permissions[2]));
        $this->assertEquals($collection->count(), 2);
        
        $this->assertTrue($collection->hasPermission($permission4));
        $this->assertSame($collection->remove($permission4), $collection);
        $this->assertFalse($collection->hasPermission($permission4));
        $this->assertEquals($collection->count(), 1);
        
        $this->assertTrue($collection->hasPermission($permission5));
        $this->assertSame($collection->remove($permission5), $collection);
        $this->assertFalse($collection->hasPermission($permission5));
        $this->assertEquals($collection->count(), 0);
    }
    
    public function testRemoveAllWorksAsExcpected() {
        
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
        
        $this->assertEquals($collection->count(), 5);
        $collection->removeAll();
        $this->assertEquals($collection->count(), 0);
        
        $this->assertFalse($collection->hasPermission($permissions[0]));
        $this->assertFalse($collection->hasPermission($permissions[1]));
        $this->assertFalse($collection->hasPermission($permissions[2]));
        $this->assertFalse($collection->hasPermission($permission4));
        $this->assertFalse($collection->hasPermission($permission5));
    }
    
    
    /////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////
    // Test methods inherited from \SimpleAcl\GenericBaseCollection
    /////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////
    public function testGetIteratorWorksAsExcpected() {
        
        $collection = new GenericPermissionsCollection();
        $this->assertInstanceOf(\Traversable::class, $collection->getIterator());
    }

    public function testRemoveByKeyWorksAsExcpected() {
        
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

    public function testKeyExistsWorksAsExcpected() {
        
        $collection = new GenericPermissionsCollection();
        
        $perm1 = new GenericPermission('action-a', 'resource-a');
        $perm2 = new GenericPermission('action-b', 'resource-b');
        $perm3 = new GenericPermission('action-c', 'resource-c');
        
        $collection->add($perm1);
        $collection->add($perm2);
        $collection->add($perm3);
        
        $this->assertFalse($collection->keyExists('non-existent-key'));
        $this->assertFalse($collection->keyExists([]));
        $this->assertFalse($collection->keyExists(777));
        $this->assertTrue($collection->keyExists(0));
        $this->assertTrue($collection->keyExists(1));
        $this->assertTrue($collection->keyExists(2));
    }

    public function testCountWorksAsExcpected() {
        
        $collection = new GenericPermissionsCollection();
        
        $perm1 = new GenericPermission('action-a', 'resource-a');
        $perm2 = new GenericPermission('action-b', 'resource-b');
        $perm3 = new GenericPermission('action-c', 'resource-c');
        
        $this->assertEquals($collection->count(), 0);
        
        $collection->add($perm1);
        $collection->add($perm2);
        $collection->add($perm3);
        
        $this->assertEquals($collection->count(), 3);
    }
    
    public function testSortWorksAsExcpected() {
        
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

    public function testDumpWorksAsExcpected() {
        
        $collection = new GenericPermissionsCollection();
        
        $calbackTrueNoArg = function() { return true; };
        $calbackThreeArgs = function(bool $returnVal, bool $returnVal2, bool $returnVal3) { 
            
            return $returnVal && $returnVal2 && $returnVal3; 
        };
        
        $perm1 = new GenericPermission('action-a', 'resource-a');
        $perm2 = new GenericPermission('action-b', 'resource-b', true, $calbackThreeArgs, true, true, true);
        $perm3 = new GenericPermission('action-c', 'resource-c', false, $calbackTrueNoArg);
        
        $haystack = $collection->dump();
        $this->assertStringContainsString('SimpleAcl\GenericPermissionsCollection (', $haystack);
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        
        $collection->add($perm1);
        $collection->add($perm2);
        $collection->add($perm3);
        
        $haystack1 = $collection->dump();
        $this->assertStringContainsString('{', $haystack1);
       
        $this->assertStringContainsString("\titem[0]: SimpleAcl\GenericPermission (", $haystack1);
        $this->assertStringContainsString("\t{", $haystack1);
        $this->assertStringContainsString("\t\taction: `action-a`", $haystack1);
        $this->assertStringContainsString("\t\tresource: `resource-a`", $haystack1);
        $this->assertStringContainsString("\t\tallowActionOnResource: true", $haystack1);
        $this->assertStringContainsString("\t\tadditionalAssertions: NULL", $haystack1);
        $this->assertStringContainsString("\t\targsForCallback: array (", $haystack1);
        $this->assertStringContainsString("\t\t)", $haystack1);
        $this->assertStringContainsString("\t}", $haystack1);
       
        $this->assertStringContainsString("\titem[1]: SimpleAcl\GenericPermission (", $haystack1);
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
       
        $this->assertStringContainsString("\titem[2]: SimpleAcl\GenericPermission (", $haystack1);
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
        
        $this->assertStringNotContainsString("\titem[0]: SimpleAcl\GenericPermission (", $haystack2);
        $this->assertStringNotContainsString("\t{", $haystack2);
        $this->assertStringNotContainsString("\t\taction: `action-a`", $haystack2);
        $this->assertStringNotContainsString("\t\tresource: `resource-a`", $haystack2);
        $this->assertStringNotContainsString("\t\tallowActionOnResource: true", $haystack2);
        $this->assertStringNotContainsString("\t\tadditionalAssertions: NULL", $haystack2);
        $this->assertStringNotContainsString("\t\targsForCallback: array (", $haystack2);
        $this->assertStringNotContainsString("\t\t)", $haystack2);
        $this->assertStringNotContainsString("\t}", $haystack2);
       
        $this->assertStringNotContainsString("\titem[1]: SimpleAcl\GenericPermission (", $haystack2);
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
       
        $this->assertStringNotContainsString("\titem[2]: SimpleAcl\GenericPermission (", $haystack2);
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

    public function test__toStringWorksAsExcpected() {
        
        $collection = new GenericPermissionsCollection();
        
        $calbackTrueNoArg = function() { return true; };
        $calbackThreeArgs = function(bool $returnVal, bool $returnVal2, bool $returnVal3) { 
            
            return $returnVal && $returnVal2 && $returnVal3; 
        };
        
        $perm1 = new GenericPermission('action-a', 'resource-a');
        $perm2 = new GenericPermission('action-b', 'resource-b', true, $calbackThreeArgs, true, true, true);
        $perm3 = new GenericPermission('action-c', 'resource-c', false, $calbackTrueNoArg);
        
        $haystack = $collection->__toString();
        $this->assertStringContainsString('SimpleAcl\GenericPermissionsCollection (', $haystack);
        $this->assertStringContainsString('{', $haystack);
        $this->assertStringContainsString('}', $haystack);
        
        $collection->add($perm1);
        $collection->add($perm2);
        $collection->add($perm3);
        
        $haystack1 = $collection->__toString();
        $this->assertStringContainsString('{', $haystack1);
       
        $this->assertStringContainsString("\titem[0]: SimpleAcl\GenericPermission (", $haystack1);
        $this->assertStringContainsString("\t{", $haystack1);
        $this->assertStringContainsString("\t\taction: `action-a`", $haystack1);
        $this->assertStringContainsString("\t\tresource: `resource-a`", $haystack1);
        $this->assertStringContainsString("\t\tallowActionOnResource: true", $haystack1);
        $this->assertStringContainsString("\t\tadditionalAssertions: NULL", $haystack1);
        $this->assertStringContainsString("\t\targsForCallback: array (", $haystack1);
        $this->assertStringContainsString("\t\t)", $haystack1);
        $this->assertStringContainsString("\t}", $haystack1);
       
        $this->assertStringContainsString("\titem[1]: SimpleAcl\GenericPermission (", $haystack1);
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
       
        $this->assertStringContainsString("\titem[2]: SimpleAcl\GenericPermission (", $haystack1);
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
}
