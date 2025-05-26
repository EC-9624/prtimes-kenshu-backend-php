<?php

namespace App;

use App\Controllers\AuthController;
use App\Core\Database;
use App\Core\Router;
use App\Controllers\HomeController;

class App
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
        $this->router->get('/login', [AuthController::class, 'index']);
        $this->router->get('/register', [AuthController::class, 'showRegister']);
        //users

        //posts



        $this->router->dispatch();
    }
}
