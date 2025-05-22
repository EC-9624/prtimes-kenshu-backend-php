<?php

namespace App\Core;

class Router
{
    public function dispatch($uri)
    {
        echo "Routing: " . $uri;
    }
}
