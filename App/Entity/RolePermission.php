<?php

namespace App\Entity;

class RolePermission
{

    private $permission;

    private function __construct($json)
    {
        $this->permission = $this->parseJSON( $json );
    }

    private function parseJSON($json)
    {
        return json_decode( $json, true );
    }

    public function __set($key, $value)
    {
        if (isset( $this->permission[$key] ))
        {
            $this->permission[$key] = $value;
        }
    }

    public static function CreateFromJSONString($json)
    {
        return new self( $json );
    }

    public static function CreateWithFullPermissions()
    {
        $json = '{"manageAdminRolesAllowed":1,"manageAdminsOfRoles":"ALL","changeOwnCredentialsAllowed":1,"readOnly":0,"helpdeskMode":0,"uiAccess":"ALL"}';
        return self::CreateFromJSONString( $json );
    }

    public static function CreateFromCurrentAdminPermission()
    {
        // fetch permission-json string of current admin & create instance
        $json = '';
        return new self( $json );
    }

    public function IsReadOnlyAdmin()
    {
        return $this->permission['readOnly'] == 1;
    }

    public function IsManageAdminRolesAllowed()
    {
        return $this->permission['manageAdminRolesAllowed'] == 1;
    }

    public function IsHelpDeskAdmin()
    {
        return $this->permission['helpdeskMode'] == 1;
    }

    public function CanChangeCredentials()
    {
        return $this->permission['changeOwnCredentialsAllowed'] == 1;
    }

    public function CanAccessCompleteUI()
    {
        return $this->permission['uiAccess'] == 'ALL';
    }

    public function CanManageAllAdminRoles()
    {
        return $this->permission['manageAdminsOfRoles'] == 'ALL';
    }

    public function CanManageAdminWithRole($roleId)
    {
        $manageAdminsOfRoles = $this->permission['manageAdminsOfRoles'];

        if ($manageAdminsOfRoles == 'ALL')
        {
            return true;
        }

        if (is_array( $manageAdminsOfRoles ) && in_array( $roleId, $manageAdminsOfRoles ))
        {
            return true;
        }

        return false;
    }

    public function CompareRolePermissions(RolePermission $role2)
    {
        // manageAdminRolesAllowed
        if (! $this->IsManageAdminRolesAllowed() && $role2->IsManageAdminRolesAllowed())
        {
            return 10046;
        }

        // manageAdminsOfRoles
        if (! $this->CanManageAllAdminRoles())
        {
            $manageAdminsOfRoles = $this->permission['manageAdminsOfRoles'];
            $role2ManageAdminsOfRoles = $role2->permission['manageAdminsOfRoles'];

            if ($role2->CanManageAllAdminRoles() || ! $this->arrayContains( $manageAdminsOfRoles, $role2ManageAdminsOfRoles ))
            {
                return 10047;
            }
        }

        // changeOwnCredentialsAllowed
        if (! $this->CanChangeCredentials() && $role2->CanChangeCredentials())
        {
            return 10048;
        }

        // uiAccess
        if (! $this->CanAccessCompleteUI())
        {
            if ($role2->CanAccessCompleteUI())
            {
                return 10065;
            }

            if (! $this->CompareAccessibleUIMenus( $role2 ))
            {
                return 10049;
            }
            if (! $this->CompareAccessibleUIViews( $role2 ))
            {
                return 10049;
            }
        }

        // readOnly
        if ($this->IsReadOnlyAdmin() && ! $role2->IsReadOnlyAdmin())
        {
            return 10052;
        }

        // helpdeskMode
        if ($this->IsHelpDeskAdmin() && ! $role2->IsHelpDeskAdmin())
        {
            return 10058;
        }

        return 0;
    }

    public function CompareAccessibleUIMenus(RolePermission $role2)
    {
        return $this->arrayContains( $this->GetAccessibleUIMenus(), $role2->GetAccessibleUIMenus() );
    }

    public function CompareAccessibleUIViews(RolePermission $role2)
    {
        $accessibleViews = $this->GetAccessibleUIViews();
        $role2AccessibleViews = $role2->GetAccessibleUIViews();

        foreach ( $role2AccessibleViews as $menuName => $submenus )
        {
            if (! array_key_exists( $menuName, $accessibleViews ))
            {
                return 10050;
            }

            if (! $this->arrayContains( $accessibleViews[$menuName], $submenus ))
            {
                return 10051;
            }
        }
    }

    public function GetAccessibleUIMenus()
    {
        $menus = [];
        if (isset( $this->permission['uiAccess']['menu'] ))
        {
            $menus = $this->permission['uiAccess']['menu'];
        }

        return $menus;
    }

    public function GetAccessibleUIViews()
    {
        $views = [];
        if (isset( $this->permission['uiAccess']['views'] ))
        {
            $views = $this->permission['uiAccess']['views'];
        }

        return $views;
    }

    private function arrayContains($big, $small)
    {
        $bigCount = $big;
        $smallCount = count( $small );

        if ($bigCount < $smallCount)
        {
            return false;
        }

        foreach ( $small as $e )
        {
            if (! in_array( $e, $big ))
            {
                return false;
            }
        }

        return true;
    }

    public function ToJson()
    {
        return json_encode( $this->permission );
    }
}