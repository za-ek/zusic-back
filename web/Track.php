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
    $bytesRange = [0];
    if (!empty($_SERVER['HTTP_RANGE'])) {
        $bytesRange = explode('-', substr($_SERVER['HTTP_RANGE'], strpos($_SERVER['HTTP_RANGE'], '=') + 1));
    }
    $size = filesize($result[0]);
    if(empty($bytesRange[1])) {
        $bytesRange[1] = $size - 1;
    }
    $maxPerRequest = 1024 * 128;
    if ($bytesRange[1] - $bytesRange[0] > $maxPerRequest) {
        $bytesRange[1] = $bytesRange[0] + $maxPerRequest;
    }
    $length = $bytesRange[1] - $bytesRange[0];

    if($length === 0) {
        die(header('HTTP/1.1 416 Requested Range Not Satisfiable'));
    }

    header('HTTP/1.1 206 Partial Content');
    header('Accept-Ranges: bytes');
    header('Content-Disposition: inline;filename="'.$result[1].'.mp3"');
    header("X-Content-Duration: {$result[2]}");
    header('Content-length: '.$length);
    header("Content-Range: bytes {$bytesRange[0]}-{$bytesRange[1]}/{$size}");
    header('Content-Type: audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3');
    header('Cache-Control: private, must-revalidate');
    header('Last-Modified: '.gmdate(
        'D, d M Y H:i:s',
        (@filemtime($result[0]) > $result[3]) ? @filemtime($result[0]) : $result[3]
    ) . ' GMT' );
    if ($this->getAction()->getRequest()->getMethod() === 'GET') {
        set_time_limit(0);
        // readfile($result[0]);
        $fp = fopen($result[0], 'rb');
        fseek($fp, $bytesRange[0], SEEK_SET);
        if (ob_get_level() == 0) ob_start();
        $printed = 0;
        while( !feof($fp) && $printed < $length){
            $out = min(128, $bytesRange[1] - $printed);
            if($out < 1) {
                error_log('$printed: ' . $printed);
                error_log('$length: ' . $length);
                error_log('$size: ' . $size);
                die();
            }
            echo fread($fp, $out);
            $printed += $out;
            flush();
            usleep(100);
        }
    }
    die();
} else {
    throw new \Zaek\Framy\Action\NotFound;
}