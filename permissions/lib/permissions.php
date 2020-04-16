<?php

namespace Bitrix\Permissions;

use Bitrix\Permissions\PermissionsTable;
use Bitrix\Permissions\GroupPermissionsTable;

require_once __DIR__ . '/permissions_orm.php';
require_once __DIR__ . '/group_permissions_orm.php';

/**
 * Класс проверки разрешений пользователей.
 */
class Permissions
{
    /**
     * Возращает массив с кодами, которые существуют в переданной группе.
     * Например:
     *      Передаем данные: ['a', 'b', 'c']
     *      Возвращается массив: ['a', 'b']
     *      Коды 'a', 'b' существуют в группе - доступ для них разрешен.
     *      Кода 'c' не существует в группе - доступ для него запрещен.
     *
     * @param  int           $groupId
     * @param  string|array  $permissionCodes
     * @return array|bool    Array в случае успеха, False в случае ошибки
     */
    public function checkByGroupId($groupId = null, $permissionCodes = [])
    {
        if (!self::isValidInteger($groupId))
            return false;

        if (!is_array($permissionCodes))
            $permissionCodes = [$permissionCodes];

        if (sizeof($permissionCodes) == 0)
            return false;

        $result = PermissionsTable::GetList([
            'select' => ['*'],
            'filter' => ['permission_code' => $permissionCodes],
        ])->fetchAll();

        $permissionIds = [];
        $permissionData = [];
        foreach ($result as $value) {
            // заменяет ключи массива на permission_id,
            // чтобы по ним выбрать существующие данные в конце
            $permissionData[$value['permission_id']] = $value;

            // собираем в один массив все id доступов для поиска
            $permissionIds[] = $value['permission_id'];
        }

        $result = GroupPermissionsTable::GetList([
            'select' => ['*'],
            'filter' => ['group_id' => $groupId, 'permission_id' => $permissionIds],
        ])->fetchAll();

        $rsAccess = array_map(function($value) use ($permissionData) {
            return $permissionData[$value['permission_id']]['permission_code'];
        }, $result);

        return $rsAccess;
    }

    /**
     * Аналог метода checkByGroupId()
     * @param  int           $userId
     * @param  string|array  $permissionCodes
     * @return array|bool    Array в случае успеха, False в случае ошибки
     */
    public function checkByUserId($userId = null, $permissionCodes = [])
    {
        if (!self::isValidInteger($userId))
            return false;

        $groupId = self::getGroupIdByUserId($userId);

        if (!$groupId)
            return false;

        $rsAccess = self::checkByGroupId($groupId, $permissionCodes);

        return $rsAccess;
    }

    /**
     * Получить все коды разрешений по id группы
     * @param  int        $groupId
     * @return array|bool Array в случае успеха, False в случае ошибки
     */
    public function getAllCodesByGroupId($groupId = null)
    {
        if (!self::isValidInteger($groupId))
            return false;

        $result = GroupPermissionsTable::GetList([
            'select' => ['permission_id'],
            'filter' => ['group_id' => $groupId],
        ])->fetchAll();

        if (sizeof($result) == 0)
            return false;

        $permissionIds = array_map(function($value) {
            return $value['permission_id'];
        }, $result);

        $result = PermissionsTable::GetList([
            'select' => ['permission_code'],
            'filter' => ['permission_id' => $permissionIds],
        ])->fetchAll();

        if (sizeof($result) == 0)
            return false;

        $rsCodes = [];
        $rsCodes = array_map(function($value) {
            return $value['permission_code'];
        }, $result);

        return $rsCodes;
    }

    /**
     * Получить все правила по id пользователя
     * @param  int        $userId
     * @return array|bool Array в случае успеха, False в случае ошибки
     */
    public function getAllCodesByUserId($userId = null)
    {
        if (!self::isValidInteger($userId))
            return false;

        $groupId = self::getGroupIdByUserId($userId);
        $rsCodes = self::getAllCodesByGroupId($groupId);

        return $rsCodes;
    }

    /**
     * Получить id группы по id пользователя
     * @param  int      $userId
     * @return int|bool Int в случае успеха, False в случае ошибки
     */
    public function getGroupIdByUserId($userId = null)
    {
        if (!self::isValidInteger($userId))
            return false;

        $userData = \CUser::GetByID($userId)->Fetch();

        if (!array_key_exists('UF_PERMISSIONS', $userData))
            return false;

        if (empty($userData['UF_PERMISSIONS']))
            return false; // если id группы пользовтаеля стоит как "не выбрано"

        $groupId = (int) $userData['UF_PERMISSIONS'];

        return $groupId;
    }

    /**
     * Утилитарный метод проверки является ли параметр числом
     * @param  int  $int
     * @return bool
     */
    private function isValidInteger($int = null)
    {
        if ($int === null)
            return false;

        if (!is_integer($int))
            return false;

        if ($int < 0)
            return false;

        return true;
    }
}
?>
