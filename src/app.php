<?php

namespace App;

use App\Controllers\AuthController;
use App\Core\Database;
use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\PostController;
use App\Controllers\UserController;

class app
{
    protected Router $router;
    protected Database $db;

    public function __construct()
    {
        $this->router = new Router();
        $this->db = new Database();
    }

    public function run(): void
    {
        $this->db->getConnection();
        //top
        $this->router->get('/', [HomeController::class, 'index']);
        //categories
        $this->router->get('/categories/{category_slug}', [HomeController::class, 'showCategory']);
        //auth
        $this->router->get('/login', [AuthController::class, 'showLoginForm']);
        $this->router->post('/login', [AuthController::class, 'login']);
        $this->router->get('/logout', [AuthController::class, 'logout']);
        $this->router->get('/register', [AuthController::class, 'showRegisterForm']);
        $this->router->post('/register', [AuthController::class, 'register']);
        //users
        $this->router->get('/users/{user_id}', [UserController::class, 'showUserPosts']);

        //posts
        $this->router->get('/posts/{post_slug}', [PostController::class, 'showPost']);
        $this->router->get('/create-post', [PostController::class, 'showCreatePost']);
        $this->router->post('/create-post', [PostController::class, 'createPost']);
        $this->router->get('/posts/{post_id}/edit', [PostController::class, 'showEditpost']);
        $this->router->patch('/posts/{post_id}/edit', [PostController::class, 'editPost']);
        $this->router->delete('/posts/{post_id}/delete', [PostController::class, 'deletePost']);

        $this->router->get('/info', function () {
            phpinfo();
        });


        $this->router->dispatch();
    }
}
