<?php

namespace App\Controllers;

require_once __DIR__ . '/../core/helper.php';

class AuthController
{
    public function index()
    {
        render('auth/login', ['title' => 'Login Page']);
    }

    public function showRegister()
    {
        render('auth/register', ['title' => 'Register Page']);
    }
}
