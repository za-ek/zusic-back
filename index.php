<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    header('Access-Control-Allow-Origin: http://localhost:8081');
    $controller = new \Zaek\Framy\Controller([
        'homeDir' => __DIR__,
        'routes' => [
            'GET:json /artists' => '/web/ArtistList.php',
            'GET:json /artists/<id:\d+>/albums' => '/web/AlbumList.php',
            'GET:json /artists/<id:\d+>' => '/web/ArtistItem.php',
            'GET:json /albums' => '/web/AlbumList.php',
            'GET:json /albums/<id:\d+>' => '/web/AlbumItem.php',
            'GET /tracks/<id:\d+>' => '/web/Track.php',

            'CLI /scan' => '/jobs/ScanDirectory.php',
            'CLI /consist' => '/jobs/Consistency.php',
            'CLI /install' => '/install.php',
        ],
        'dataDir' => __DIR__.'/music',
        'dbFile' => __DIR__.'/db/music.sqlite',
    ]);
    $controller->handle();
    $action = $controller->getRouter()->getRequestAction($controller->getRequest());
    $controller->getResponse()->flush();

} catch (\Exception $e) {
    echo $e->getMessage();
}