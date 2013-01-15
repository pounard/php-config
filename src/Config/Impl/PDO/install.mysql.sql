
CREATE TABLE `php_config_storage` (
    `id`    INT(11)      UNSIGNED NOT NULL AUTO_INCREMENT,
    `path`  VARCHAR(255) UNIQUE NOT NULL,
    `type`  INT(3)       NOT NULL DEFAULT -1,
    `value` BLOB         NOT NULL,
    PRIMARY KEY(`id`)
);

CREATE INDEX `php_config_storage_path_idx`
    ON `php_config_storage`(`path`);

CREATE TABLE `php_config_schema` (
    `id`        INT(11)      UNSIGNED NOT NULL AUTO_INCREMENT,
    `schema_id` VARCHAR(255) NOT NULL,
    `path`      VARCHAR(255) UNIQUE NOT NULL,
    `type`      INT(3)       NOT NULL DEFAULT -1,
    `listtype`  INT(3)       DEFAULT NULL,
    `shortdesc` BLOB         DEFAULT NULL,
    `longdesc`  BLOB         DEFAULT NULL,
    `locale`    VARCHAR(10)  DEFAULT NULL,
    `default`   BLOB         DEFAULT NULL,
    PRIMARY KEY(`id`)
);

CREATE INDEX `php_config_schema_path_idx`
    ON `php_config_schema`(`path`);
CREATE INDEX `php_config_schema_schema_id_idx`
    ON `php_config_schema`(`schema_id`);
