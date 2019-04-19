<?php
/**
 * @var $this \Zaek\Framy\Application
 */
try {
    $dbFile = $this->getController()->getConf('dbFile');
} catch (\Zaek\Framy\InvalidConfiguration $e) {
    echo 'You need put `dbFile` configuration option in /index.php';
    return;
}
$db = new SQLite3($dbFile);
$stmt = $db->prepare("
    SELECT 
           t.id as id,
           t.title as title,
           t.duration as duration,
           t.album_id as album_id,
           t.artist_id as artist_id,
           a.title as album_title,
           a.year as album_year,
           b.title as artist_title
    FROM tracks t
        LEFT JOIN albums a ON a.id = t.album_id
        LEFT JOIN artists b on b.id = t.artist_id
    WHERE album_id = :id
");
$albumId = (int)$this->getAction()->getVar('id');
$stmt->bindValue(':id', $albumId, SQLITE3_INTEGER);
$result = $stmt->execute();
$return = [];
$len = strlen($this->getController()->getConf('dataDir'));
$artistsIds = [];
while($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $return[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'album_id' => $row['album_id'],
        'artist_id' => $row['artist_id'],
        'duration' => $row['duration'],
    ];
    $artistsIds[] = $row['artist_id'];
}

$artistList = [];
$albumList = [];

if ($return) {
    $result = $db->query("SELECT id, title FROM artists WHERE id IN (" . implode(',', $artistsIds) . ")");
    while($artist = $result->fetchArray(SQLITE3_ASSOC)) {
        $artistList[] = $artist;
    }

    $result = $db->query("SELECT id, title FROM albums WHERE id IN (" . implode(',', [$albumId]) . ")");
    if($result && $album = $result->fetchArray(SQLITE3_ASSOC)) {
        $albumList[] = $album;
    }
}

return [
    'tracks' => $return,
    'artists' => $artistList,
    'albums' => $albumList,
];