<?php
require_once __DIR__ . '/../db_connect.php';

$email = 'apitest@example.com';
$password = 'password123';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        // Update password to be sure
        $pdo->prepare("UPDATE users SET password_hash = ?, verified = 1 WHERE email = ?")->execute([$hash, $email]);
        echo "User updated: $email / $password";
    } else {
        $pdo->prepare("INSERT INTO users (email, password_hash, verified) VALUES (?, ?, 1)")->execute([$email, $hash]);
        echo "User created: $email / $password";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
