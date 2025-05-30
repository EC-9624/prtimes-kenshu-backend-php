<?php

namespace App\Controllers;


require_once __DIR__ . '/../core/helper.php';

class PostConstroller
{
    //GET /posts/post_slug
    public function showPost(string $post_slug)
    {
        render('post/show', ['title' => 'Post Detail Page']);
        var_dump($post_slug);
    }
    //GET /create-post
    public function showCreatePost()
    {
        //check session if not redirect to login

        render('post/create', ['title' => 'Create Post Page']);
    }
    //POST /create-post
    public function createPost()
    {
        //will be call from create showCreatePost page 
        //get user info from session
        // think about File upload
        // maybe create File upload handler?
        echo 'storePost called';
    }
    //GET /posts/post_slug/edit
    public function showEditpost() {}

    //PATCH /posts/post_slug/edit
    public function editpost()
    {
        echo 'editpost called';
    }

    // DELETE /posts/post_id/delete

    public function deletePost(string $post_id)
    {
        // will be called from user post page
        echo 'deletePost called';
    }
}
