<?php
session_start();
require_once 'db_connect.php';

// CSRF Protection: Ensure token exists and matches
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "<script>alert('Invalid CSRF token!'); window.location.href = 'error.php';</script>";
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Invalid request method!'); window.location.href = 'error.php';</script>";
    exit;
}

// Validate and sanitize user ID
$userId = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
if (!$userId) {
    echo "<script>alert('Invalid user ID!'); window.location.href = 'error.php';</script>";
    exit;
}

try {
    // Prepare statement to update verification status
    $stmt = $pdo->prepare("UPDATE users SET verified = 1 WHERE id = :id AND verified = 0");
    $stmt->execute(['id' => $userId]);

    // Check if the update was successful
    if ($stmt->rowCount() > 0) {
        echo "<script>alert('Account Verified! You can now use all features.'); window.location.href = 'success.php';</script>";
    } else {
        echo "<script>alert('Your account is already verified or does not exist.'); window.location.href = 'error.php';</script>";
    }
} catch (PDOException $e) {
    echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "'); window.location.href = 'error.php';</script>";
}
