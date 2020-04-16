<?
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid()) {
   return;
}

if ($errorException = $APPLICATION->GetException()) {
    echo CAdminMessage::ShowMessage($errorException->GetString());
} else {
    echo CAdminMessage::ShowNote(Loc::getMessage('PERM_UNSTEP_SUCCESS_UNINSTALL'));
}
?>

<!-- кнопка назад -->
<form action="<? echo $APPLICATION->GetCurPage(); ?>">
    <input type="hidden" name="lang" value="<? echo LANG; ?>" />
    <input type="submit" value="<? echo Loc::getMessage('PERM_UNSTEP_BACK_TO_LIST'); ?>">
</form>
