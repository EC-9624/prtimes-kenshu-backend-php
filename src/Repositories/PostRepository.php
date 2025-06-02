<?php

namespace App\Repositories;

use App\Core\Database;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Models\Post;
use PDO;
use Ramsey\Uuid\Uuid;



class PostRepository implements PostRepositoryInterface
{
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->getConnection();
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
     * @param string|string[] $postIds A single post ID as a string, or an array of post IDs.
     * @return array<int, array{post_id: string, name: string, slug: string}>
     */
    public function fetchTagsByPostIds(string|array $postIds): array
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

    public function fetchPostBySlug(string $postSlug)
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
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //     
    //     [
    //     'user_id'     => 'a2aef7d4-9c68-4e9f-b345-1e3ac93f1bb7',
    //     'title'       => 'My First Post',
    //     'slug'        => 'my-first-post',
    //     'text'        => 'This is the body of the post.',
    //     'image_path'  => '/uploads/example.jpg',  // optional
    //     'alt_text'    => 'Sample thumbnail',      // optional
    //     'tag_slugs'   => ['technology', 'mobile'] // optional
    // ]

    /**
     * Create a new post, optional thumbnail image, and optional tags.
     *
     * @param array{
     *   user_id: string,
     *   title: string,
     *   slug: string,
     *   text: string,
     *   image_path?: string|null,
     *   alt_text?: string|null,
     *   tag_slugs?: string[]|null
     * } $data
     *
     * @return Post|null  The newly‐created Post model, or null on failure.
     *                    
     */
    public function create(array $data) //: ?Post
    {
        $this->pdo->beginTransaction();

        // Generate UUIDs
        $postId = Uuid::uuid4()->toString();
        $imageId = null;

        // If an image_path was passed and is not empty, generate imageId now
        if (
            array_key_exists('image_path', $data)
            && $data['image_path'] !== null
            && trim((string)$data['image_path']) !== ''
        ) {
            $imageId = Uuid::uuid4()->toString();
        }

        // Insert the post row (thumbnail_image_id may be null or actual UUID)
        $postSql = "
                INSERT INTO posts
                  (post_id, user_id, title, slug, text, thumbnail_image_id, created_at)
                VALUES
                  (:post_id, :user_id, :title, :slug, :text, :thumbnail_image_id, NOW())
            ";
        $postStmt = $this->pdo->prepare($postSql);
        $postStmt->execute([
            ':post_id'            => $postId,
            ':user_id'            => $data['user_id'],
            ':title'              => $data['title'],
            ':slug'               => $data['slug'],
            ':text'               => $data['text'],
            ':thumbnail_image_id' => $imageId,       // may be null
        ]);

        // If we have an image, insert into images with post_id referencing the new post
        if ($imageId !== null) {
            $imageSql = "
                    INSERT INTO images
                      (image_id, post_id, image_path, alt_text, created_at)
                    VALUES
                      (:image_id, :post_id, :image_path, :alt_text, NOW())
                ";
            $imageStmt = $this->pdo->prepare($imageSql);
            $imageStmt->execute([
                ':image_id'   => $imageId,
                ':post_id'    => $postId,
                ':image_path' => $data['image_path'],
                ':alt_text'   => $data['alt_text'] ?? null,
            ]);
        }

        // If tag_slugs is a non‐empty array, fetch their IDs and insert into post_tags
        if (
            array_key_exists('tag_slugs', $data)
            && is_array($data['tag_slugs'])
            && count($data['tag_slugs']) > 0
        ) {
            $tagSlugs = $data['tag_slugs'];
            // Build an IN clause 
            $placeholders = implode(',', array_fill(0, count($tagSlugs), '?'));
            $tagFetchSql = "
                    SELECT tag_id
                    FROM tags
                    WHERE slug IN ($placeholders)
                ";
            $tagFetchStmt = $this->pdo->prepare($tagFetchSql);
            // Execute with numeric array of tag slugs
            $tagFetchStmt->execute($tagSlugs);
            $foundTags = $tagFetchStmt->fetchAll(PDO::FETCH_COLUMN);

            if (is_array($foundTags) && count($foundTags) > 0) {
                $tagInsertSql = "
                        INSERT INTO post_tags (post_id, tag_id, created_at)
                        VALUES (:post_id, :tag_id, NOW())
                    ";
                $tagInsertStmt = $this->pdo->prepare($tagInsertSql);
                foreach ($foundTags as $tagId) {
                    $tagInsertStmt->execute([
                        ':post_id' => $postId,
                        ':tag_id'  => $tagId,
                    ]);
                }
            }
        }

        $this->pdo->commit();

        //Return the freshly‐created post 
        $rows = $this->fetchPostsByIdsRaw([$postId]);
        return count($rows) ? $rows[0] : null;
    }
}
