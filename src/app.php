<?php

namespace App;

use App\Core\Database;
use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\PostController;

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
        $this->router->post('/', [HomeController::class, 'post']);
        $this->router->put('/', [HomeController::class, 'put']);
        $this->router->patch('/', [HomeController::class, 'patch']);
        $this->router->delete('/', [HomeController::class, 'delete']);

        $this->router->dispatch();
    }
}
