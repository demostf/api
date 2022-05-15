<?php

declare(strict_types=1);

$_SERVER['SCRIPT_NAME'] = '';

if ('/upload' === $_SERVER['REQUEST_URI']) {
    require __DIR__ . '/../src/public/upload.php';
} elseif ('/reset' === $_SERVER['REQUEST_URI']) {
    // allow the api tests to reset the database
    /** @var \Demostf\API\Container $container */
    $container = require __DIR__ . '/../src/init.php';
    $connection = $container->getConnection();

    clearDatabase($connection);
} elseif ('/testuser' === $_SERVER['REQUEST_URI']) {
    // allow the api tests to create a test user
    /** @var \Demostf\API\Container $container */
    $container = require __DIR__ . '/../src/init.php';
    $connection = $container->getConnection();

    $query = $connection->createQueryBuilder();
    $query->insert('users')
        ->values([
            'steamid' => $query->createNamedParameter('steamid1'),
            'name' => $query->createNamedParameter('nickname1'),
            'avatar' => $query->createNamedParameter('avatar1'),
            'token' => $query->createNamedParameter('key1'),
        ])->add('orderBy', 'ON CONFLICT DO NOTHING')// hack to append arbitrary string to sql
        ->execute();
} else {
    require __DIR__ . '/../src/public/index.php';
}

function clearDatabase(Doctrine\DBAL\Connection $connection) {
    $tables = $connection->getSchemaManager()->listTables();
    foreach ($tables as $table) {
        truncateTable($connection, $table->getName());
    }
}

function truncateTable(Doctrine\DBAL\Connection $connection, string $tableName) {
    $sql = sprintf('TRUNCATE TABLE %s;', $tableName);
    $connection->query($sql);
}
