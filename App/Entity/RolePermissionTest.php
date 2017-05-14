<?php
namespace Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\RolePermission;

class RolePermissionTest extends TestCase
{
	
	public function testCreateFromJson()
	{
		$permission = RolePermission::CreateWithFullPermissions();
		
		$this->assertTrue($permission->CanManageAdminWithRole(1));
		$this->assertTrue($permission->IsManageAdminRolesAllowed());
		$this->assertTrue($permission->CanChangeCredentials());
		$this->assertTrue(! $permission->IsReadOnlyAdmin());
		$this->assertTrue(! $permission->IsHelpDeskAdmin());
		$this->assertTrue($permission->CanAccessCompleteUI());

		
		$json = '{"manageAdminRolesAllowed":0,"manageAdminsOfRoles":[1,2,3],"changeOwnCredentialsAllowed":1,"readOnly":0,"helpdeskMode":0,"uiAccess":"ALL"}';
		$permission2 = RolePermission::CreateFromJSONString($json);
		
		$this->assertTrue( ! $permission2->CanManageAdminWithRole(4));
		$this->assertTrue( $permission2->CanManageAdminWithRole(3));
		
	}
	
	private function createReadOnlyPermission()
	{
		$json = '{"manageAdminRolesAllowed":1,"manageAdminsOfRoles":"ALL","changeOwnCredentialsAllowed":1,"readOnly":1,"helpdeskMode":0,"uiAccess":"ALL"}';
		$readOnlyPermission = RolePermission::CreateFromJSONString($json);
		
		return $readOnlyPermission;
	}
	
	public function testCompare()
	{
		$readOnlyPermission = $this->createReadOnlyPermission();
		
		$json = '{"manageAdminRolesAllowed":1,"manageAdminsOfRoles":"ALL","changeOwnCredentialsAllowed":1,"readOnly":0,"helpdeskMode":0,"uiAccess":"ALL"}';
		$allPermission2 = RolePermission::CreateFromJSONString($json);
		
		$this->assertEquals(10052, $readOnlyPermission->CompareRolePermissions($allPermission2));
		
		$readOnlyPermission->manageAdminRolesAllowed = 0;
		$this->assertEquals(10046, $readOnlyPermission->CompareRolePermissions($allPermission2));
		$readOnlyPermission->manageAdminRolesAllowed = 1;
		
		$readOnlyPermission->manageAdminsOfRoles = [2,3];		
		$this->assertEquals(10047, $readOnlyPermission->CompareRolePermissions($allPermission2));
		$readOnlyPermission->manageAdminsOfRoles = 'ALL';
		
		$readOnlyPermission->changeOwnCredentialsAllowed = 0;
		$this->assertEquals(10048, $readOnlyPermission->CompareRolePermissions($allPermission2));		
	}
	
	public function testCompareUIAccessibility()
	{
		$permission = RolePermission::CreateWithFullPermissions();		
		$permission2 = RolePermission::CreateWithFullPermissions();
		
		$this->assertEquals(0, $permission->CompareRolePermissions($permission2));
		
		$json = '{"manageAdminRolesAllowed":1,"manageAdminsOfRoles":"ALL","changeOwnCredentialsAllowed":1,"readOnly":0,"helpdeskMode":0,"uiAccess":["menus": ["dashboard", "settings", "users"]]}';
		$permission = RolePermission::CreateFromJSONString($json);
		
		
	}
	
	private function getJsonString()
	{
		$json = '{"manageAdminRolesAllowed":1,"manageAdminsOfRoles":"ALL","changeOwnCredentialsAllowed":1,"readOnly":0,"helpdeskMode":0,"uiAccess":"ALL"}';
		
		return $json;
	}
	
}