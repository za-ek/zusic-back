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
if ($this->getAction()->getVar('id')) {
    $stmt = $db->prepare("SELECT * FROM albums WHERE artist_id=:id");
    $stmt->bindValue(':id', $this->getAction()->getVar('id'), SQLITE3_INTEGER);
} else {
    $stmt = $db->prepare("
        SELECT 
               a.id as id,
               a.title as title,
               a.artist_id as artist_id,
               a.year as year,
               a.track_count as track_count,
               b.title as artist_title
        FROM albums a
        LEFT JOIN artists b ON b.id = a.artist_id
    ");
}
$result = $stmt->execute();
$return = [];
while($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $return[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'year' => $row['year'],
        'track_count' => $row['track_count'],
        'artist' => [
            'id' => $row['artist_id'],
            'title' => $row['artist_title']
        ]
    ];
}

return [
    'albums' => $return
];