<?php

namespace App\Controllers;

require_once __DIR__ . '/../core/helper.php';

use App\Core\Database;
use App\DTO\CreatePostDTO;
use App\DTO\ValidatedFormDTO;
use App\DTO\UpdatePostDTO;
use App\Exceptions\PostCreationException;
use App\Exceptions\PostRetrievalException;
use App\Models\Post;
use App\Repositories\PostRepository;
use DateTimeImmutable;
use Exception;
use PDO;
use PDOException;
use Ramsey\Uuid\Uuid;

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

    /**
     * @param string $post_slug
     * @return void
     * @throws Exception
     */
    public function showPost(string $post_slug): void
    {
        $postRow = $this->postRepo->fetchPostBySlug($post_slug);
        $post = $this->getPost($postRow);

        render('post/show', [
            'title' => 'Post Detail Page',
            'data'  => $post
        ]);
    }

    // GET /create-post

    /**
     * @return void
     */
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
    // TODO : modify createPost to be able to upload multiple images
    /**
     * @param $body
     * @return void
     * @throws Exception
     */
    public function createPost($body): void
    {

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['errors'] = ['Please log in to create a post.'];
            header('Location: /login');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $validatedData = $this->validatePostForm($body, $_FILES);


        if ($validatedData->hasErrors()) {
            $_SESSION['errors'] = $validatedData->errors;
            $_SESSION['old'] = $body;
            header('Location: /create-post');
            exit();
        }

        $dto = new CreatePostDTO(
            userId: $userId,
            title: $validatedData->title,
            slug: $validatedData->slug,
            text: $validatedData->text,
            thumbnailFileData: $validatedData->thumbnailFileData,
            altText: $validatedData->altText,
            tagSlugs: $validatedData->tagSlugs
        );

        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }

        try {
            $this->postRepo->create($dto);
            $this->pdo->commit();

            $postRow = $this->postRepo->fetchPostBySlug($validatedData->slug);

            if (!$postRow) {
                throw new PostRetrievalException("Post was created but could not be retrieved by slug '{$validatedData['slug']}'");
            }

            $newPost = $this->getPost($postRow);

            $_SESSION['success_message'] = 'Post created successfully!';
            header('Location: /posts/' . $newPost->slug);
            exit();
        } catch (PDOException $e) {

            $this->pdo->rollBack();
            error_log("PDOException: " . $e->getMessage());
            throw new PostCreationException("Failed to create post: " . $e->getMessage(), 0, $e);
        } catch (PostRetrievalException $e) {

            error_log("PostRetrievalException: " . $e->getMessage());
            $_SESSION['errors'] = [
                'Post retrieval failed after creation. : ' . $e->getMessage()
            ];
            header('Location: /create-post');
            exit();
        } catch (PostCreationException $e) {

            error_log("PostCreationException: " . $e->getMessage());
            $_SESSION['errors'] = [
                'Failed to create post: " : ' . $e->getMessage()
            ];
            $_SESSION['old'] = $body;
            header('Location: /create-post');
            exit();
        }
    }

    // GET /posts/post_slug/edit
    /**
     * @param string $slug
     * @return void
     */
    public function showEditPost(string $slug): void
    {
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old'] ?? [];

        unset($_SESSION['errors'], $_SESSION['old']);

        $postRow = $this->postRepo->fetchPostBySlug($slug);
        if (!$postRow) {
            http_response_code(404);
            echo "Post not found.";
            return;
        }

        $postId = $postRow['post_id'];
        $tagRows = $this->postRepo->fetchTagsByPostIds([$postId]);
        $tagMap = $this->groupTagsByPostId($tagRows);
        $tagsForThisPost = $tagMap[$postId] ?? [];

        $isAuthor = isset($_SESSION['user_id']) && $_SESSION['user_id'] === $postRow['author_id'];

        render('post/edit', [
            'title' => 'Edit Post',
            'post' => $postRow,
            'tags' => $tagsForThisPost,
            'errors' => $errors,
            'old' => $old,
            'isAuthor' => $isAuthor
        ]);
    }



    // PATCH /posts/post_slug/edit

    /**
     * @param string $slug
     * @param array $body
     * @return void
     */
    public function editPost(string $slug, array $body): void
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['errors'] = ['Please log in to update posts.'];
            header("Location: /posts/{$slug}/edit");
            exit();
        }

        if ($_SESSION['user_id'] !== $body['author_id']) {
            $_SESSION['errors'] = ['You are not authorized to edit this post.'];
            header("Location: /posts/{$slug}/edit");
            exit();
        }

        $errors = $this->validateBasicPostFields($body);

        if (isset($error) && count($errors) > 0) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $body;
            header("Location: /posts/{$slug}/edit");
            exit();
        }

        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }

        try {
            $updateDto = new UpdatePostDTO($body);

            $this->postRepo->update($updateDto);
            $this->pdo->commit();

            header("Location: /posts/{$slug}");
            exit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("PDOException: " . $e->getMessage());
            $_SESSION['errors'] = [
                'A database error occurred during update: ' . $e->getMessage()
            ];
        }
    }

    // DELETE /posts/post_id/delete

    /**
     * @param string $slug
     * @param array $body
     * @return void
     */
    public function deletePost(string $slug, array $body): void
    {
        // TODO: delete post logic
        echo $slug;
        print_r($body);
        echo 'deletePost called';
    }

    /**
     * Group tag rows by post ID.
     *
     * @param array $tagRows
     * @return array<string, array<int, array{name: string, slug: string}>>
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
        preDump($tagMap);
        return $tagMap;
    }

    /**
     * helper function to  validate post title , text
     * @param array $body
     * @return array
     */
    private function validateBasicPostFields(array $body): array
    {
        $errors = [];

        $title = trim($body['title'] ?? '');
        $text = trim($body['text'] ?? '');

        if ($title === '') {
            $errors[] = 'Title is required.';
        }

        if ($text === '') {
            $errors[] = 'Body content is required.';
        }

        return $errors;
    }

    /**
     * helper function to validate post form
     * @param array $body
     * @param array $files
     * @return ValidatedFormDTO
     */
    private function validatePostForm(array $body, array $files): ValidatedFormDTO
    {

        $errors = $this->validateBasicPostFields($body);

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

        if ($slug === '') {
            $errors[] = 'Post slug is required.';
        } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors[] = 'Slug must only contain lowercase letters, numbers, and hyphens.';
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

        return new ValidatedFormDTO(
            errors: $errors,
            title: $title,
            slug: $slug,
            text: $text,
            altText: $altText,
            tagSlugs: $tagSlugs,
            thumbnailFileData: $thumbnailFileData
        );
    }

    /**
     * function to retrieve post detail
     * @param array $postRow
     * @return Post
     * @throws Exception
     */
    public function getPost(array $postRow): Post
    {
        $postId = $postRow['post_id'];
        $tagRows = $this->postRepo->fetchTagsByPostIds([$postId]);
        $tagMap = $this->groupTagsByPostId($tagRows);
        $tagsForThisPost = $tagMap[$postId] ?? [];

        return new Post(
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
    }
}
