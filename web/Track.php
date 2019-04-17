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
$stmt = $db->prepare("SELECT path, title, duration FROM tracks WHERE id = :id");
$stmt->bindValue(':id', $this->getAction()->getVar('id'), SQLITE3_INTEGER);
$result = $stmt->execute();

if($result = $result->fetchArray(SQLITE3_NUM)) {
    $size = filesize($result[0]);
    header('HTTP/1.1 206 Partial Content');
    header('Content-Disposition: inline;filename="'.$result[1].'.mp3"');
    header("X-Content-Duration: {$result[2]}");
    header('Content-length: '.$size);
    header("Content-Range: bytes 0-".($size-1)."/{$size}");
    header('Content-Type: audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3');
    header("Last-Modified: ".gmdate('D, d M Y H:i:s', @filemtime($result[0])) . ' GMT' );

    readfile($result[0]);
} else {
    throw new \Zaek\Framy\Action\NotFound;
}