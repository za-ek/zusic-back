<?php
/**
 * @var $this \Zaek\Framy\Application
 * @var $mysqli mysqli
 */
$mysqli = $this->getController()->db();
$stmt = $mysqli->prepare("
    SELECT 
           t.id as id,
           t.title as title,
           t.duration as duration,
           t.album_id as album_id,
           t.artist_id as artist_id,
           a.title as album_title,
           -- a.year as album_year,
           b.title as artist_title
    FROM files t
        LEFT JOIN albums a ON a.id = t.album_id
        LEFT JOIN artists b on b.id = t.artist_id
    WHERE album_id = ?
");
if(!$stmt) {
    return $mysqli->error;
}
$albumId = (int)$this->getAction()->getRequest()->get('id')['id'];
$stmt->bind_param('i', $albumId);
$result = $stmt->execute();
if($result && $result = $stmt->get_result()) {
    $return = [];
    $len = strlen($this->getController()->getConf('dataDir'));
    $artistsIds = [];
    while ($row = $result->fetch_assoc()) {
        $return[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'album_id' => $row['album_id'],
            'artist_id' => $row['artist_id'],
            'duration' => $row['duration'],
        ];
        $artistsIds[] = $row['artist_id'];
    }
}

return [
    'tracks' => $return,
];