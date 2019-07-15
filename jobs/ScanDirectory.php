<?php
include __DIR__ . "/../mp3.php";
/**
 * @var \Zaek\Framy\Application $this
 */

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

try {
    $db = $this->getController()->getConf('db');
    global $mysqli;
    $mysqli = new mysqli($db['host'], $db['login'], $db['password'], $db['dbname']);
    $mysqli->autocommit(false);

    if($mysqli->connect_error) {
        die($mysqli->connect_error);
    }
} catch (\Zaek\Framy\InvalidConfiguration $e) {
    echo 'Couldn\'t connect to database';
    return;
}

global $total;
$total = 0;
function addMp3File($file) {
    global $mysqli;
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
}

$scanDirectory = function ($target) use (&$scanDirectory){
    if(is_dir($target)){
        $files = glob( $target . '*', GLOB_MARK );
        foreach( $files as $file ) {
            $scanDirectory( $file );
        }
    } else if(strcasecmp(substr($target, -4), '.mp3') == 0) {
        addMp3File($target);
    }
    ob_end_flush();
    ob_start();
};

$scanDirectory($dataDir);
$mysqli->commit();

// include "Artists.php";
// include "DetectCompilations.php";
// include "Consistency.php";
