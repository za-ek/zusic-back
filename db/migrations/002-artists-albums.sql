CREATE TABLE IF NOT EXISTS artists (
   `id` INT NOT NULL AUTO_INCREMENT,
   `title` VARCHAR(128) NOT NULL,
   `album_count` SMALLINT UNSIGNED,
   `track_count` MEDIUMINT UNSIGNED,
   `is_compilation` INT(1) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`),
   UNIQUE(`title`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS albums (
   `id` INT NOT NULL AUTO_INCREMENT,
   `title` VARCHAR(128) NOT NULL,
   `artist_id` INT UNSIGNED,
   `track_count` MEDIUMINT UNSIGNED,
   `is_compilation` INT(1) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`),
   UNIQUE(`artist_id`, `title`)
) ENGINE = InnoDB;