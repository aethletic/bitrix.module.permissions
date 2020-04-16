/*------------------------------
 * Создание таблиц
 *------------------------------
 *
 */

CREATE TABLE IF NOT EXISTS group_permissions
(
    group_id INT NOT NULL,
    permission_id INT NOT NULL
) engine = innodb charset = utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS permissions
(
    permission_id INT NOT NULL auto_increment,
    permission_code TEXT NOT NULL,
    name TEXT NOT NULL,
    PRIMARY KEY (permission_id)
) engine = innodb charset = utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS groups
(
    group_id INT NOT NULL auto_increment,
    group_code TEXT NOT NULL,
    name TEXT NOT NULL,
    PRIMARY KEY (group_id)
) engine = innodb charset = utf8 COLLATE utf8_general_ci;
