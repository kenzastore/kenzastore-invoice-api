<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Debug routing
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriSegments = explode('/', $uri);

echo json_encode([
    'debug' => true,
    'request_uri' => $_SERVER['REQUEST_URI'],
    'parsed_path' => $uri,
    'uri_segments' => $uriSegments,
    'method' => $_SERVER['REQUEST_METHOD'],
    'timestamp' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT);
?>