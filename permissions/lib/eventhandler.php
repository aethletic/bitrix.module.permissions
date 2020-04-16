<?php
namespace Bitrix\Permissions;

class Eventhandler {
    function GetEditFormHTML($arUserField, $arHtmlControl) {
        $iIBlockId = intval($arUserField['SETTINGS']['IBLOCK_ID']);
        $sReturn = '';

        $rElem = $GLOBALS['DB']->query("SELECT * FROM groups");
        $groupData = [];
        while ($arElem = $rElem->fetch()) {
            $groupData[] = $arElem;
        }

        $sReturn .= '<select name="'.$arHtmlControl['NAME'].'"><option value="">Не выбрано</option>';
        foreach ($groupData as $value) {
            $sReturn .= '<option value="'.$value['group_id'].'" '.($arHtmlControl['VALUE'] == $value['group_id'] ? 'selected': '').'>'.$value['name'].'</option>';
        }
        $sReturn .= '</select>';
        return $sReturn;
    }

    function GetDBColumnType($arUserField) {
        switch(strtolower($GLOBALS['DB']->type)) {
            case 'mysql':
                return 'varchar(255)';
            break;
            case 'oracle':
                return 'varchar(255)';
            break;
        }
    }

    function GetUserTypeDescription() {
        return array(
            'USER_TYPE_ID' => 'permissions_control',
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => 'Привязка к группе доступа',
            'BASE_TYPE' => 'string',
        );
    }
}
