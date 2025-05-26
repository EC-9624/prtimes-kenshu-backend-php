<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    protected ?PDO $pdo = null;
    protected string $host;
    protected string $dbname;
    protected string $user;
    protected string $password;

    public function __construct()
    {
        $this->host = getenv('DATABASE_HOST');
        $this->dbname = getenv('DATABASE_NAME');
        $this->user = getenv('DATABASE_USER');
        $this->password = getenv('DATABASE_PASSWORD');
    }

    /**
     * Get the PDO instance.
     * This method allows other parts of your application to interact with the database.
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        // Only establish connection if it hasn't been established yet
        if ($this->pdo === null) {
            $dsn = "pgsql:host=$this->host;port=5432;dbname=$this->dbname";
            try {
                $this->pdo = new PDO($dsn, $this->user, $this->password);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                die("Database connection failed. Please try again later.");
            }
        }
        return $this->pdo;
    }
}
