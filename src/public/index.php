<?php

echo "<h1>Hello from PHP!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

$host = getenv('DATABASE_HOST');
$dbname = getenv('DATABASE_NAME');
$user = getenv('DATABASE_USER');
$password = getenv('DATABASE_PASSWORD');

var_dump($host, $dbname, $user, $password);

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$dbname";
    var_dump($dsn);
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Successfully connected to PostgreSQL!</p>";

    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "<p>PostgreSQL Version: " . htmlspecialchars($version) . "</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
