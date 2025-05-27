<?php

namespace App\Models;

use Ramsey\Uuid\UuidInterface;

class User
{
    public UuidInterface $user_id;
    public string $user_name;
    public string $email;
    private ?string $password;


    public function __construct(
        UuidInterface $user_id,
        string $user_name,
        string $email,
        ?string $password = null
    ) {
        $this->user_id = $user_id;
        $this->user_name = $user_name;
        $this->email = $email;
        $this->password = $password;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
