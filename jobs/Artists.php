<?php
try {
    /**
     * @var $mysqli mysqli
     */
    $mysqli = $this->getController()->db();
} catch (\Zaek\Framy\InvalidConfiguration $e) {
    echo 'Couldn\'t connect to database';
    return;
}

$result = $mysqli->query("
    INSERT INTO artists (title, track_count)
    SELECT a.artist, COUNT(*)
    FROM files a
    GROUP BY a.artist
    ON DUPLICATE KEY UPDATE track_count = VALUES(track_count)
");
$mysqli->commit();

do {
    $mysqli->query("
        UPDATE files f
        LEFT JOIN artists a ON a.title = f.artist
        SET f.artist_id = a.id
        WHERE f.artist_id = 0
");
    $mysqli->commit();

} while($mysqli->affected_rows > 0);


$result = $mysqli->query("
    SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
    INSERT INTO
        albums (
            title,
            artist_id,
            track_count
        )
    SELECT a.album, a.artist_id, COUNT(*)
    FROM files a
    GROUP BY a.artist, a.album
    ON DUPLICATE KEY UPDATE track_count = VALUES(track_count)
");
$mysqli->commit();

do {
    $mysqli->query("
        UPDATE files f
        LEFT JOIN albums a ON a.title = f.album AND a.artist_id = f.artist_id
        SET f.album_id = a.id
        WHERE f.album_id = 0
");
    $mysqli->commit();

} while($mysqli->affected_rows > 0);
