<?php

namespace App\Controllers;

require_once __DIR__ . '/../core/helper.php';

class HomeController
{
    public function index()
    {
        render('index', ['title' => 'Home Page']);
    }
}
