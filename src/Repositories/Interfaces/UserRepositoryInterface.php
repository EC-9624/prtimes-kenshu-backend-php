<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Ramsey\Uuid\UuidInterface;

interface UserRepositoryInterface
{
    public function findById(UuidInterface $userId): ?User;
    public function findByEmail(string $email): ?User;
    public function create($userName, $email, $password): User;
    // public function update(UuidInterface $userId, array $data): User;
    // public function delete(UuidInterface $userId): bool;
}
