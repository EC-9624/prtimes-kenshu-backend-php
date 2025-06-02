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
        try {
            // Fetch all posts
            $postStmt = $this->pdo->prepare(" SELECT
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
            ORDER BY p.created_at DESC");
            $postStmt->execute();
            $postRows = $postStmt->fetchAll();

            if (count($postRows) === 0) {
                return [];
            }

            // Fetch all tags for all posts
            $tagStmt = $this->pdo->prepare(" SELECT
                pt.post_id,
                t.name,
                t.slug
            FROM post_tags pt
            JOIN tags t ON pt.tag_id = t.tag_id");
            $tagStmt->execute();
            $tagRows = $tagStmt->fetchAll();

            // Group tags by post_id
            $tagMap = [];
            foreach ($tagRows as $tag) {
                $postId = $tag['post_id'];
                if (!isset($tagMap[$postId])) {
                    $tagMap[$postId] = [];
                }
                $tagMap[$postId][] = [
                    'name' => $tag['name'],
                    'slug' => $tag['slug']
                ];
            }

            // Combine post data with encoded tags
            $posts = [];
            foreach ($postRows as $row) {
                $row['tags_json'] = json_encode($tagMap[$row['post_id']] ?? []);
                $posts[] = Post::fromListViewData($row);
            }

            return $posts;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            print_r("Database error: " . $e->getMessage());
            return [];
        }
    }


    public function getPostsByTag(string $tagSlug): array
    {
        try {
            // Get post IDs that have the tag
            $stmt = $this->pdo->prepare("SELECT DISTINCT p.post_id
            FROM posts p
            JOIN post_tags pt ON pt.post_id = p.post_id
            JOIN tags t ON t.tag_id = pt.tag_id
            WHERE t.slug = :tag_slug");
            $stmt->bindParam(':tag_slug', $tagSlug, PDO::PARAM_STR);
            $stmt->execute();
            $postIdRows = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($postIdRows)) {
                return [];
            }

            // Get full post info
            $inClause = implode(',', array_fill(0, count($postIdRows), '?'));

            $stmt = $this->pdo->prepare("SELECT
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
            ORDER BY p.created_at DESC");
            $stmt->execute($postIdRows);
            $postRows = $stmt->fetchAll();

            //  Get all tags for these posts
            $stmt = $this->pdo->prepare("SELECT
                pt.post_id,
                t.name,
                t.slug
            FROM post_tags pt
            JOIN tags t ON pt.tag_id = t.tag_id
            WHERE pt.post_id IN ($inClause)");
            $stmt->execute($postIdRows);
            $tagRows = $stmt->fetchAll();

            // Group tags by post_id
            $tagMap = [];
            foreach ($tagRows as $tag) {
                $postId = $tag['post_id'];
                $tagMap[$postId][] = [
                    'name' => $tag['name'],
                    'slug' => $tag['slug']
                ];
            }

            // Build Post objects
            $posts = [];
            foreach ($postRows as $row) {
                $row['tags_json'] = json_encode($tagMap[$row['post_id']] ?? []);
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
