<?php

namespace App\Repositories;

use App\Core\Database;
use App\Repositories\Interfaces\PostRepositoryInterface;
use PDO;

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
     *
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
     * Fetch tags for a given list of post IDs.
     * Returns an array of rows, each containing:
     *   post_id, name, slug
     *
     * @param string[] $postIds
     * @return array<int, array{post_id: string, name: string, slug: string}>
     */
    public function fetchTagsByPostIds(array $postIds): array
    {
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
     *
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
}
