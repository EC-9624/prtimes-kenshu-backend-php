<?php
const VIEW_PATH = __DIR__ . '/../Views/';

/**
 * helper function to render View in controller
 * @param string $template
 * @param array $data
 * @return void
 */
function render(string $template, array $data = []): void
{
    extract($data);
    require VIEW_PATH . $template . '.php';
}

/**
 * @param $value
 * @return void
 */
function preDump($value): void
{
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
}
