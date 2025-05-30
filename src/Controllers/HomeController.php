<?php

namespace App\Controllers;

require_once __DIR__ . '/../core/helper.php';

use App\Repositories\PostRepository;
use App\Core\Database;

class HomeController
{
    public function index()
    {
        $database = new Database();
        $postRepo = new PostRepository($database);
        $posts = $postRepo->getAllPosts();


        render('home/index', ['title' => 'Home Page', 'data' => $posts]);
    }

    public function showCategory($category)
    {
        var_dump($category);
        $database = new Database();
        $postRepo = new PostRepository($database);
        $posts = $postRepo->getAllPosts($category);


        render('home/index', ['title' => 'Home Page', 'data' => $posts]);
    }
}
