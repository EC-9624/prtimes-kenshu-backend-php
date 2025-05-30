<?php

namespace App\Repositories\Interfaces;

use App\Models\Post;
use Ramsey\Uuid\UuidInterface;

interface PostRepositoryInterface
{
    public function getAllPosts();
    // public function findById(UuidInterface $postId): ?Post;
    // public function create(): Post;
}
