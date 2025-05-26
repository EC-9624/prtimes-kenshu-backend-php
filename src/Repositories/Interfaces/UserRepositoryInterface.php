<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Ramsey\Uuid\UuidInterface; // Best practice for type hinting UUIDs

interface UserRepositoryInterface
{
    public function all(): array;
    public function find(UuidInterface $userId): ?User;
    public function create(array $data): User;
    public function update(UuidInterface $userId, array $data): User;
    public function delete(UuidInterface $userId): bool;
}
