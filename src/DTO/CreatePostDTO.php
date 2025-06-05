<?php

namespace App\DTO;

namespace App\DTO;

class CreatePostDTO
{
    public function __construct(
        public string $userId,
        public string $title,
        public string $slug,
        public string $text,
        public ?array $thumbnailFileData = null,
        public ?string $altText = null,
        public ?array $tagSlugs = null
    ) {}
}
