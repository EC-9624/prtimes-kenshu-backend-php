<?php
echo "Method: <pre>" . $_SERVER['REQUEST_METHOD'] . "</pre>\n";
echo "URI: <pre>" . $_SERVER['REQUEST_URI'] . "</pre>\n";
echo "Server: <pre>" . print_r($_SERVER, true) . "</pre>";
