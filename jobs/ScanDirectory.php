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

try {
    $dbFile = $this->getController()->getConf('dbFile');
} catch (\Zaek\Framy\InvalidConfiguration $e) {
    echo 'You need put `dbFile` configuration option in /index.php';
    return;
}

if(!file_exists($dataDir)) {
    echo "Data dir is empty (directory `{$dataDir}` not found)";
    return;
}
global $db;
$db = new SQLite3($dbFile);
$db->busyTimeout(5000);


function addMp3File($file) {
    global $db;
    $tagger = new \duncan3dc\MetaAudio\Tagger;
    $tagger->addDefaultModules();
    $mp3 = $tagger->open($file);
    $duration = new \MP3File($file);

    $artist = findArtist($db, $mp3->getArtist());
    $album = findAlbum($db, $artist, $mp3->getAlbum(), $mp3->getYear());

    $stmt = $db->prepare("SELECT id FROM tracks WHERE artist_id = :artist AND album_id = :album AND title = :title LIMIT 1");
    $stmt->bindValue(':artist', $artist);
    $stmt->bindValue(':album', $album);
    $stmt->bindValue(':title', $mp3->getTitle());
    $result = $stmt->execute();
    if (!($row = $result->fetchArray())) {
        $stmt = $db->prepare(
            "INSERT INTO tracks(title, path, artist_id, album_id, duration, updated_at)
                          VALUES(:title, :path, :artist, :album, :duration, :updated)"
        );
        $stmt->bindValue(":title", $mp3->getTitle());
        $stmt->bindValue(":path", $file);
        $stmt->bindValue(":artist", $artist);
        $stmt->bindValue(":album", $album);
        $stmt->bindValue(":duration", $duration->getDuration());
        $stmt->bindValue(":updated", time());
        $stmt->execute();
    }

}

function findArtist(SQLite3 $db, $artist) {
    if($artist) {
        $stmt = $db->prepare('SELECT id FROM artists WHERE title = :title');
        $stmt->bindValue(':title', $artist);
        $result = $stmt->execute();

        if ($result = $result->fetchArray()) {
            $artist = $result[0];
        } else {
            $stmt = $db->prepare('INSERT INTO artists (title, album_count, track_count) VALUES (:title, 1, 1)');
            $stmt->bindValue(':title', $artist);
            $stmt->execute();

            $artist = $db->lastInsertRowID();
        }
    }

    if(!$artist) $artist = null;

    return $artist;
}

function findAlbum(SQLite3 $db, $artist, $album, $year) {
    if($album) {
        $stmt = $db->prepare('SELECT id FROM albums WHERE title = :title AND artist_id = :artist_id');
        $stmt->bindValue(':artist_id', $artist);
        $stmt->bindValue(':title', $album);
        $result = $stmt->execute();

        if ($result = $result->fetchArray()) {
            $album = $result[0];
        } else {
            $stmt = $db->prepare('INSERT INTO albums (title, artist_id, track_count, year) VALUES (:title, :artist_id, 1, :year)');
            $stmt->bindValue(':artist_id', $artist);
            $stmt->bindValue(':title', $album);
            $stmt->bindValue(':year', $year);
            $stmt->execute();

            $album = $db->lastInsertRowID();

            $stmt = $db->prepare("UPDATE artists SET album_count = album_count + 1 WHERE id = :artist_id");
            $stmt->bindValue(':artist_id', $artist);
            $stmt->execute();
        }
    }

    if(!$album) $album = null;

    return $album;
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

include "DetectCompilations.php";
include "Consistency.php";
