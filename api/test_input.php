<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Debug input reading
$rawInput = file_get_contents("php://input");
$postData = $_POST;
$phpInput = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : 'Not set';

echo json_encode([
    'debug' => true,
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'Not set',
    'raw_input' => $rawInput,
    'post_data' => $postData,
    'php_input' => $phpInput,
    'input_length' => strlen($rawInput),
    'timestamp' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT);
?>