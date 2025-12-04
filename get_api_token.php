<?php
session_start();
require_once 'db_connect.php';
require_once 'api/utils/JWT.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    // Generate a token for the current user
    $issuer = 'http://localhost';
    if (file_exists(__DIR__ . '/api/config/config.php')) {
        require_once __DIR__ . '/api/config/config.php';
        $issuer = Config::getApiBaseUrl();
    } elseif (isset($_SERVER['HTTP_HOST'])) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $issuer = $protocol . '://' . $_SERVER['HTTP_HOST'];
    }

    $payload = [
        'iss' => $issuer, // Issuer
        'aud' => $issuer, // Audience
        'iat' => time(), // Issued at
        'exp' => time() + (60 * 60 * 24), // Expiration time (24 hours)
        'data' => [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['email']
        ]
    ];

    $jwt = new JWT();
    $token = $jwt->encode($payload);

    echo json_encode(['status' => 'success', 'token' => $token]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
