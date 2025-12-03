<?php
// Simple test script to verify download functionality
session_start();

// Mock session data for testing
$_SESSION['user_id'] = 1;
$_SESSION['email'] = 'test@example.com';

require_once __DIR__ . '/db_connect.php';

// Check if we have any invoices in the database
try {
    $stmt = $pdo->prepare('SELECT id, invoice_number FROM invoices LIMIT 5');
    $stmt->execute();
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Available Invoices for Testing:</h2>";
    if (empty($invoices)) {
        echo "<p>No invoices found in database.</p>";
        echo "<p>You can create a new invoice first using <a href='new_invoice.php'>new_invoice.php</a></p>";
    } else {
        echo "<ul>";
        foreach ($invoices as $invoice) {
            echo "<li>";
            echo "Invoice #{$invoice['invoice_number']} (ID: {$invoice['id']}) - ";
            echo "<a href='update_invoice.php?id={$invoice['id']}' target='_blank'>Test Update Page</a>";
            echo "</li>";
        }
        echo "</ul>";
    }
    
    echo "<h3>Test Instructions:</h3>";
    echo "<ol>";
    echo "<li>Click on one of the 'Test Update Page' links above</li>";
    echo "<li>The update invoice page should load with existing data</li>";
    echo "<li>Click the 'Download' button to test the new functionality</li>";
    echo "<li>The download should work like the new invoice download</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Download Functionality</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h2, h3 { color: #333; }
        ul, ol { margin: 20px 0; }
        li { margin: 10px 0; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
</body>
</html>
