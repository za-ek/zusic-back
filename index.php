<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    $controller = new \Zaek\Framy\Controller([
        'homeDir' => __DIR__,
        'routes' => [
            'CLI|GET /artists' => '/web/ArtistList.php',
            'CLI|GET /artists/<id:\d+>' => '/web/ArtistItem.php',
            'CLI|GET /albums' => '/web/AlbumList.php',
            'CLI|GET /albums/<id:\d+>' => '/web/AlbumItem.php',

            'CLI /scan' => '/jobs/ScanDirectory.php',
            'CLI /install' => '/install.php',
        ],
        'dataDir' => __DIR__.'/music',
        'dbFile' => __DIR__.'/db/music.sqlite',
    ]);
    $controller->handle();
    $controller->getResponse()->flush();

} catch (\Exception $e) {
    echo $e->getMessage();
}