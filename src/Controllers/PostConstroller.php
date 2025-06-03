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
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old']);

        render('post/create', [
            'title' => 'Create Post Page',
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    //POST /create-post
    public function createPost($body)
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['errors'] = ['Please log in to create a post.'];
            header('Location: /login');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $title = trim($body['title'] ?? '');
        $slug = trim($body['slug'] ?? '');
        $text = trim($body['text'] ?? '');
        $tagSlugs = $body['tag_slugs'] ?? [];
        $altText = trim($body['alt_text'] ?? '');

        $thumbnailFileData = null;
        if (isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] === UPLOAD_ERR_OK) {
            $thumbnailFileData = $_FILES['thumbnail_image'];
        } else if (isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $_SESSION['errors'] = ['File upload failed with error code: ' . $_FILES['thumbnail_image']['error']];
            $_SESSION['old'] = $body;
            header('Location: /create-post');
            exit();
        }

        // Validate input
        $errors = [];

        if ($title === '') {
            $errors[] = 'Post title is required.';
        }

        if ($slug === '') {
            $errors[] = 'Post slug is required.';
        }

        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors[] = 'Slug must only contain lowercase letters, numbers, and hyphens.';
        }

        if ($text === '') {
            $errors[] = 'Post content is required.';
        }

        if ($thumbnailFileData) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($thumbnailFileData['type'], $allowedTypes)) {
                $errors[] = 'Thumbnail image must be JPG, PNG, GIF, or WebP.';
            }
        }

        if (strlen($altText) > 255) {
            $errors[] = 'Alt text must be 255 characters or fewer.';
        }


        if (count($errors) > 0) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $body;
            header('Location: /create-post');
            exit();
        }

        // Passed validation
        $postData = [
            'user_id'             => $userId,
            'title'               => $title,
            'slug'                => $slug,
            'text'                => $text,
            'thumbnail_file_data' => $thumbnailFileData,
            'alt_text'            => $altText,
            'tag_slugs'           => $tagSlugs,
        ];

        $this->postRepo->create($postData);


        echo '<pre>' . htmlspecialchars(print_r($postData, true)) . '</pre>';

        echo 'createPost called';
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
