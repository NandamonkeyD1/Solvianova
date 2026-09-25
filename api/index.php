<?php

// 1. Prepare SQLite Database in writable /tmp directory on Vercel
$tmpDb = '/tmp/database.sqlite';
if (!file_exists($tmpDb)) {
    $sourceDb = __DIR__ . '/../database/database.sqlite';
    if (file_exists($sourceDb)) {
        copy($sourceDb, $tmpDb);
    } else {
        touch($tmpDb);
    }
}

putenv("DB_DATABASE={$tmpDb}");
$_ENV['DB_DATABASE'] = $tmpDb;
$_SERVER['DB_DATABASE'] = $tmpDb;

// 2. Set compiled view path to writable /tmp directory
putenv("VIEW_COMPILED_PATH=/tmp");
$_ENV['VIEW_COMPILED_PATH'] = '/tmp';
$_SERVER['VIEW_COMPILED_PATH'] = '/tmp';

// 3. Set config/cache paths to /tmp directory
putenv("APP_CONFIG_CACHE=/tmp/config.php");
putenv("APP_SERVICES_CACHE=/tmp/services.php");
putenv("APP_PACKAGES_CACHE=/tmp/packages.php");
putenv("APP_ROUTES_CACHE=/tmp/routes.php");
putenv("APP_EVENTS_CACHE=/tmp/events.php");

// 4. Require Laravel public index
require __DIR__ . '/../public/index.php';
