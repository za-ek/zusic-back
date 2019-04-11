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
           a.title as album_title,
           a.year as album_year,
           b.title as artist_title
    FROM tracks t
        LEFT JOIN albums a ON a.id = t.album_id
        LEFT JOIN artists b on b.id = t.artist_id
    WHERE album_id = :id
");
$stmt->bindValue(':id', $this->getAction()->getVar('id'), SQLITE3_INTEGER);
$result = $stmt->execute();
$return = [];
$len = strlen($this->getController()->getConf('dataDir'));
while($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $return[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'duration' => $row['duration'],
        'album' => [
            'title' => $row['album_title'],
            'year' => $row['album_year'],
        ],
        'artist' => [
            'title' => $row['artist_title'],
        ],
    ];
}

return $return;