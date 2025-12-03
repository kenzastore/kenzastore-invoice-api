<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/JWT.php';

class AuthController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Email and password required']);
            return;
        }

        $stmt = $this->db->prepare("SELECT id, email, password_hash FROM users WHERE email = :email AND verified = 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        error_log("Login attempt for: $email. User found: " . ($user ? 'yes' : 'no'));
        if ($user) {
            error_log("Hash verification: " . (password_verify($password, $user['password_hash']) ? 'pass' : 'fail'));
        }

        if ($user && password_verify($password, $user['password_hash'])) {
            $payload = [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'exp' => time() + (60 * 60 * 24) // 24 hours expiration
            ];
            $token = JWT::encode($payload);
            echo json_encode(['status' => 'success', 'token' => $token]);
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        }
    }
}
