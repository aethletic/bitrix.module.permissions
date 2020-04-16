<?php

// напрямую классы бд здесь не работают,
// поэтому обращение к ним через класс Admin
use Bitrix\Permissions\Admin;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
require_once __DIR__ . "/../lib/admin.php";

CModule::IncludeModule('permissions');

$POST_RIGHT = $APPLICATION->GetGroupRight("permissions");
if ($POST_RIGHT == "D")
    $APPLICATION->AuthForm("Доступ запрещен");

$sTableID = "permissions";
$oSort = new CAdminSorting($sTableID, "permissions_id", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

// TODO: сделать фильтр

if($lAdmin->EditAction() && $POST_RIGHT=="W")
{
    foreach($FIELDS as $ID=>$arFields)
    {
        if(!$lAdmin->IsUpdated($ID))
            continue;

        $DB->StartTransaction();
        $ID = IntVal($ID);
        $cData = Admin::getPermissionInstance();
        if(($rsData = $cData->GetByID($ID)) && ($arData = $rsData->Fetch()))
        {
            foreach($arFields as $key=>$value)
                $arData[$key]=$value;
            if(!$cData->Update($ID, $arData))
            {
                $lAdmin->AddGroupError("Какая-то ошибка: ".$cData->LAST_ERROR, $ID);
                $DB->Rollback();
            }
        }
        else
        {
            $lAdmin->AddGroupError("Чего-то нет", $ID);
            $DB->Rollback();
        }
        $DB->Commit();
    }
}

if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W")
{
    if($_REQUEST['action_target']=='selected')
    {
        $rsData = Admin::getPermissionData();
        while($arRes = $rsData->Fetch())
            $arID[] = $arRes['ID'];
    }

    foreach($arID as $ID)
    {
        if(strlen($ID)<=0)
            continue;
       	$ID = IntVal($ID);

        switch($_REQUEST['action'])
        {
        case "delete":
            @set_time_limit(0);
            $DB->StartTransaction();
            if(!Admin::getPermissionInstance()::Delete($ID))
            {
                $DB->Rollback();
                $lAdmin->AddGroupError("Какая-то ошибка", $ID);
            }
            $DB->Commit();
            break;
        }

    }
}

$rsData = Admin::getPermissionData();
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint("Страница"));

$lAdmin->AddHeaders(array(
  array(  "id"    =>"permission_id",
    "content"  =>"ID",
    "sort"     =>"auto",
    "default"  =>true,
  ),
  array(  "id"    =>"permission_code",
    "content"  =>"Код",
    "sort"     =>"id",
    "default"  =>true,
  ),
  array(  "id"    =>"name",
    "content"  =>"Название",
    "sort"     =>"id",
    "default"  =>true,
  ),
));

while($arRes = $rsData->NavNext(true, 'f_')):
  $row =& $lAdmin->AddRow($f_permission_id, $arRes);
  $row->AddInputField("permission_id", array("size"=>20));
  $row->AddInputField("permission_code", array("size"=>20));
  $row->AddInputField("name", array("size"=>20));

  $arActions = array();
  $arActions[] = array(
    "ICON"=>"edit",
    "DEFAULT"=>true,
    "TEXT"=>"Редактировать",
    "ACTION"=>$lAdmin->ActionRedirect("fields_edit.php?id=".$f_permission_id."&table=permissions&code=".$f_permission_code."&name=".$f_name."&edit=Y")
  );

  if ($POST_RIGHT>="W")
    $arActions[] = array(
      "ICON"=>"delete",
      "TEXT"=>"Удалить",
      "ACTION"=>"if(confirm('Вы уверены, что хотите удалить?')) ".$lAdmin->ActionDoGroup($f_permission_id, "delete")
    );

  $row->AddActions($arActions);
endwhile;

$lAdmin->AddGroupActionTable(Array(
    "delete"=>"Удалить", // удалить выбранные элементы
));

$aContext = array(
    array(
        "TEXT"=>"Добавить",
        "LINK"=>"fields_edit.php?edit=N&table={$sTableID}&lang=".LANG,
        "TITLE"=>"Добавить новое разрешение",
        "ICON"=>"btn_new",
    ),
);
$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle("Разрешения");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // второй общий пролог

// здесь будет вывод страницы
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
