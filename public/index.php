<?php

// Start the session for every request
// This MUST be the very first thing in your script, before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';

use App\app;

$app = new app();
$app->run();
