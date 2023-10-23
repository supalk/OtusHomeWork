<?php
// Should be set to 0 in production
error_reporting(E_ALL);

// Should be set to '0' in production
ini_set('display_errors', '1');

// Settings
$settings = [
    "db" => [
        'driver' => 'pgsql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'username' => $_ENV['DB_USERNAME'] ?? '',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'database' => $_ENV['DB_DATABASE'] ?? '',
        'port' => $_ENV['DB_PORT'] ?? 0,
        'timezone' => $_ENV['TIMEZONE'] ?? 'Europe/Moscow',
    ],
    "db-read" => [
        'driver' => 'pgsql',
        'host' => $_ENV['DB_R_HOST'] ?? 'localhost',
        'username' => $_ENV['DB_R_USERNAME'] ?? '',
        'password' => $_ENV['DB_R_PASSWORD'] ?? '',
        'database' => $_ENV['DB_R_DATABASE'] ?? '',
        'port' => $_ENV['DB_R_PORT'] ?? 0,
        'timezone' => $_ENV['TIMEZONE'] ?? 'Europe/Moscow',
    ],
];

// ...

return $settings;
