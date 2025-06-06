<?php

namespace App\Core;

class Router
{
    protected string $uri;
    protected string $method;
    protected array  $routes  = [];
    protected array  $body    = [];

    public function __construct()
    {
        $this->uri    = strtok($_SERVER['REQUEST_URI'], '?');
        $this->method = strtoupper(
            $_REQUEST['_method']
                ?? $_SERVER['REQUEST_METHOD']
        );

        // 2) if it's not GET or POST, parse php://input:
        if (in_array($this->method, ['PUT', 'PATCH', 'DELETE'])) {
            $input = file_get_contents('php://input');
            parse_str($input, $this->body); // Store in $this->body, not $_POST
        } elseif ($this->method === 'POST') {
            // For POST requests, use $_POST data
            $this->body = $_POST;
        }
    }

    public function get(string $path, callable|array $action): void
    {
        $this->routes['GET'][$path] = $action;
    }

    public function post(string $path, callable|array $action): void
    {
        $this->routes['POST'][$path] = $action;
    }

    public function put(string $path, callable|array $action): void
    {
        $this->routes['PUT'][$path] = $action;
    }

    public function patch(string $path, callable|array $action): void
    {
        $this->routes['PATCH'][$path] = $action;
    }

    public function delete(string $path, callable|array $action): void
    {
        $this->routes['DELETE'][$path] = $action;
    }

    /**
     * @return mixed|void
     */
    public function dispatch()
    {
        $routes = $this->routes[$this->method] ?? [];
        if ($this->method == 'OPTIONS' || $this->method == 'HEAD') {
            http_response_code(405);
            echo 'Method Not Allowed';
            return;
        }

        foreach ($routes as $path => $action) {
            if ($this->match($path, $params)) {

                return $this->runAction($action, $params);
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }

    protected function match(string $path, &$params): bool
    {
        $pattern = preg_replace('#\{([\w]+)}#', '([\w-]+)', $path);
        $pattern = "#^{$pattern}$#";

        if (preg_match($pattern, $this->uri, $matches)) {
            array_shift($matches); // drop the full‐match
            $params = $matches;
            return true;
        }

        return false;
    }

    protected function runAction(callable|array $action, array $params)
    {
        $args = $params;

        if (!empty($this->body)) {
            $args[] = $this->body;
        }

        if (is_array($action)) {
            [$class, $method] = $action;
            $controller = new $class();
            return call_user_func_array([$controller, $method], $args);
        }

        return call_user_func_array($action, $args);
    }
}
