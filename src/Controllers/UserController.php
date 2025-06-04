<?php

namespace App\Controllers;

require_once __DIR__ . '/../core/helper.php';

use App\Core\Database;
use App\Repositories\UserRepository;
use PDOException;
use PDO;

class UserController
{
    private UserRepository $userRepo;
    private PDO $pdo;

    public function __construct()
    {
        $database = new Database();
        $this->pdo = $database->getConnection();
        $this->userRepo = new UserRepository($this->pdo);
    }

    public function showUserPosts()
    {
        render('user/posts', ['title' => 'User Posts Page']);
    }
}
