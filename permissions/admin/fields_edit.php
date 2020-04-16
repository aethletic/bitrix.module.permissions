<?

// напрямую классы бд здесь не работают,
// поэтому обращение к ним через класс Admin
use Bitrix\Permissions\Admin;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
require_once __DIR__ . "/../lib/admin.php";

CModule::IncludeModule('permissions');

$POST_RIGHT = $APPLICATION->GetGroupRight("permissions");
if ($POST_RIGHT == "D")
    $APPLICATION->AuthForm("Доступ запрещен");
?>

<?
// для вставки в текст страницы
$edit_table_name = $_GET['table'] == 'groups' ? 'группы' : 'разрешения';

if ($_GET['edit'] == "Y") {
    $aTabs[] = array("DIV" => "edit", "TAB" => "Редактировать", "ICON"=>"main_user_edit", "TITLE"=>"Редактировать $edit_table_name");
} else {
    $aTabs[] = array("DIV" => "add", "TAB" => "Добавить", "ICON"=>"main_user_edit", "TITLE"=>"Добавить $edit_table_name");
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$message = null;
$bVarsFromForm = false;

// TODO: сделать валидацию на входящие данные
// TODO: показывать сообщение если входящие данные не корректные
if(
    $REQUEST_METHOD == "POST"
    &&
    ($save!="" || $apply!="")
    &&
    $POST_RIGHT=="W"
    &&
    check_bitrix_sessid()
)
{
    if ($_POST['table'] == 'groups') {
        $ORM = Admin::getGroupsInstance();
    } else {
        $ORM = Admin::getPermissionInstance();
    }

  // имя groups меняем на group
  $table = substr($_POST['table'], 0, -1);
  $arFields = Array(
    "{$table}_id" => $_POST['id'],
    "{$table}_code" => $_POST['code'],
    "name" => $_POST['name'],
  );

  // обновление данных
  if($_POST['action'] == 'update')
  {
     if ($_POST['delete'] == 'Y') {
         $res = $ORM::delete($_POST['id']);
     } else {
         $res = $ORM::update($_POST['id'], $arFields);

         if (array_key_exists('group_permissions', $_POST) && $res->isSuccess()) {
             Admin::getGroupPermissionInstance()::delete($_POST['id']); // удаляем всё, где primary ключ id группы
             // и добавляем заново уже измененные данные
             foreach ($_POST['group_permissions'] as $perm_id) {
                 Admin::getGroupPermissionInstance()::add(['group_id' => $_POST['id'], 'permission_id' => $perm_id]);
             }
         }
     }
  }
  else // тогда вставляем новые данные
  {
    $success = $ORM::add($arFields);

    if (array_key_exists('group_permissions', $_POST) && $success->isSuccess()) {
        $group_id = $success->getId();
        foreach ($_POST['group_permissions'] as $perm_id) {
            Admin::getGroupPermissionInstance()::add(['group_id' => $group_id, 'permission_id' => $perm_id]);
        }
    }
    $res = ($success > 0);
  }

  if($res)
  {
    if ($apply != "" && $_POST['action'] !== 'add' && $_POST['delete'] !== 'Y')
     LocalRedirect("/bitrix/admin/fields_edit.php?status=ok&lang=".LANG."&table={$_POST['table']}&id={$_POST['id']}&code={$_POST['code']}&name={$_POST['name']}&edit=Y");
    else
      LocalRedirect("/bitrix/admin/idem_{$_POST['table']}_admin.php?from=save&lang=".LANG);
  }
  else
  {
    if ($e = $APPLICATION->GetException()) {
        $message = new CAdminMessage("Ошибка при сохранении", $e);
    }
    $bVarsFromForm = true;
  }
}

$aMenu = array(
  array(
    "TEXT"  => "Назад к списку",
    "TITLE" => "Вернуться назад",
    "LINK"  => "idem_{$_GET['table']}_admin.php?lang=".LANG,
    "ICON"  => "btn_list",
  )
);

// данные для селекта
if ($_REQUEST['table'] == 'groups') {
    $permsData = Admin::getPermissionData()->fetchAll();
    // по этому массиву определить какие есть права и отметить их в селекте как selected
    if ($_GET['edit'] == 'Y') {
        $curPermsData = Admin::getGroupPermissionData()->fetchAll();
        $curPermsIds = [];
        foreach ($curPermsData as $perm) {
            if ($perm['group_id'] == $_GET['id']) {
                $curPermsIds[] = $perm['permission_id'];
            }
        }
    }
}

function getCurPermsIds($group_id)
{

}
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<?
// здесь будет вывод страницы с формой
$context = new CAdminContextMenu($aMenu);
$context->Show();

if($_REQUEST["status"] == "ok" && $_REQUEST["edit"] == "Y") {
    CAdminMessage::ShowMessage(array("MESSAGE"=>"Данные успешно обновлены.", "TYPE"=>"OK"));
}

?>

<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?=LANG ?>">
<input type="hidden" name="table" value="<?echo $_GET['table']?>">
<input type="hidden" name="action" value="<?echo $_GET['edit'] == "Y" ? 'update' : 'add'?>">

<?
$tabControl->Begin();
?>


<!-- Таб Редактировать -->
<?if ($_GET['edit'] == "Y"):?>
<?
$tabControl->BeginNextTab();
?>
  <tr>
    <td>ID</td>
    <td><input type="text" name="id" value="<?echo $_GET['id']?>" size="5" maxlength="4" readonly/></td>
  </tr>

  <tr>
    <td><span class="required">*</span>Код <?echo $edit_table_name?></td>
    <td><input type="text" name="code" value="<?echo $_GET['code']?>" size="40" maxlength="255" /></td>
  </tr>

  <tr>
    <td><span class="required">*</span>Название <?echo $edit_table_name?></td>
    <td><input type="text" name="name" value="<?echo $_GET['name']?>" size="100" maxlength="255" /></td>
  </tr>

  <?if ($_GET['table'] == 'groups'):?>
  <tr>
      <td><span class="required">*</span>Разрешения <?echo $edit_table_name?></td>
      <td>
          <select name="group_permissions[]" size="14" multiple style="width: 360px;">
              <?foreach ($permsData as $perm):?>
              <option value="<?echo $perm['permission_id']?>" <?echo in_array($perm['permission_id'], $curPermsIds) ? 'selected': '';?> > <?echo $perm['name']?></option>
              <?endforeach?>
          </select>
      </td>
  </tr>
  <?endif?>

  <tr>
    <td>Удалить</td>
    <td><input type="checkbox" name="delete" value="Y"/></td>
  </tr>

<? else: ?>

<!-- Таб Добавить -->
<?
$tabControl->BeginNextTab();
?>
<tr>
  <td><span class="required">*</span>Код <?echo $edit_table_name?></td>
  <td><input type="text" name="code" value="" placeholder="example_code_name" size="40" maxlength="255" /></td>
</tr>

<tr>
  <td><span class="required">*</span>Название <?echo $edit_table_name?></td>
  <td><input type="text" name="name" value="" placeholder="Название <?echo $edit_table_name?>" size="100" maxlength="255" /></td>
</tr>

    <?if ($_GET['table'] == 'groups'):?>
    <tr>
        <td><span class="required">*</span>Разрешения <?echo $edit_table_name?></td>
        <td>
            <select name="group_permissions[]" size="14" multiple style="width: 360px;">
                <?foreach ($permsData as $perm):?>
                <option value="<?echo $perm['permission_id']?>"><?echo $perm['name']?></option>
                <?endforeach?>
            </select>
        </td>
    </tr>
    <?endif?>
<? endif ?>
<?
$tabControl->Buttons(
  array(
    "disabled"=>($POST_RIGHT<"W"),
    "back_url"=>"idem_{$_GET['table']}_admin.php?lang=".LANG,
  )
);
?>

<?
$tabControl->End();
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
