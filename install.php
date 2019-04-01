<?php
/**
 * DB choice:
 * SQLite strives to provide local data storage for individual applications and devices. SQLite emphasizes economy, efficiency, reliability, independence, and simplicity.
 * ... https://www.sqlite.org/whentouse.html
 * Since PHP 5, the SQLite extension is built-in PHP
 * ... https://www.php.net/manual/de/migration5.databases.php
 */

$db = new SQLite3($this->getController()->getConf('dbFile'));

$db->query("CREATE TABLE IF NOT EXISTS artists (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    title VARCHAR(128) NOT NULL,
    album_count INTEGER,
    track_count INTEGER
)");

$db->query("CREATE TABLE IF NOT EXISTS albums (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    title VARCHAR(128) NOT NULL,
    artist_id INTEGER,
    year INTEGER,
    track_count INTEGER
)");

$db->query("CREATE TABLE IF NOT EXISTS tracks (
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    title VARCHAR(128) NOT NULL,
    path VARCHAR(255) NOT NULL UNIQUE,
    artist_id INTEGER,
    album_id INTEGER,
    duration INTEGER
);");
