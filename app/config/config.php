<?php
declare(strict_types=1);

$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'tadbirkor';
$dbUser = getenv('DB_USER') ?: 'tadbirkor';
$dbPass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : 'tadbirkor_pass';

return array (
  'app_name' => getenv('APP_NAME') ?: 'Tadbirkor.uz',
  'base_url' => getenv('APP_URL') ?: '',
  'timezone' => 'Asia/Tashkent',
  'contest' => 
  array (
    'monthly_prize' => 'Ekspert sessiyasi + bosh sahifada alohida joy + mentor fikri',
  ),
  'db' => 
  array (
    'host' => $dbHost,
    'port' => $dbPort,
    'database' => $dbName,
    'username' => $dbUser,
    'password' => $dbPass,
    'charset' => 'utf8mb4',
  ),
);
