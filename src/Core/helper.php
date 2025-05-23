<?php

function render(string $template, array $data = []): void
{
    extract($data);
    require __DIR__ . '/../Views/' . $template . '.php';
}
