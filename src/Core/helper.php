<?php
define('VIEW_PATH', __DIR__ . '/../Views/');

function render(string $template, array $data = []): void
{
    extract($data);
    require VIEW_PATH . $template . '.php';
}
