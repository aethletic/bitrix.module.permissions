<?php

use Bitrix\Permissions\Admin;
use Bitrix\Permissions\Permissions;

if (!CModule::IncludeModule('permissions')) {
    echo '<strong>Модуль "permissions" не найден.</strong>';
    return;
}

// start tests

// test 1
$checkByGroupId = [];
$checkByGroupId['Корректный запрос'] = checkByGroupIdTest(
    $groupId = 1,
    $permissionCodes = ['view_aip_information','UNKNOWN_CODE','site_information_update'], // UNKNOWN_CODE disallow
    $result = ['view_aip_information', 'site_information_update']
);
$checkByGroupId['Несуществующий ID группы'] = checkByGroupIdTest(
    $groupId = 999999, // id not exist
    $permissionCodes = ['view_aip_information'],
    $result = []
);
$checkByGroupId['ID группы как строка'] = checkByGroupIdTest(
    $groupId = '123', // string invalid
    $permissionCodes = ['view_aip_information'],
    $result = false
);
$checkByGroupId['Пустой массив правил'] = checkByGroupIdTest(
    $groupId = '123',
    $permissionCodes = [], // empty perm. codes
    $result = false
);
$checkByGroupId['Пустой ID группы и правил'] = checkByGroupIdTest(
    $groupId = null, // empty group id
    $permissionCodes = [],
    $result = false
);
$checkByGroupId['Отрицательное число ID группы'] = checkByGroupIdTest(
    $groupId = -123213, // invalid group id
    $permissionCodes = ['view_aip_information'],
    $result = false
);
$checkByGroupId['Правило как строка'] = checkByGroupIdTest(
    $groupId = 1,
    $permissionCodes = 'view_aip_information', // perm. code as string (only for 1 code)
    $result = ['view_aip_information']
);

function checkByGroupIdTest($groupId = null, $permissionCodes = [], $result = [])
{
    return Permissions::checkByGroupId($groupId, $permissionCodes) === $result;
}

// test 2
$checkByUserId = [];
$checkByUserId['Корректный запрос'] = checkByUserIdTest(
    $userId = 1,
    $permissionCodes = ['view_aip_information','UNKNOWN_CODE'], // UNKNOWN_CODE disallow
    $result = ['view_aip_information']
);
$checkByUserId['Несуществующий ID пользователя'] = checkByUserIdTest(
    $userId = 999999, // id not exist
    $permissionCodes = ['view_aip_information'],
    $result = false
);
$checkByUserId['ID пользователя как строка'] = checkByUserIdTest(
    $userId = '123', // string invalid
    $permissionCodes = ['view_aip_information'],
    $result = false
);
$checkByUserId['Пустой массив правил'] = checkByUserIdTest(
    $userId = '123',
    $permissionCodes = [], // empty perm. codes
    $result = false
);
$checkByUserId['Пустой ID пользователя и правил'] = checkByUserIdTest(
    $userId = null, // empty group id
    $permissionCodes = null,
    $result = false
);
$checkByUserId['Отрицательное число ID пользователя'] = checkByUserIdTest(
    $userId = -123213, // invalid group id
    $permissionCodes = ['view_aip_information'],
    $result = false
);
$checkByUserId['Правило как строка'] = checkByUserIdTest(
    $userId = 1,
    $permissionCodes = 'view_aip_information', // perm. code as string (only for 1 code)
    $result = ['view_aip_information']
);

function checkByUserIdTest($userId = null, $permissionCodes = [], $result = [])
{
    return Permissions::checkByUserId($userId, $permissionCodes) === $result;
}

// test 3
$getAllCodesByGroupId = [];
$getAllCodesByGroupId['Корректный запрос'] = getAllCodesByGroupIdTest(
    $groupId = 2,
    $result = [
        'view_aip_information',
        'processing_applications_from_users',
        'payment_of_bills',
        'view_documents',
        'contacting_company',
        'posting_service_information',
    ]
);
$getAllCodesByGroupId['Несуществующий ID группы'] = getAllCodesByGroupIdTest(
    $groupId = 999999,
    $result = false
);
$getAllCodesByGroupId['Пустой ID группы'] = getAllCodesByGroupIdTest(
    $groupId = null,
    $result = false
);
$getAllCodesByGroupId['ID группы как строка'] = getAllCodesByGroupIdTest(
    $groupId = '1',
    $result = false
);
$getAllCodesByGroupId['Отрицательное число ID группы'] = getAllCodesByGroupIdTest(
    $groupId = -1,
    $result = false
);
$getAllCodesByGroupId['ID группы как массив'] = getAllCodesByGroupIdTest(
    $groupId = ['array'],
    $result = false
);

