<?php

namespace App\Controllers;

require_once __DIR__ . '/../core/helper.php';

use App\Core\Database;
use App\Models\Post;
use App\Repositories\PostRepository;
use Ramsey\Uuid\Uuid;
use DateTimeImmutable;
use PDO;
use PDOException;

class PostController
{
    private PostRepository $postRepo;
    private PDO $pdo;
    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->postRepo = new PostRepository($this->pdo);
    }

    // GET /posts/post_slug
    public function showPost(string $post_slug)
    {
        $postRow = $this->postRepo->fetchPostBySlug($post_slug);
        $postId = $postRow['post_id'];
        $tagRows = $this->postRepo->fetchTagsByPostIds([$postId]);
        $tagMap = $this->groupTagsByPostId($tagRows);
        $tagsForThisPost = $tagMap[$postId] ?? [];

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

        render('post/show', [
            'title' => 'Post Detail Page',
            'data'  => $post
        ]);
    }

    // GET /create-post
    public function showCreatePost(): void
    {
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old']);

        render('post/create', [
            'title'  => 'Create Post Page',
            'errors' => $errors,
            'old'    => $old,
        ]);
    }

    // POST /create-post
    public function createPost($body): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['errors'] = ['Please log in to create a post.'];
            header("Location: '/login'");
            exit();
        }

        $userId = $_SESSION['user_id'];
        $validatedData = $this->validatePostForm($body, $_FILES);

        if (!empty($validatedData['errors'])) {
            $_SESSION['errors'] = $validatedData['errors'];
            $_SESSION['old'] = $body;
            header('Location: /create-post');
            exit();
        }

        $postData = [
            'user_id'             => $userId,
            'title'               => $validatedData['title'],
            'slug'                => $validatedData['slug'],
            'text'                => $validatedData['text'],
            'thumbnail_file_data' => $validatedData['thumbnailFileData'],
            'alt_text'            => $validatedData['alt_text'],
            'tag_slugs'           => $validatedData['tag_slugs'],
        ];


        // Start the transaction
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }

        try {

            $newPostId = $this->postRepo->create($postData); // Call the repository method

            if ($newPostId === null) {

                $_SESSION['errors'] = ['Failed to upload thumbnail image or create post d.'];
                $_SESSION['old'] = $body;
                header('Location: /create-post');
                exit();
            }

            $this->pdo->commit();


            $postRow = $this->postRepo->fetchPostBySlug($validatedData['slug']);

            if (!$postRow) {
                // This is a critical error if it happens after a successful creation and commit
                error_log("Critical error: Post with slug '{$validatedData['slug']}' was created (ID: $newPostId) but could not be retrieved.");
                $_SESSION['errors'] = ['A critical error occurred after creating the post.'];
                header('Location: /error'); // Redirect to a generic error page
                exit();
            }

            $postId = $postRow['post_id'];
            $tagRows = $this->postRepo->fetchTagsByPostIds([$postId]);
            $tagMap = $this->groupTagsByPostId($tagRows); // Assuming this helper exists
            $tagsForThisPost = $tagMap[$postId] ?? [];

            $newPost = new Post(
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

            $_SESSION['success_message'] = 'Post created successfully!';
            header('Location: /posts/' . $newPost->slug);
            exit();
        } catch (PDOException $e) {

            $this->pdo->rollBack();
            error_log("PDOException during post creation: " . $e->getMessage());
            $_SESSION['errors'] = ['An error occurred while creating the post.' . $e->getMessage()];
            $_SESSION['old'] = $body;
            header('Location: /create-post');
            exit();
        }
    }

    private function validatePostForm(array $body, array $files): array
    {
        $errors = [];

        $title = trim($body['title'] ?? '');
        $slug = trim($body['slug'] ?? '');
        $text = trim($body['text'] ?? '');
        $altText = trim($body['alt_text'] ?? '');
        $tagSlugs = $body['tag_slugs'] ?? [];

        $thumbnailFileData = null;
        if (isset($files['thumbnail_image'])) {
            $fileError = $files['thumbnail_image']['error'];
            if ($fileError === UPLOAD_ERR_OK) {
                $thumbnailFileData = $files['thumbnail_image'];
            } elseif ($fileError !== UPLOAD_ERR_NO_FILE) {
                $errors[] = 'File upload failed with error code: ' . $fileError;
            }
        }

        // Validation
        if ($title === '') {
            $errors[] = 'Post title is required.';
        }

        if ($slug === '') {
            $errors[] = 'Post slug is required.';
        } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
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

        return [
            'errors' => $errors,
            'title' => $title,
            'slug' => $slug,
            'text' => $text,
            'alt_text' => $altText,
            'tag_slugs' => $tagSlugs,
            'thumbnailFileData' => $thumbnailFileData,
        ];
    }


    // GET /posts/post_slug/edit
    public function showEditPost()
    {
        // TODO: implement edit post form
        echo 'showEditPost called';
    }

    // PATCH /posts/post_slug/edit
    public function editPost()
    {
        // TODO: implement patch update logic
        echo 'editPost called';
    }

    // DELETE /posts/post_id/delete
    public function deletePost(string $post_id)
    {
        // TODO: delete post logic
        echo 'deletePost called';
    }

    /**
     * Group tag rows by post ID.
     *
     * @param array $tagRows
     * @return array
     */
    private function groupTagsByPostId(array $tagRows): array
    {
        $tagMap = [];
        foreach ($tagRows as $tag) {
            $pid = $tag['post_id'];
            if (!isset($tagMap[$pid])) {
                $tagMap[$pid] = [];
            }
            $tagMap[$pid][] = [
                'name' => $tag['name'],
                'slug' => $tag['slug'],
            ];
        }
        return $tagMap;
    }
}
