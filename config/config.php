<?php
// Edit these values for your server. They can also be set with environment variables.
define('ROOT', dirname(__DIR__));
define('APP_NAME', 'Vaccination Management System');

define('DB_DSN', getenv('VMS_DSN') ?: 'mysql:host=localhost;dbname=vaccination_db;charset=utf8mb4');
define('DB_USER', getenv('VMS_DB_USER') !== false ? getenv('VMS_DB_USER') : 'root');
define('DB_PASS', getenv('VMS_DB_PASS') !== false ? getenv('VMS_DB_PASS') : '');

// URL path of this folder, for example /vms when it lives in htdocs/vms. Detected automatically.
$doc = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
$base = ($doc && strpos(ROOT, $doc) === 0) ? str_replace('\\', '/', substr(ROOT, strlen($doc))) : '';
define('BASE_URL', getenv('VMS_BASE') !== false ? getenv('VMS_BASE') : rtrim($base, '/'));

date_default_timezone_set('Asia/Karachi');

// Set to true only while developing.
define('DEBUG', false);
