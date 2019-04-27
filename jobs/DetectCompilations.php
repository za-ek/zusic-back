<?php
try {
    $dbFile = $this->getController()->getConf('dbFile');
} catch (\Zaek\Framy\InvalidConfiguration $e) {
    echo 'You need put `dbFile` configuration option in /index.php';
    return;
}

global $db;
$db = new SQLite3($dbFile);

$foundTitles = [];

/**
 * Just because 5
 */
$list = $db->query("SELECT *, COUNT(*) FROM albums GROUP BY title HAVING COUNT(*) > 5");
while($item = $list->fetchArray(SQLITE3_ASSOC)) {
    $foundTitles[] = $item['title'];
}

$stmt = $db->prepare("SELECT * FROM albums WHERE title = :title");

foreach($foundTitles as $title) {
    $stmt->bindValue(':title', $title);
    if($list = $stmt->execute()) {
        $albumIds = [];
        while ($item = $list->fetchArray(SQLITE3_ASSOC)) {
            $albumIds[] = $item['id'];
        }

        $result = $db->query("SELECT id, path FROM tracks WHERE album_id IN (".implode(',', $albumIds).")");
        $directories = [];
        while($track = $result->fetchArray(SQLITE3_ASSOC)) {
            $directories[dirname($track['path'])][] = $track['id'];
        }

        foreach($directories as $dirPath => $trackIds) {
            /**
             * Just because 3
             */
            if (count($trackIds) > 3) {
                $trackInfo = $db->query("SELECT * FROM tracks WHERE id = {$trackIds[0]}");
                $trackInfo = $trackInfo->fetchArray(SQLITE3_ASSOC);

                $db->query("
                    UPDATE tracks
                    SET title = (
                        SELECT (case title when '' then 'unknown artist' else title end)
                        FROM artists
                        WHERE artist_id = artists.id
                    ) || ' - ' || title
                    WHERE id IN (".implode(',', $trackIds).")
                ");
                $db->query("
                    DELETE FROM artists WHERE id != {$trackInfo['artist_id']} AND id IN (
                        SELECT artist_id FROM tracks t WHERE t.id IN (".implode(',', $trackIds).")
                    )
                ");
                $db->query("
                    DELETE FROM albums WHERE id != {$trackInfo['album_id']} AND id IN (
                        SELECT album_id FROM tracks t WHERE t.id IN (".implode(',', $trackIds).")
                    )
                ");
                $db->query("UPDATE tracks SET artist_id = {$trackInfo['artist_id']} WHERE id IN (".implode(',', $trackIds).")");
                $db->query("UPDATE tracks SET album_id = {$trackInfo['album_id']} WHERE id IN (".implode(',', $trackIds).")");

                $stmtUpdate = $db->prepare("UPDATE artists SET title = :title WHERE id = {$trackInfo['artist_id']}");
                $stmtUpdate->bindValue(':title', $title);
                $stmtUpdate->execute();

                $db->query("UPDATE artists SET is_compilation = 1 WHERE id = {$trackInfo['artist_id']}");
            }
        }
    }
}

include "Consistency.php";