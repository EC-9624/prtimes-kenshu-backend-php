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

    /**
     * Handles user registration.
     *
     * Validates user input, checks for errors, and if all is valid,
     * stores the new user into the database.
     *
     * @param array $body Associative array of form data (e.g. POST body).
     * Expected keys: 'user_name', 'email', 'password', 'confirm_password'
     * @return void
     */
    public function register(array $body = [])
    {
        $userName = trim($body['user_name']);
        $email = trim($body['email']);
        $password = trim($body['password']);
        $confirmPassword = trim($body['confirm_password']);

        $errors = [];

        if ($userName === '' || $password === null) {
            $errors['user_name'] = 'User name is required.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required.';
        }

        if ($password === '' || $password === null) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        }

        if ($password !== $confirmPassword) {
            $errors['password'] = 'Passwords do not match.';
        }

        if (count($errors)) {
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

    /**
     * Handles user login.
     *
     * @param array $body An associative array containing the request body data, typically from a POST request.
     * Expected keys: 'email', 'password'.
     * @return void
     */
    public function login(array $body = [])
    {
        $email = trim($body['email']);
        $password = trim($body['password']);
        $errors = [];

        if ($email === '' || $email === null) {
            $errors['email'] = ' Email is required.';
        }

        if ($password === "" || $password === null) {
            $errors['password'] = 'Password is required.';
        }

        if (count($errors)) {
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
            $user = $userRepo->findByEmail($email);

            if ($user === null) {
                $errors['credentials'] = 'Invalid credentials.';
                render('auth/login', [
                    'title' => 'Login Page',
                    'errors' => $errors,
                    'old' => compact('email')
                ]);
                return;
            }

            if (!password_verify($password, $user->getPassword() ?? '')) {
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

            $_SESSION['user_id'] = $user->getUserId()->toString();
            $_SESSION['user_name'] = $user->getUserName();
            $_SESSION['email'] = $user->getEmail();

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

    /**
     * Handles user logout.
     *
     * Destroys the session and redirects the user to the login page.
     *
     * @return void
     */
    public function logout()
    {
        session_unset();
        session_destroy();

        header('Location: /login');
        exit;
    }
}
