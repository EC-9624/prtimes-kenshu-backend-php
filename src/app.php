<?php

namespace App;

use App\Controllers\AuthController;
use App\Core\Database;
use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\PostConstroller;

class app
{
    protected Router $router;
    protected Database $db;

    public function __construct()
    {
        $this->router = new Router();
        $this->db = new Database();
    }

    public function run()
    {
        $this->db->getConnection();
        //top
        $this->router->get('/', [HomeController::class, 'index']);
        //categories
        $this->router->get('/{category_slug}', [HomeController::class, 'showCategory']);
        //auth
        $this->router->get('/login', [AuthController::class, 'showLoginForm']);
        $this->router->post('/login', [AuthController::class, 'login']);
        $this->router->get('/logout', [AuthController::class, 'logout']);
        $this->router->get('/register', [AuthController::class, 'showRegisterForm']);
        $this->router->post('/register', [AuthController::class, 'register']);
        //users

        //posts
        $this->router->get('/posts/{post_slug}', [PostConstroller::class, 'showPost']);
        $this->router->get('/create-post', [PostConstroller::class, 'showCreatePost']);
        $this->router->post('/create-post', [PostConstroller::class, 'createPost']);
        $this->router->get('/posts/{post_id}/edit', [PostConstroller::class, 'showEditpost']);
        $this->router->patch('/posts/{post_id}/edit', [PostConstroller::class, 'editPost']);
        $this->router->delete('/posts/{post_id}/delete', [PostConstroller::class, 'deletePost']);

        $this->router->get('/info', function () {
            phpinfo();
        });


        $this->router->dispatch();
    }
}
