<?php

namespace App\DTO;

namespace App\DTO;

class ValidatedFormDTO
{
    public function __construct(
        public array $errors,
        public string $title,
        public string $slug,
        public string $text,
        public string $altText,
        public array $tagSlugs,
        public ?array $thumbnailFileData = null
    ) {}

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
