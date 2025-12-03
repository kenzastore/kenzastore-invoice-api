<?php
require_once 'db_connect.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    try {
        // Verify the token
        $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = :token AND verified = 0");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Mark the user as verified
            $stmt = $pdo->prepare("UPDATE users SET verified = 1, verification_token = NULL WHERE id = :id");
            $stmt->execute(['id' => $user['id']]);

            echo "<h1>Email Verified!</h1><p>Your email has been successfully verified. You can now <a href='login.php'>login</a>.</p>";
        } else {
            echo "<h1>Invalid or Expired Token</h1><p>The verification link is invalid or has expired.</p>";
        }
    } catch (PDOException $e) {
        echo "<h1>Error</h1><p>Database error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<h1>Invalid Request</h1><p>No verification token provided.</p>";
}
