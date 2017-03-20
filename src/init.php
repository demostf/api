<?php
$autoloader = require __DIR__ . '/../vendor/autoload.php';

if (!getenv('DB_TYPE')) {
	Dotenv::load(__DIR__ . '/../');
}
