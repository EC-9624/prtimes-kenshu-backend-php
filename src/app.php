<?php

namespace App;

use App\Core\Database;
use App\Core\Router;

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
        $uri = $_SERVER['REQUEST_URI'];

        $uri = strtok($uri, '?');

        $this->router->dispatch($uri);
        $this->db->getConnection();
    }
}
