<?php

namespace App\Repositories\Interfaces;

use App\Models\Post;
use Ramsey\Uuid\UuidInterface;

interface PostRepositoryInterface
{
    public function fetchAllPostsRaw(): array;
    public function fetchTagsByPostIds(array $postIds): array;
    public function fetchPostIdsByTag(string $tagSlug): array;
    public function fetchPostsByIdsRaw(array $postIds): array;
}
