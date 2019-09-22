<?php
include __DIR__ . "/../mp3.php";
/**
 * @var \Zaek\Framy\Application $this
 */
ob_implicit_flush(1);
ob_get_clean();

$log = new class {
    public function log($line, $level = 0) {
        echo $line . PHP_EOL;
    }
};

$dataDir = $this->getAction()->getRequest()->getArgument('dataDir');
if(!$dataDir) {
    try {
        $dataDir = $this->getController()->getConf('dataDir');
    } catch (\Zaek\Framy\InvalidConfiguration $e) {
        echo 'You need put `dataDir` configuration option in /index.php';
        return;
    }
}
if(!file_exists($dataDir)) {
    echo "Data dir is empty (directory `{$dataDir}` not found)";
    return;
}

$log->log("data dir is ok");

try {
    $mysqli = $this->getController()->db();
} catch (\Zaek\Framy\InvalidConfiguration $e) {
    echo 'Couldn\'t connect to database';
    return;
}

$log->log("connected to db " . get_class($mysqli));

global $total;
$total = 0;
$addMp3File = function($file) use($mysqli) {
    echo 'add mp3 ' . $file . PHP_EOL;
    static $total;
    $tagger = new \duncan3dc\MetaAudio\Tagger;
    $tagger->addDefaultModules();
    $mp3 = $tagger->open($file);
    $duration = new \MP3File($file);

    $stmt = $mysqli->prepare(
        "INSERT INTO files(file_path, title, artist, album, duration, track_number)
                          VALUES(?, ?, ?, ?, ?, ?)"
    );
    if($stmt) {

        $artist = $mp3->getArtist();
        $album = $mp3->getAlbum();
        $duration = $duration->getDuration();
        $number = $mp3->getTrackNumber();
        $title = $mp3->getTitle();

        $total++;
        $stmt->bind_param(
            'ssssii',
            $file,
            $title,
            $artist,
            $album,
            $duration,
            $number
        );

        $stmt->execute();

        if($total >= 500) {
            $mysqli->commit();
            $total = 0;
        }
    } else {
        error_log($mysqli->error);
    }
};

$scanDirectory = function ($target) use (&$scanDirectory, $log, $addMp3File) {
    if(is_dir($target)){
        $log->log("dir: {$target}");
        $files = glob( $target . '*', GLOB_MARK );
        foreach( $files as $file ) {
            $scanDirectory( $file );
        }
    } else if(strcasecmp(substr($target, -4), '.mp3') == 0) {
        $addMp3File($target);
        $log->log("file: {$target}");
    }
};

$log->log("go scan {$dataDir}");

$scanDirectory($dataDir);
$mysqli->commit();

// include "Artists.php";
// include "DetectCompilations.php";
// include "Consistency.php";
