<?php

declare(strict_types=1);

$projectDir = dirname(__DIR__);
$dbFile = $projectDir . '/database/data.db';
$schemaFile = $projectDir . '/database/schema.sql';

if (file_exists($dbFile)) {
    unlink($dbFile);
}

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec(file_get_contents($schemaFile));

echo "Database initialisée : {$dbFile}\n";
