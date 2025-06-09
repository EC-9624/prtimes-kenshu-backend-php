<?php

namespace App\Controllers;

require_once __DIR__ . '/../core/helper.php';

use App\Core\Database;
use App\Repositories\PostRepository;
use App\Models\Post;
use PDO;
use DateMalformedStringException;

class HomeController
{
    private PostRepository $postRepo;
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->postRepo = new PostRepository($this->pdo);
    }

    /**
     * Show the home page with all posts.
     */
    public function index(): void
    {
        $errors = $_SESSION['errors'] ?? null;
        unset($_SESSION['errors']);

        $postRows = $this->postRepo->fetchAllPostsRaw();
        if (count($postRows) === 0) {
            render('home/index', [
                'title' => 'Home Page',
                'data'  => [],
                'errors' => $errors
            ]);
            return;
        }

        $postIds = array_map(static fn($row) => $row['post_id'], $postRows);
        $tagRows = $this->postRepo->fetchTagsByPostIds($postIds);
        $tagMap = $this->groupTagsByPostId($tagRows);
        $posts = $this->buildPostModels($postRows, $tagMap);

        render('home/index', [
            'title' => 'Home Page',
            'data'  => $posts,
            'errors' => $errors
        ]);
    }

    /**
     * Show posts filtered by a tag slug.
     *
     * @param string $category  The tag slug (e.g. "technology" or "mobile").
     */
    public function showCategory(string $category): void
    {
        $postIds = $this->postRepo->fetchPostIdsByTag($category);
        if (count($postIds) === 0) {
            render('home/index', [
                'title' => $category . ' Page',
                'data'  => []
            ]);
            return;
        }

        $postRows = $this->postRepo->fetchPostsByIdsRaw($postIds);
        $tagRows = $this->postRepo->fetchTagsByPostIds($postIds);
        $tagMap = $this->groupTagsByPostId($tagRows);
        $posts = $this->buildPostModels($postRows, $tagMap);

        render('home/index', [
            'title' => $category . ' Page',
            'data'  => $posts
        ]);
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

    /**
     * Build Post models from raw post rows and tag map.
     *
     * @param array $postRows
     * @param array $tagMap
     * @return array<Post>
     */
    private function buildPostModels(array $postRows, array $tagMap): array
    {
        $posts = [];

        foreach ($postRows as $row) {
            try {
                $tagsForThisPost = $tagMap[$row['post_id']] ?? [];
                $row['tags_json'] = json_encode($tagsForThisPost);
                $posts[] = Post::fromListViewData($row);
            } catch (DateMalformedStringException $e) {
                error_log("Failed to parse date for post ID {$row['post_id']}: " . $e->getMessage());
                continue;
            }
        }

        return $posts;
    }
}
