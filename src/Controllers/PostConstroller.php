<?php

namespace App\Controllers;


require_once __DIR__ . '/../core/helper.php';

class PostConstroller
{

    public function showPost(string $post_slug)
    {
        render('post/show', ['title' => 'Post Detail Page']);
        var_dump($post_slug);
    }

    public function showCreatePost()
    {
        //check session if not redirect to login

        render('post/show', ['title' => 'Create Post Page']);
    }

    public function createPost()
    {
        //will be call from create showCreatePost page 
        //get user info from session
        // think about File upload
        // maybe create File upload handler?
        echo 'storePost called';
    }

    public function showEditpost() {}

    public function editpost() {}
}
