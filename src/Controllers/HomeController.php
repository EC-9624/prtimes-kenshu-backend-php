<?php

namespace App\Controllers;

require_once __DIR__ . '/../core/helper.php';

use App\Core\Database;
use App\Repositories\PostRepository;
use App\Models\Post;
use Exception;

class HomeController
{
    private PostRepository $postRepo;

    public function __construct()
    {
        $database = new Database();
        $this->postRepo = new PostRepository($database);
    }

    /**
     * Show the home page with all posts.
     */
    public function index()
    {
        try {
            // Fetch all raw post rows
            $postRows = $this->postRepo->fetchAllPostsRaw();
            if (count($postRows) === 0) {
                render('home/index', [
                    'title' => 'Home Page',
                    'data'  => []
                ]);
                return;
            }

            // Extract all post IDs
            $postIds = array_map(static fn($row) => $row['post_id'], $postRows);

            // Fetch tags for all these post IDs
            $tagRows = $this->postRepo->fetchTagsByPostIds($postIds);

            // Group tags by post_id
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

            // Build Post models
            $posts = [];
            foreach ($postRows as $row) {
                $tagsForThisPost = $tagMap[$row['post_id']] ?? null;
                $row['tags_json'] = json_encode($tagsForThisPost);

                // fromListViewData
                $posts[] = Post::fromListViewData($row);
            }

            render('home/index', [
                'title' => 'Home Page',
                'data'  => $posts
            ]);
        } catch (Exception $e) {
            error_log("Unexpected error in HomeController::index(): " . $e->getMessage());
            render('home/index', [
                'title' => 'Home Page',
            ]);
            var_dump($e);
        }
    }

    /**
     * Show posts filtered by a tag slug.
     *
     * @param string $category  The tag slug (e.g. "technology" or "mobile").
     */
    public function showCategory(string $category)
    {
        // get all post IDs that match this tag slug
        $postIds = $this->postRepo->fetchPostIdsByTag($category);

        if (count($postIds) === 0) {
            render('home/index', [
                'title' => $category . ' Page',
                'data'  => []
            ]);
            return;
        }

        // Fetch only those posts’ raw data
        $postRows = $this->postRepo->fetchPostsByIdsRaw($postIds);

        // Fetch tags for exactly these posts
        $tagRows = $this->postRepo->fetchTagsByPostIds($postIds);

        // Group tags by post_id
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

        // Build Post models
        $posts = [];
        foreach ($postRows as $row) {
            $tagsForThisPost = $tagMap[$row['post_id']] ?? [];
            $row['tags_json'] = json_encode($tagsForThisPost);
            $posts[] = Post::fromListViewData($row);
        }

        render('home/index', [
            'title' => $category . ' Page',
            'data'  => $posts
        ]);
    }
}
