<?php

/** Install only into a newly created, empty database. Never drops existing tables. */
require dirname(__DIR__) . '/app/bootstrap.php';
if (PHP_SAPI !== 'cli') {
    exit('CLI only');
}
$db = \App\Core\Database::connection();
if ($db->query('SHOW TABLES')->fetch()) {
    exit("Database tidak kosong. Gunakan database baru untuk instalasi.\n");
}
$db->exec(file_get_contents(BASE_PATH.'/database/schema.sql'));
$db->exec(file_get_contents(BASE_PATH.'/database/seed.sql'));
echo "Schema dan data demo berhasil diimport.\n";
