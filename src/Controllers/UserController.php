<?php

namespace App\Controllers;

require_once __DIR__ . '/../core/helper.php';

use App\Core\Database;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use PDOException;
use PDO;

class UserController
{
    private PostRepository $postRepo;
    private UserRepository $userRepo;

    private PDO $pdo;

    public function __construct()
    {
        $database = new Database();
        $this->pdo = $database->getConnection();
        $this->postRepo = new PostRepository($this->pdo);
        $this->userRepo = new UserRepository($this->pdo);
    }

    public function showUserPosts($usernameParam)
    {
        $errors = [];
        $user = $this->userRepo->findByUsername($usernameParam);

        if (!$user) {
            $errors[] = 'User not Found';
            render('user/posts', [
                'title' => 'User Posts Page',
                'user' => null,
                'posts' => null,
                'errors' => $errors
            ]);

            return;
        }

        echo '<pre>';
        print_r('$_SESSION userId: ' . $_SESSION['user_id']);
        echo '<br>';
        print_r('post userId: ' . $user->getUserId()->toString());
        echo '</pre>';
        // die;

        $userId = $user->getUserId()->toString();

        $posts = $this->postRepo->fetchPostsByUserId($userId);


        render('user/posts', [
            'title' => 'User Posts Page',
            'user' => $user,
            'posts' => $posts,
            'errors' => $errors
        ]);
    }
}
