<?php

namespace App\Controllers;

require_once __DIR__ . '/../core/helper.php';

use App\Core\Database;
use App\Models\Post;
use App\Repositories\PostRepository;
use Ramsey\Uuid\Uuid;
use DateTimeImmutable;

class PostConstroller
{

    private PostRepository $postRepo;

    public function __construct()
    {
        $database = new Database();
        $this->postRepo = new PostRepository($database);
    }

    //GET /posts/post_slug
    public function showPost(string $post_slug)
    {
        $postRow = $this->postRepo->fetchPostBySlug($post_slug);
        $postId = $postRow['post_id'];
        $tagRow = $this->postRepo->fetchTagsByPostIds($postId);

        //Group tags by post_id
        $tagMap = [];
        foreach ($tagRow as $tag) {
            $pid = $tag['post_id'];
            if (!isset($tagMap[$pid])) {
                $tagMap[$pid] = [];
            }
            $tagMap[$pid][] = [
                'name' => $tag['name'],
                'slug' => $tag['slug'],
            ];
        }

        $tagsForThisPost = $tagMap[$postRow['post_id']] ?? [];

        $post = new Post(
            Uuid::fromString($postRow['post_id']),
            Uuid::fromString($postRow['author_id']),
            $postRow['author'],
            $postRow['slug'],
            $postRow['title'],
            $postRow['text'],
            $postRow['image_path'],
            $tagsForThisPost,
            new DateTimeImmutable($postRow['created_at'])
        );

        render('post/show', ['title' => 'Post Detail Page', 'data' => $post]);
    }

    //GET /create-post
    public function showCreatePost()
    {
        //check session if not redirect to login

        render('post/create', ['title' => 'Create Post Page']);
    }
    //POST /create-post
    public function createPost($body)
    {
        var_dump($body);
        //will be call from create showCreatePost page 
        //get user info from session
        // $this->postRepo->create();
        echo 'create called';
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
