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
        'username' => $_ENV['DB_USERNAME'] ?? 'esa',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'database' => $_ENV['DB_DATABASE'] ?? '',
        'port' => $_ENV['DB_PORT'] ?? 0,
        'timezone' => $_ENV['TIMEZONE'] ?? 'Europe/Moscow',
    ],
];

// ...

return $settings;
