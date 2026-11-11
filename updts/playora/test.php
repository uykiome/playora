<?php
require_once __DIR__ . '/config/database.php';
echo $pdo ? 'Playora and MySQL connection are working!' : 'Playora PHP works, but MySQL is not connected. Check README.md.';
