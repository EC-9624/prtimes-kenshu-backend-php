<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;
    private static string $host;
    private static string $dbname;
    private static string $user;
    private static string $password;

    /**
     * Get the PDO instance.
     * This method allows other parts of your application to interact with the database.
     *
     * @return PDO
     */
    public static function getConnection(): PDO
    {
        // Only establish connection if it hasn't been established yet
        if (self::$pdo === null) {
            // Initialize static properties when getConnection is first called
            self::$host = getenv('DATABASE_HOST');
            self::$dbname = getenv('DATABASE_NAME');
            self::$user = getenv('DATABASE_USER');
            self::$password = getenv('DATABASE_PASSWORD');

            $dsn = "pgsql:host=" . self::$host . ";port=5432;dbname=" . self::$dbname;
            try {
                self::$pdo = new PDO($dsn, self::$user, self::$password);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw new PDOException("Database connection failed", 0, $e);
            }
        }
        return self::$pdo;
    }
}
