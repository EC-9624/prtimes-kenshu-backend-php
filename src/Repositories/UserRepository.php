<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Core\Database;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->getConnection();
    }

    public function findById(UuidInterface $userId): ?User
    {
        $stmt = $this->pdo->prepare("SELECT user_id, user_name, email FROM users WHERE user_id = ?");
        $stmt->execute([$userId->toString()]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return new User(
            Uuid::fromString($row['user_id']),
            $row['user_name'],
            $row['email']
        );
    }

    public function findByEmail(string $email): ?array
    {

        $stmt = $this->pdo->prepare("SELECT user_id, user_name, email, password FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        // Return the full row, including the hashed password
        // The password verification will happen in the controller, NOT the repository
        return $row;
    }

    public function create($userName, $email, $password): User
    {
        $userId = Uuid::uuid4();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare(
            "INSERT INTO users (user_id, user_name, email, password) VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            $userId->toString(),
            $userName,
            $email,
            $hashedPassword,
        ]);

        return new User(
            $userId,
            $userName,
            $email,
        );
    }
}
