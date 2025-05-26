<?php

namespace App\Controllers;

require_once __DIR__ . '/../core/helper.php';

use App\Core\Database;
use App\Repositories\UserRepository;
use Exception;
use PDOException;

class AuthController
{
    public function showLoginForm()
    {
        render('auth/login', ['title' => 'Login Page']);
    }

    public function showRegisterForm()
    {
        render('auth/register', ['title' => 'Register Page']);
    }

    public function register(array $body = [])
    {

        $userName = trim($body['user_name'] ?? '');
        $email = trim($body['email'] ?? '');
        $password = trim($body['password'] ?? '');
        $confirmPassword = trim($body['confirm_password'] ?? '');

        $errors = [];

        if (empty($userName)) {
            $errors['user_name'] = 'User name is required.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required.';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        }

        if ($password !== $confirmPassword) {
            $errors['password'] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            render('auth/register', [
                'title' => 'Register Page',
                'errors' => $errors,
                'old' => compact('userName', 'email')
            ]);
            return;
        }

        // Save to database
        $database = new Database();
        $userRepo = new UserRepository($database);

        try {
            $user = $userRepo->create(
                $userName,
                $email,
                $password,
            );

            header('Location: /login');
            exit;
        } catch (Exception $e) {
            render('auth/register', [
                'title' => 'Register Page',
                'errors' => ['Registration failed. Please try again. Error :' . $e->getMessage()],
                'old' => compact('userName', 'email')
            ]);
        }
    }

    public function login(array $body = [])
    {
        $email = trim($body['email']);
        $password = trim($body['password']);
        $errors = [];

        if (empty($email)) {
            $errors['email'] = ' Email is required.';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        }

        if (!empty($errors)) {
            render('auth/login', [
                'title' => 'Login Page',
                'errors' => $errors,
                'old' => compact('email')
            ]);
            return;
        }

        $database = new Database();
        $userRepo = new UserRepository($database);
        try {
            $user = $userRepo->findByEmail($body['email']);

            if (!$user) {
                $errors['credentials'] = 'Invalid credentials.';
                render('auth/login', [
                    'title' => 'Login Page',
                    'errors' => $errors,
                    'old' => compact('email')
                ]);
                return;
            }

            if (!password_verify($password, $user['password'])) {
                $errors['credentials'] = 'Invalid credentials.';
                render('auth/login', [
                    'title' => 'Login Page',
                    'errors' => $errors,
                    'old' => compact('email')
                ]);
                return;
            }
            // Authentication Successful!

            // session_start() is now handled globally.
            // Only regenerate the ID here for security.
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['email'] = $user['email'];

            header('Location: /');
            exit;
        } catch (PDOException $e) {
            error_log("PDO Error during login: " . $e->getMessage());
            render('auth/login', [
                'title' => 'Login Page',
                'errors' => "PDO Error during login: " . $e->getMessage(),
                'old' => compact('email')
            ]);
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();

        header('Location: /login');
        exit;
    }
}
