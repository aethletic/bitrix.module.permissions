<?php

namespace Bitrix\Permissions;

use Bitrix\Permissions\GroupsTable;
use Bitrix\Permissions\PermissionsTable;
use Bitrix\Permissions\GroupPermissionsTable;

require_once __DIR__ . '/groups_orm.php';
require_once __DIR__ . '/permissions_orm.php';
require_once __DIR__ . '/group_permissions_orm.php';

class Admin
{
    // TODO: отдавать только объект datamanger, а не выборку из бд
    public function getPermissionData()
    {
        return PermissionsTable::GetList(['select' => ['*']]);
    }

    public function getPermissionInstance()
    {
        return new PermissionsTable;
    }

    public function getGroupPermissionInstance()
    {
        return new GroupPermissionsTable;
    }

    public function getGroupPermissionData()
    {
        return GroupPermissionsTable::GetList(['select' => ['*']]);
    }

    public function getGroupsInstance()
    {
        return new GroupsTable;
    }

    public function getGroupsData()
    {
        return GroupsTable::GetList(['select' => ['*']]);
    }
}
?>
