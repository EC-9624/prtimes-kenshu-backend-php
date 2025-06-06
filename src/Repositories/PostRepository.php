<?php

namespace App\Repositories;

use App\Repositories\Interfaces\PostRepositoryInterface;
use App\DTO\CreatePostDTO;
use PDOException;
use PDO;
use RuntimeException;
use Ramsey\Uuid\Uuid;



class PostRepository implements PostRepositoryInterface
{
    private PDO $pdo;
    private string $uploadFileSystemDirectory = __DIR__ . '/../../public/img/uploads/';

    public function __construct(PDO $pdoConnection)
    {
        $this->pdo = $pdoConnection;
    }

    /**
     * Fetch all posts.
     * Returns an array of associative arrays, each containing:
     *   post_id, title, slug, author (user_name), author_id, image_path, created_at
     *Use For Listview
     * @return array<int, array{post_id: string, title: string, slug: string, author: string, author_id: string, image_path: ?string, created_at: string}>
     */
    public function fetchAllPostsRaw(): array
    {
        $sql =
            "SELECT
                p.post_id,
                p.title,
                p.slug,
                u.user_name AS author,
                u.user_id AS author_id,
                i.image_path,
                p.created_at
            FROM posts p
            JOIN users u ON p.user_id = u.user_id
            LEFT JOIN images i ON p.thumbnail_image_id = i.image_id
            WHERE p.deleted_at IS NULL
            ORDER BY p.created_at DESC
            ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch tags for a given list of post IDs or a single post ID.
     * Returns an array of rows, each containing:
     * post_id, name, slug
     *
     * @param string[] $postIds A single post ID as a string, or an array of post IDs.
     * @return array<int, array{post_id: string, name: string, slug: string}>
     */
    public function fetchTagsByPostIds(array $postIds): array
    {
        // Ensure $postIds is always an array for consistent processing
        if (!is_array($postIds)) {
            $postIds = [$postIds];
        }

        if (count($postIds) === 0) {
            return [];
        }

        // Build an IN-clause with the correct number of placeholders
        $inClause = implode(',', array_fill(0, count($postIds), '?'));

        $sql =
            "SELECT
            pt.post_id,
            t.name,
            t.slug
        FROM post_tags pt
        JOIN tags t ON pt.tag_id = t.tag_id
        WHERE pt.post_id IN ($inClause)
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($postIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch post IDs that are linked to a given tag slug.
     *
     * @param string $tagSlug
     * @return string[] Array of post_id strings (UUIDs)
     */
    public function fetchPostIdsByTag(string $tagSlug): array
    {
        $sql =
            "SELECT DISTINCT 
                p.post_id
            FROM posts p
            JOIN post_tags pt ON pt.post_id = p.post_id
            JOIN tags t ON t.tag_id = pt.tag_id
            WHERE t.slug = :tag_slug
            AND p.deleted_at IS NULL
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tag_slug', $tagSlug, PDO::PARAM_STR);
        $stmt->execute();

        // FETCH_COLUMN gives a flat array of post_id strings
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Given an array of post IDs, fetch those posts’ raw data (no tags).
     * Use For Listview
     * @param string[] $postIds
     * @return array<int, array{post_id: string, title: string, slug: string, author: string, author_id: string, image_path: ?string, created_at: string}>
     */
    public function fetchPostsByIdsRaw(array $postIds): array
    {
        if (count($postIds) === 0) {
            return [];
        }

        $inClause = implode(',', array_fill(0, count($postIds), '?'));

        $sql =
            "SELECT
                p.post_id,
                p.title,
                p.slug,
                u.user_name AS author,
                u.user_id AS author_id,
                i.image_path,
                p.created_at
            FROM posts p
            JOIN users u ON p.user_id = u.user_id
            LEFT JOIN images i ON p.thumbnail_image_id = i.image_id
            WHERE p.post_id IN ($inClause)
            AND p.deleted_at IS NULL
            ORDER BY p.created_at DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($postIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $postSlug The slug of the post to retrieve.
     * 
     * @return array< {post_id: string, title: string, slug: string, author: string, author_id: string, image_path: ?string, created_at: string}>
     */
    public function fetchPostBySlug(string $postSlug): array
    {
        $sql =
            "SELECT
                p.post_id,
                p.title,
                p.slug,
                p.text,
                u.user_name AS author,
                u.user_id AS author_id,
                i.image_path,
                p.created_at
            FROM posts p
            JOIN users u ON p.user_id = u.user_id
            LEFT JOIN images i ON p.thumbnail_image_id = i.image_id
            WHERE p.slug = :post_slug
            AND p.deleted_at IS NULL
            ORDER BY p.created_at DESC
            ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':post_slug', $postSlug, PDO::PARAM_STR);
        $stmt->execute();
        return  $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new post, optional thumbnail image, and optional tags.
     *
     * @param CreatePostDTO{
     * user_id: string,
     * title: string,
     * slug: string,
     * text: string,
     * thumbnail_file_data?: array<string, mixed>|null, 
     * alt_text?: string|null,
     * tag_slugs?: string[]|null
     * } $data
     * @return string The ID of the newly created post, or null if file upload fails.
     *
     * @throws PDOException If a database operation fails.
     */
    public function create(CreatePostDTO $data): void
    {
        $postId = Uuid::uuid4()->toString();
        $imageId = null;
        $imagePath = null;

        // Handle File Upload and insert image first
        if (
            is_array($data->thumbnailFileData) &&
            isset($data->thumbnailFileData['tmp_name']) &&
            $data->thumbnailFileData['error'] === UPLOAD_ERR_OK &&
            !empty($data->thumbnailFileData['tmp_name'])
        ) {
            $uploadResult = $this->handleFileUpload($data->thumbnailFileData);

            if (!$uploadResult) {
                $this->pdo->rollBack();
                return;
            }

            $imageId = $uploadResult['image_id'];
            $imagePath = $uploadResult['image_path'];

            $imageSql = "
            INSERT INTO images (image_id, image_path, alt_text, created_at)
            VALUES (:image_id, :image_path, :alt_text, NOW())
        ";
            $imageStmt = $this->pdo->prepare($imageSql);
            $imageStmt->execute([
                ':image_id' => $imageId,
                ':image_path' => $imagePath,
                ':alt_text' => $data->altText,
            ]);
        }

        $postSql = "
        INSERT INTO posts (post_id, user_id, title, slug, text, thumbnail_image_id, created_at)
        VALUES (:post_id, :user_id, :title, :slug, :text, :thumbnail_image_id, NOW())
    ";
        $postStmt = $this->pdo->prepare($postSql);
        $postStmt->execute([
            ':post_id' => $postId,
            ':user_id' => $data->userId,
            ':title' => $data->title,
            ':slug' => $data->slug,
            ':text' => $data->text,
            ':thumbnail_image_id' => $imageId,
        ]);

        if (!empty($data->tagSlugs)) {
            $placeholders = implode(',', array_fill(0, count($data->tagSlugs), '?'));

            $tagFetchSql = "
            SELECT tag_id FROM tags WHERE slug IN ($placeholders)
        ";
            $tagFetchStmt = $this->pdo->prepare($tagFetchSql);
            $tagFetchStmt->execute($data->tagSlugs);
            $foundTags = $tagFetchStmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($foundTags)) {
                $tagInsertSql = "
                INSERT INTO post_tags (post_id, tag_id, created_at)
                VALUES (:post_id, :tag_id, NOW())
            ";
                $tagInsertStmt = $this->pdo->prepare($tagInsertSql);
                foreach ($foundTags as $tagId) {
                    $tagInsertStmt->execute([
                        ':post_id' => $postId,
                        ':tag_id' => $tagId,
                    ]);
                }
            }
        }

        if ($imageId !== null) {
            $updateImageSql = "
            UPDATE images SET post_id = :post_id WHERE image_id = :image_id
        ";
            $updateImageStmt = $this->pdo->prepare($updateImageSql);
            $updateImageStmt->execute([
                ':post_id' => $postId,
                ':image_id' => $imageId,
            ]);
        }
    }

    private function handleFileUpload(array $uploadedFile): ?array
    {
        // Validate file type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        if (!in_array($uploadedFile['type'], $allowedMimeTypes)) {
            $message = "Invalid file type: " . $uploadedFile['type'];
            error_log($message);
            throw new RuntimeException($message);
        }

        // Generate new filename
        $fileExtension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
        $originalNameWithoutExt = pathinfo($uploadedFile['name'], PATHINFO_FILENAME);
        $timestamp = date('Ymd_His');
        $sanitizedFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $originalNameWithoutExt);
        $newFileName = $timestamp . '_' . $sanitizedFilename . '.' . $fileExtension;
        $destinationPath = $this->uploadFileSystemDirectory . $newFileName;

        // Move the file
        if (!move_uploaded_file($uploadedFile['tmp_name'], $destinationPath)) {
            $message = "Failed to move uploaded file.";
            error_log($message);
            throw new RuntimeException($message);
        }

        return [
            'image_id'   => Uuid::uuid4()->toString(),
            'image_path' => '/img/uploads/' . $newFileName,
        ];
    }
}
