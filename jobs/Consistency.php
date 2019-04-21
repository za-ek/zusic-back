<?php
try {
    $dbFile = $this->getController()->getConf('dbFile');
} catch (\Zaek\Framy\InvalidConfiguration $e) {
    echo 'You need put `dbFile` configuration option in /index.php';
    return;
}

global $db;
$db = new SQLite3($dbFile);

$db->query(
    "
    UPDATE albums
    SET track_count = (
        SELECT COUNT(*)
        FROM tracks
        WHERE album_id = albums.id
    )
"
);

$db->query(
    "
    UPDATE artists
    SET track_count = (
        SELECT COUNT(*)
        FROM tracks
        WHERE artist_id = artists.id
    )
"
);

$db->query(
    "
    UPDATE artists
    SET album_count = (
        SELECT COUNT(*)
        FROM albums
        WHERE artist_id = artists.id
    )
"
);

$db->query("
    DELETE FROM albums WHERE track_count = 0
");
$db->query("
    DELETE FROM artists WHERE track_count = 0
");