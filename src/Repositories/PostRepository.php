<?php

namespace App\Repositories;

use App\Core\Database;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Models\Post;
use PDO;

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
    }

    // public function findById(UuidInterface $postId): ?Post {}
    // public function create(): Post {}
}
