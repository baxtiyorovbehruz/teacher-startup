<?php
declare(strict_types=1);

session_start();

$config = require __DIR__ . '/app/config/config.php';
date_default_timezone_set($config['timezone'] ?? 'Asia/Tashkent');

require_once __DIR__ . '/app/core/helpers.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Auth.php';
require_once __DIR__ . '/app/core/IdeaService.php';

$db = null;
$auth = null;
$ideas = null;
$dbError = null;

try {
    $db = new App\Core\Database($config['db']);
    $auth = new App\Core\Auth($db);
    $ideas = new App\Core\IdeaService($db);
} catch (Throwable $exception) {
    $dbError = $exception->getMessage();
}
