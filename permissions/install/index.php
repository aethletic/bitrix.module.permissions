<?

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Модуль для работы с разрешениями пользователей
 */
class permissions extends CModule
{
    var $MODULE_ID = "permissions";
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_VERSION_DATE;
    var $MODULE_VERSION;

    /**
     * Иниц. модуля
     */
    function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__."/version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = Loc::getMessage("PERM_INDEX_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("PERM_INDEX_MODULE_DESCR");
    }

    /**
     * Создание таблиц
     */
    function InstallDB()
    {
        global $DB;

        $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/local/modules/{$this->MODULE_ID}/install/db/mysql/install_create.sql");
        $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/local/modules/{$this->MODULE_ID}/install/db/mysql/install_insert.sql");

        return true;
    }

    /**
     * Удаление таблиц
     */
    function UnInstallDB()
    {
        global $DB;

        $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/local/modules/{$this->MODULE_ID}/install/db/mysql/uninstall.sql");

        return true;
    }

    /**
     * Установка событий
     */
    function InstallEvents()
    {
        \Bitrix\Main\EventManager::getInstance()->registerEventHandlerCompatible(
            "main",
            "OnUserTypeBuildList",
            'permissions',
            '\\Bitrix\\Permissions\\Eventhandler',
            'GetUserTypeDescription'
        );

        return true;
    }

    /**
     * Удаление событий
     */
    function UnInstallEvents()
    {
        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler(
            "main",
            "OnUserTypeBuildList",
            "permissions",
            "\\Bitrix\\Permissions\\Eventhandler",
            "GetUserTypeDescription"
        );
        return true;
    }

    /**
     * Создание файлов модуля
     */
    function InstallFiles()
    {
        return true;
    }

    /**
     * Удаление файлов модуля
     */
    function UnInstallFiles()
    {
        return true;
    }

    /**
     * Установка модуля
     * 1. Создание таблиц
     * 2. Создание событий
     * 3. Создание файлов
     * 4. Регистрация модуля
     */
    function DoInstall()
    {
        $this->InstallDB();
        $this->InstallFiles();
        $this->InstallEvents();

        $requireStr['permissions'] = <<<STR
<?php
    require("{$_SERVER['DOCUMENT_ROOT']}/local/modules/{$this->MODULE_ID}/admin/idem_permissions_admin.php");
?>
STR;

        $requireStr['groups'] = <<<STR
<?php
    require("{$_SERVER['DOCUMENT_ROOT']}/local/modules/{$this->MODULE_ID}/admin/idem_groups_admin.php");
?>
STR;

        $requireStr['group_perms'] = <<<STR
<?php
    require("{$_SERVER['DOCUMENT_ROOT']}/local/modules/{$this->MODULE_ID}/admin/idem_group_perms_admin.php");
?>
STR;

        $requireStr['fields_edit'] = <<<STR
<?php
    require("{$_SERVER['DOCUMENT_ROOT']}/local/modules/{$this->MODULE_ID}/admin/fields_edit.php");
?>
STR;

        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/idem_permissions_admin.php', $requireStr['permissions']);
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/idem_groups_admin.php', $requireStr['groups']);
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/idem_group_perms_admin.php', $requireStr['group_perms']);
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/fields_edit.php', $requireStr['fields_edit']);

        RegisterModule($this->MODULE_ID);

        // добавление пользовательских полей
        (new \CUserTypeEntity())->Add(array(
            'ENTITY_ID'         => 'USER',
            'FIELD_NAME'        => 'UF_PERMISSIONS',
            'USER_TYPE_ID'      => 'permissions_control',
            'SORT'              => 100,
            'SETTINGS'          => array(
                'DEFAULT_VALUE' => '1',
            ),
            'EDIT_FORM_LABEL'   => array(
                'ru'    => 'Права доступа',
                'en'    => '(EN) Права доступа',
            ),
        ));

        global $APPLICATION;
        $APPLICATION->IncludeAdminFile(Loc::getMessage("PERM_INDEX_INSTALL_TITLE"), $_SERVER['DOCUMENT_ROOT'] . "/local/modules/{$this->MODULE_ID}/install/step.php");
    }

    /**
     * Удаление модуля
     * NOTE: Удаление происходит в обратном порядке:
     * 1. Удаление модуля
     * 2. Удаление файлов
     * 3. Удаление событий
     * 4. Удаление таблиц
     */
    function DoUninstall()
    {
        UnRegisterModule($this->MODULE_ID);

        unlink($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/idem_permissions_admin.php');
        unlink($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/idem_groups_admin.php');
        unlink($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/idem_group_perms_admin.php');
        unlink($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/fields_edit.php');

        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallDB();

        // удаление пользовательских полей
        $res = CUserTypeEntity::GetList(
            array(),
            array('FIELD_NAME' => 'UF_PERMISSIONS')
        );
        if (sizeof($field = $res->fetch()) > 0) {
            (new \CUserTypeEntity())->Delete($field['ID']);
        }

        global $APPLICATION;
        $APPLICATION->IncludeAdminFile(Loc::getMessage("PERM_INDEX_UNINSTALL_TITLE"), $_SERVER['DOCUMENT_ROOT'] . "/local/modules/{$this->MODULE_ID}/install/unstep.php");
    }
}
?>
