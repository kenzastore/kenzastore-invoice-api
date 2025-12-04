<?php
require_once __DIR__ . '/../utils/JWT.php';

class AuthMiddleware {
    public static function authenticate() {
        // Try multiple methods to get the Authorization header
        $authHeader = '';
        
        // Method 1: apache_request_headers() - works on Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        }
        
        // Method 2: Check $_SERVER variables - works on nginx and other servers
        if (!$authHeader) {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
        }
        
        // Method 3: Check for lowercase version (some servers use lowercase)
        if (!$authHeader && isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        }

        // Log for debugging (remove in production after fixing)
        error_log("AuthMiddleware: Authorization header = " . ($authHeader ? 'present' : 'missing'));

        if (preg_match('/Bearer\\s(\\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            $payload = JWT::decode($token);
            
            error_log("AuthMiddleware: Token decoded = " . ($payload ? 'success' : 'failed'));
            error_log("AuthMiddleware: user_id present = " . (isset($payload['user_id']) ? 'yes' : 'no'));
            
            if ($payload && isset($payload['user_id'])) {
                return $payload;
            }
        }

        header('HTTP/1.0 401 Unauthorized');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
}
