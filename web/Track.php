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
$stmt = $db->prepare("SELECT path, title, duration, updated_at FROM tracks WHERE id = :id");
$stmt->bindValue(':id', $this->getAction()->getRequest()->get('id')['id'], SQLITE3_INTEGER);
$result = $stmt->execute();

if($result = $result->fetchArray(SQLITE3_NUM)) {
    $size = filesize($result[0]);
    header('HTTP/1.1 206 Partial Content');
    header('Content-Disposition: inline;filename="'.$result[1].'.mp3"');
    header("X-Content-Duration: {$result[2]}");
    header('Content-length: '.$size);
    header("Content-Range: bytes 0-".($size-1)."/{$size}");
    header('Content-Type: audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3');
    header('Cache-Control: private, must-revalidate');
    header('Last-Modified: '.gmdate(
        'D, d M Y H:i:s',
        (@filemtime($result[0]) > $result[3]) ? @filemtime($result[0]) : $result[3]
    ) . ' GMT' );
    if ($this->getAction()->getRequest()->getMethod() === 'GET') {
        readfile($result[0]);
    }
    die();
} else {
    throw new \Zaek\Framy\Action\NotFound;
}