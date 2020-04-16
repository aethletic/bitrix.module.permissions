<?php
namespace Bitrix\Permissions;

use Bitrix\Main\Entity;

class PermissionsTable extends Entity\DataManager
{
   public static function getTableName()
   {
      return 'permissions';
   }

   public static function getMap()
   {
      return array(
         'permission_id' => array(
            'data_type' => 'integer',
            'primary' => true,
            'autocomplete' => true,
            'title' => 'ID',
         ),
         'permission_code' => array(
            'data_type' => 'text',
            'title' => 'Код',
         ),
         'name' => array(
            'data_type' => 'text',
            'title' => 'Название',
         )
      );
   }
}
