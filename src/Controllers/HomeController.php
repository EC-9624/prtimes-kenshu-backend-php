<?php

namespace App\Controllers;

class HomeController
{
    public function index()
    {
        echo "GET request handled";
    }

    public function post($body = [])
    {
        echo "POST request handled";
        echo "<pre>" . print_r($body, true) . "</pre>";
    }

    public function put($body = [])
    {
        echo "PUT request handled";
        echo "<pre>" . print_r($body, true) . "</pre>";
    }

    public function patch($body = [])
    {
        echo "PATCH request handled";
        echo "<pre>" . print_r($body, true) . "</pre>";
    }

    public function delete($body = [])
    {
        echo "DELETE request handled";
        echo "<pre>" . print_r($body, true) . "</pre>";
    }
}
