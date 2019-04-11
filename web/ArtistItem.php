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
$stmt = $db->prepare("SELECT * FROM artists WHERE id=:id");
$stmt->bindValue(':id', $this->getAction()->getVar('id'), SQLITE3_INTEGER);
$result = $stmt->execute();
$return = [];
while($row = $result->fetchArray()) {
    $return[] = $row;
}

return $return;