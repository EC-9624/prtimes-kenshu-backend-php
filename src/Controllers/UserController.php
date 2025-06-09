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

    /**
     * show user created post list
     * @param $usernameParam
     * @return void
     */
    public function showUserPosts($usernameParam): void
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

        if (isset($_SESSION['user_id'])) {
            print_r($_SESSION['user_id']);
        } else {
            print_r('user_id not set in session.');
        }
        echo '<br>';
        print_r('post userId: ' . $user->getUserId()->toString());
        echo '</pre>';


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
