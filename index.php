<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::create(__DIR__.'/env');
    $dotenv->load();

    $conf = Dotenv\Dotenv::create(__DIR__);
    $conf->load();

    header('Access-Control-Allow-Origin: http://localhost:8081');
    $controller = new \Zaek\Framy\Controller([
        'homeDir' => __DIR__,
        'routes' => [
            'GET:json /tracks/random' => '/web/RandomTracks.php',
            'GET:json /genres' => '/web/GenreList.php',
            'GET:json /artists' => '/web/ArtistList.php',
            'GET:json /artists/<id:\d+>/albums' => '/web/AlbumList.php',
            'GET:json /artists/<id:\d+>' => '/web/ArtistItem.php',
            'GET:json /albums' => '/web/AlbumList.php',
            'GET:json /albums/<id:\d+>' => '/web/AlbumItem.php',
            'GET /tracks/<id:\d+>' => '/web/Track.php',
            'OPTIONS /tracks/<id:\d+>' => '/web/Track.php',

            'CLI /scan' => '/jobs/ScanDirectory.php',
            'CLI /consist' => '/jobs/Consistency.php',
            'CLI /compilations' => '/jobs/DetectCompilations.php',
            'CLI /install' => '/install.php',
        ],
        'dataDir' => getenv('datadir'),
        'dbFile' => getenv('dbfile'),
        'db' => [
            'login' => 'root',
            'password' => getenv('MYSQL_PASSWORD'),
            'host' => '172.26.5.3',
            'dbname' => 'zusic'
        ],
    ]);
    $controller->handle();
    $action = $controller->getRouter()->getRequestAction($controller->getRequest());
    $controller->getResponse()->flush();

} catch (\Exception $e) {
    echo $e->getMessage();
}