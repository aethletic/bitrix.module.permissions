<?php

$menu = array(
    "parent_menu" => "global_menu_content",
    "url" => "idem_permissions_admin.php",
    "sort" => 100,
    "text" => "Разрешения пользователей",
    "icon" => "iblock_menu_icon_types",
    "items_id" => "menu_search",
    "items" => array(
        array(
            "text" => "Редактировать разрешения",
            "url" => "idem_permissions_admin.php?lang=" . LANGUAGE_ID,
            "title" => "Разрешения",
        ),
        array(
            "text" => "Редактировать группы",
            "url" => "idem_groups_admin.php?lang=" . LANGUAGE_ID,
            "title" => "Группы",
        ),
    ),
);

return $menu;