function getAllCodesByGroupIdTest($groupId = null, $result = [])
{
    return Permissions::getAllCodesByGroupId($groupId) === $result;
}

// test 4
$getAllCodesByUserId = [];
$getAllCodesByUserId['Корректный запрос'] = getAllCodesByUserIdTest(
    $userId = 1,
    $result = [
        'view_aip_information',
        'processing_applications_from_users',
        'payment_of_bills',
        'view_documents',
        'contacting_company',
        'posting_service_information',
    ]
);

$getAllCodesByUserId['Несуществующий ID пользователя'] = getAllCodesByUserIdTest(
    $userId = 999999,
    $result = false
);
$getAllCodesByUserId['Пустой ID пользователя'] = getAllCodesByUserIdTest(
    $userId = null,
    $result = false
);
$getAllCodesByUserId['ID пользователя как строка'] = getAllCodesByUserIdTest(
    $userId = '1',
    $result = false
);
$getAllCodesByUserId['Отрицательное число ID пользователя'] = getAllCodesByUserIdTest(
    $userId = -1,
    $result = false
);
$getAllCodesByUserId['ID пользователя как массив'] = getAllCodesByUserIdTest(
    $userId = ['array'],
    $result = false
);

function getAllCodesByUserIdTest($userId = null, $result = [])
{
    return Permissions::getAllCodesByUserId($userId) === $result;
}

// test 5
$getGroupIdByUserId = [];
$getGroupIdByUserId['Получение ID (2) группы  по ID (1) пользователя'] = getGroupIdByUserIdTest(
    $userId = 1, // юзер с id 1, должен иметь группу правил с id 2
    $result = 2
);
$getGroupIdByUserId['Несуществующий ID пользователя'] = getGroupIdByUserIdTest(
    $userId = 99999,
    $result = false
);
$getGroupIdByUserId['Пустое значение ID пользователя'] = getGroupIdByUserIdTest(
    $userId = null,
    $result = false
);
$getGroupIdByUserId['ID пользователя в виде массива'] = getGroupIdByUserIdTest(
    $userId = ['id'],
    $result = false
);
function getGroupIdByUserIdTest($userId = null, $result = [])
{
    return Permissions::getGroupIdByUserId($userId) === $result;
}

// end tests

$aTabs = array(
    array(
        'DIV' => 'permissions_tests',
        'TAB' => "Тестирование модуля",
    )
);
$tabControl = new CAdminTabControl('tabControl', $aTabs);
?>


<!-- render html -->
<?
$tabControl->Begin();

foreach ($aTabs as $aTab) {
    $tabControl->BeginNextTab();
}

echo '<strong>Проверка наличия правил по ID группы</strong><br>';
echo '<strong>Метод: </strong> <code>Permissions::checkByGroupId()</code><br>';
foreach ($checkByGroupId as $key => $value) {
    echo $value ? "✅ {$key}<br>"  : "❌ {$key}<br>";
}

echo '<hr><strong>Проверка наличия правил по ID пользователя</strong><br>';
echo '<strong>Метод: </strong> <code>Permissions::checkByUserId()</code><br>';
foreach ($checkByUserId as $key => $value) {
    echo $value ? "✅ {$key}<br>"  : "❌ {$key}<br>";
}

echo '<hr><strong>Получение всех правил по ID группы</strong><br>';
echo '<strong>Метод: </strong> <code>Permissions::getAllCodesByGroupId()</code><br>';
foreach ($getAllCodesByGroupId as $key => $value) {
    echo $value ? "✅ {$key}<br>"  : "❌ {$key}<br>";
}

echo '<hr><strong>Получение всех правил по ID пользователя</strong><br>';
echo '<strong>Метод: </strong> <code>Permissions::getAllCodesByUserId()</code><br>';
foreach ($getAllCodesByUserId as $key => $value) {
    echo $value ? "✅ {$key}<br>"  : "❌ {$key}<br>";
}

echo '<hr><strong>Получение ID группы по ID пользователя</strong><br>';
echo '<strong>Метод: </strong> <code>Permissions::getGroupIdByUserId()</code><br>';
foreach ($getGroupIdByUserId as $key => $value) {
    echo $value ? "✅ {$key}<br>"  : "❌ {$key}<br>";
}

$tabControl->End();
?>
