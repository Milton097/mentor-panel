<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Now use the env vars
define('SHEET1_ID', $_ENV['GOOGLE_SHEET_ID']);
define('SHEET1_TAB', $_ENV['SHEET1_TAB']);
define('SHEET2_TAB', $_ENV['SHEET2_TAB']);
define('CREDENTIALS_PATH', $_ENV['CREDENTIALS_PATH']);
