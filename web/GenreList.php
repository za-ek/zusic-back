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
$result = $db->query("SELECT * FROM artists ORDER BY title");
$return = [];
while($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $return[] = $row + [
        'genre' => [
            'title' => ''
        ]
    ];
}

return [
    'artists' => $return
];