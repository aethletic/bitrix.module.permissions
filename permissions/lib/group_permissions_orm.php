<?php
namespace Bitrix\Permissions;

use Bitrix\Main\Entity;

class GroupPermissionsTable extends Entity\DataManager
{
   public static function getTableName()
   {
      return 'group_permissions';
   }

   public static function getMap()
   {
      return array(
         'group_id' => array(
            'data_type' => 'integer',
            'primary' => true,
         ),
         'permission_id' => array(
            'data_type' => 'integer',
        ),
      );
   }
}
