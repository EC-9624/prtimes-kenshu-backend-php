<?php

namespace App\Repositories;

use App\Core\Database;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Models\Post;
use PDO;
use PDOException;

class PostRepository implements PostRepositoryInterface
{
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->getConnection();
    }

    public function getAllPosts()
    {
        $sql = "
        SELECT
            p.post_id,
            p.title,
            p.slug,
            u.user_name AS \"author\",
            u.user_id AS \"author_id\",
            i.image_path,
            -- Aggregate tags into a JSONB array of objects
            JSONB_AGG(
                JSONB_BUILD_OBJECT('name', t.name, 'slug', t.slug)
                ORDER BY
                    t.name
            ) AS tags_json,
            p.created_at
        FROM
            posts AS p
            JOIN users AS u ON p.user_id = u.user_id
            LEFT JOIN images AS i ON p.thumbnail_image_id = i.image_id
            JOIN post_tags AS pt ON pt.post_id = p.post_id
            JOIN tags AS t ON pt.tag_id = t.tag_id
        GROUP BY
            p.post_id,
            p.title,
            p.slug,
            u.user_name,
            u.user_id,
            i.image_path,
            p.created_at
        ORDER BY
            p.created_at DESC;
    ";


        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();


            if (count($rows) <= 0) {
                return [];
            }

            $posts = [];
            foreach ($rows as $row) {
                $posts[] = Post::fromListViewData($row);
            }
            return $posts;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            print_r("Database error: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Fetches posts filtered by a specific tag slug.
     *
     * @param string $tagSlug The slug of the tag to filter posts by (e.g., 'apps').
     * @return array An array of associative arrays, each representing a post.
     */
    public function getPostsByTag(string $tagSlug): array
    {
        $sql = "
            -- Step 1: Filter to posts (:tag_slug)
            WITH filtered_posts AS (
                SELECT DISTINCT
                    p.post_id
                FROM
                    posts p
                JOIN
                    post_tags pt ON pt.post_id = p.post_id
                JOIN
                    tags t ON t.tag_id = pt.tag_id
                WHERE
                    t.slug = :tag_slug
            )
            -- Step 2: Join but limit to posts from filtered_posts
            SELECT
                p.post_id,
                p.title,
                p.slug,
                u.user_name AS \"author\",
                u.user_id AS \"author_id\",
                i.image_path,
                -- Aggregate all tags for the post, not just the filtered one
                JSONB_AGG(
                    JSONB_BUILD_OBJECT('name', t.name, 'slug', t.slug)
                    ORDER BY
                        t.name
                ) AS tags_json,
                p.created_at
            FROM
                filtered_posts fp
            JOIN
                posts p ON p.post_id = fp.post_id
            JOIN
                users u ON p.user_id = u.user_id
            LEFT JOIN
                images i ON p.thumbnail_image_id = i.image_id
            JOIN
                post_tags pt ON pt.post_id = p.post_id
            JOIN
                tags t ON t.tag_id = pt.tag_id
            GROUP BY
                p.post_id,
                p.title,
                p.slug,
                u.user_name,
                u.user_id,
                i.image_path,
                p.created_at
            ORDER BY
                p.created_at DESC;
        ";

        try {

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':tag_slug', $tagSlug, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $posts = [];
            foreach ($rows as $row) {
                $posts[] = Post::fromListViewData($row);
            }
            return $posts;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            print_r("Database error: " . $e->getMessage());
            return [];
        }
    }
    // public function findById(UuidInterface $postId): ?Post {}
    // public function create(): Post {}
}
