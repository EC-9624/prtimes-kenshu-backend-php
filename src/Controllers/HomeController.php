<?php

namespace App\Controllers;

require_once __DIR__ . '/../core/helper.php';

class HomeController
{
    public function index()
    {
        render('home/index', ['title' => 'Home Page']);
        var_dump($_SESSION);
    }
}
