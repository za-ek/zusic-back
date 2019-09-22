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
/**
 * @var $mysqli mysqli
 */
$mysqli = $this->getController()->db();
$result = $mysqli->query("SELECT * FROM artists ORDER BY title ASC");
if($result) {
    $return = [];
    while ($row = $result->fetch_assoc()) {
        $return[] = $row + [
                'genre' => [
                    'title' => ''
                ]
            ];
    }
}

return [
    'artists' => $return
];