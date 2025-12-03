<?php
require_once __DIR__ . '/../utils/JWT.php';

class AuthMiddleware {
    public static function authenticate() {
        $headers = apache_request_headers();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

        if (!$authHeader) {
            // Fallback for some server configs where Authorization header is missing
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
        }

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            $payload = JWT::decode($token);
            if ($payload && isset($payload['user_id'])) {
                return $payload;
            }
        }

        header('HTTP/1.0 401 Unauthorized');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
}
