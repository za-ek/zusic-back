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
    $mime_type = "audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3";
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: inline;filename="'.$result[1].'.mp3"');
    header('Content-length: '.filesize($result[0]));
    header('X-Pad: avoid browser bug');
    header('Cache-Control: no-cache');
    header("Content-Transfer-Encoding: chunked");
    header("X-Content-Duration: {$result[2]}");

    readfile($result[0]);
} else {
    throw new \Zaek\Framy\Action\NotFound;
}