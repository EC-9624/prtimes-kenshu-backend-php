<?php

namespace App\Models;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\UUID;

class Post
{
    public UuidInterface $post_id;
    public UuidInterface $user_id;
    public string $title;
    public string $slug;
    public string $text;
    public ?string $thumbnail_image_path;
    public array $tags_json;
    public DateTimeImmutable $created_at;

    public function __construct(
        UuidInterface $post_id,
        UuidInterface $user_id,
        string $slug,
        string $title,
        string $text,
        ?string $thumbnail_image_path,
        array $tags_json,
        DateTimeImmutable $created_at
    ) {
        $this->post_id = $post_id;
        $this->user_id = $user_id;
        $this->slug = $slug;
        $this->title = $title;
        $this->text = $text;
        $this->thumbnail_image_path = $thumbnail_image_path;
        $this->tags_json = $tags_json;
        $this->created_at = $created_at;
    }

    public function getPostId(): UuidInterface
    {
        return $this->post_id;
    }


    public function getUserId(): UuidInterface
    {
        return $this->user_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getText(): string
    {
        return $this->text;
    }


    public function getThumbnailImagePath(): ?UuidInterface
    {
        return $this->thumbnail_image_path;
    }

    public function getTagsJson(): array
    {
        return $this->tags_json;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->created_at;
    }


    /**
     * Creates a Post instance for list view (without full text content).
     *
     * @param array{
     *     post_id: string,
     *     author_id: string,
     *     slug: string,
     *     title: string,
     *     image_path: ?string,
     *     tags_json: string,
     *     created_at: string
     * } $data Associative array of post data from the database query.
     *
     * @return self Instance of the Post model with essential data for list display.
     *
     */
    public static function fromListViewData(array $data): self
    {
        return new self(
            Uuid::fromString($data['post_id']),
            Uuid::fromString($data['author_id']),
            $data['slug'],
            $data['title'],
            '', // Text is not needed in list view
            $data['image_path'],
            json_decode($data['tags_json'], true),
            new DateTimeImmutable($data['created_at'])
        );
    }
}
