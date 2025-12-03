<?php
// Load environment variables
require_once __DIR__ . '/includes/env_loader.php';
EnvLoader::load();

// Database Connection using environment variables
$host = EnvLoader::get('DATABASE_HOST', 'localhost');
$dbname = EnvLoader::get('DATABASE_NAME', 'invoicing_system');
$user = EnvLoader::get('DATABASE_USER', 'root');
$pass = EnvLoader::get('DATABASE_PASS', '');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Connection successful - no output in production
} catch (PDOException $e) {
    // Log error instead of exposing details
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}

function logAction($pdo, $user_id, $action, $invoice_id = null, $details = null) {
  $stmt = $pdo->prepare("INSERT INTO logs (user_id, action, invoice_id, details) VALUES (?, ?, ?, ?)");
  $stmt->execute([$user_id, $action, $invoice_id, $details]);
}
