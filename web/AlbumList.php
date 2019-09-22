<?php
/**
 * @var $this \Zaek\Framy\Application
 * @var $mysqli mysqli
 */
$mysqli = $this->getController()->db();

$query = "
        SELECT 
               a.id as id,
               a.title as title,
               a.artist_id as artist_id,
               -- a.year as year,
               a.track_count as track_count
        FROM albums a
    ";
if ($this->getAction()->getRequest()->get('id')) {
    $query .= " WHERE artist_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $this->getAction()->getRequest()->get('id')['id']);
} else {
    $stmt = $mysqli->prepare($query);
}

$result = $stmt->execute();
$return = [];
if($result = $stmt->get_result()) {
    while ($row = $result->fetch_assoc()) {
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
}

return [
    'albums' => $return
];