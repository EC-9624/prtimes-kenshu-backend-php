<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdoConnection)
    {
        $this->pdo = $pdoConnection;
    }

    public function findById(UuidInterface $userId): ?User
    {
        $stmt = $this->pdo->prepare("SELECT user_id, user_name, email FROM users WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId->toString()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? new User(
                Uuid::fromString($row['user_id']),
                $row['user_name'],
                $row['email'],
                $row['password']
            )
            : null;
    }

    public function findByUsername(string $userName): ?User
    {
        $stmt = $this->pdo->prepare("SELECT user_id, user_name, email FROM users WHERE user_name = ? LIMIT 1");
        $stmt->execute([$userName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? new User(
                Uuid::fromString($row['user_id']),
                $row['user_name'],
                $row['email'],
            )
            : null;
    }


    public function findByEmail(string $email): ?User
    {

        $stmt = $this->pdo->prepare("SELECT user_id, user_name, email, password FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();


        return $row
            ? new User(
                Uuid::fromString($row['user_id']),
                $row['user_name'],
                $row['email'],
                $row['password']
            )
            : null;
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
