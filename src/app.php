<?php

namespace App;

use App\Controllers\AuthController;
use App\Core\Database;
use App\Core\Router;
use App\Controllers\HomeController;

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

        $this->router->get('/', [HomeController::class, 'index']);
        //auth
        $this->router->get('/login', [AuthController::class, 'showLoginForm']);
        $this->router->post('/login', [AuthController::class, 'login']);
        $this->router->get('/logout', [AuthController::class, 'logout']);
        $this->router->get('/register', [AuthController::class, 'showRegisterForm']);
        $this->router->post('/register', [AuthController::class, 'register']);
        //users

        //posts

        $this->router->get('/info', function () {
            phpinfo();
        });


        $this->router->dispatch();
    }
}
