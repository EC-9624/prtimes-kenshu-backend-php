<?php


namespace App\DTO;

class UpdatePostDTO
{
    public string $postId;
    public string $title;
    public string $text;
    public array $tagSlugs;

    public function __construct(array $data)
    {
        $this->postId = $data['post_id'];
        $this->title = $data['title'];
        $this->text = $data['text'];
        $this->tagSlugs = $data['tag_slugs'] ?? [];
    }
}
