<?php

namespace App\Models;

use Ramsey\Uuid\UuidInterface;

class User
{
    public UuidInterface $user_id;
    public string $user_name;
    public string $email;

    public function __construct(
        UuidInterface $user_id,
        string $user_name,
        string $email
    ) {
        $this->user_id = $user_id;
        $this->user_name = $user_name;
        $this->email = $email;
    }

    public function getUserId(): UuidInterface
    {
        return $this->user_id;
    }

    public function getUserName(): string
    {
        return $this->user_name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
