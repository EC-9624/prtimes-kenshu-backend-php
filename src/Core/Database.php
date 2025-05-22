<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    protected $pdo;
    protected $host;
    protected $dbname;
    protected $user;
    protected $password;





    public function __construct()
    {
        $this->host = getenv('DATABASE_HOST');
        $this->dbname = getenv('DATABASE_NAME');
        $this->user = getenv('DATABASE_USER');
        $this->password = getenv('DATABASE_PASSWORD');

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

    /**
     * Get the PDO instance.
     * This method allows other parts of your application to interact with the database.
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
