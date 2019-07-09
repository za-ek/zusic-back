CREATE TABLE IF NOT EXISTS`zusic`.`files` (
    `id` MEDIUMINT NOT NULL AUTO_INCREMENT,
    `file_path` VARCHAR(255) NOT NULL,
    `title` VARCHAR(64) NOT NULL,
    `artist` VARCHAR(64) NOT NULL,
    `album` VARCHAR(64) NOT NULL,
    `duration` SMALLINT UNSIGNED NOT NULL,
    `track_number` SMALLINT UNSIGNED NOT NULL ,
    PRIMARY KEY (`id`),
    UNIQUE (`file_path`)
) ENGINE = InnoDB;