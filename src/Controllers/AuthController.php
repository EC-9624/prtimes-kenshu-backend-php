<?php

namespace App\Controllers;

require_once __DIR__ . '/../core/helper.php';

use App\Core\Database;
use App\Repositories\UserRepository;
use Exception;

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
        $password = $body['password'] ?? '';
        $confirmPassword = $body['confirm_password'] ?? '';

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
}
