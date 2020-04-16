<?php
namespace Bitrix\Permissions;

use Bitrix\Main\Entity;

class GroupsTable extends Entity\DataManager
{
   public static function getTableName()
   {
      return 'groups';
   }

   public static function getMap()
   {
      return array(
         'group_id' => array(
            'data_type' => 'integer',
            'primary' => true,
            'autocomplete' => true,
         ),
         'group_code' => array(
            'data_type' => 'text',
        ),
         'name' => array(
            'data_type' => 'text',
        ),
      );
   }
}
