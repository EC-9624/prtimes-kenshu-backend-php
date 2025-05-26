<?php

namespace App\Controllers;

require_once __DIR__ . '/../core/helper.php';

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
}
