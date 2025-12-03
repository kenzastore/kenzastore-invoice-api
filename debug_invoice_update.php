<?php
// Temporarily enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/db_connect.php';

echo "<h1>Debug: Process Update Invoice</h1>";

// Check if we're receiving the form data
echo "<h2>POST Data:</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Check session
echo "<h2>Session:</h2>";
echo "<pre>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "</pre>";

// Check database connection
echo "<h2>Database:</h2>";
try {
    $test = $pdo->query("SELECT 1");
    echo "✅ Database connection OK\n";
    
    // Check invoices table structure
    $cols = $pdo->query("DESCRIBE invoices")->fetchAll(PDO::FETCH_ASSOC);
    echo "\n<h3>Invoices Table Columns:</h3>";
    foreach ($cols as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage();
}

// Now try the actual save process
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['invoice_id'])) {
    echo "\n\n<h2>Attempting Save...</h2>";
    
    // Include the actual processor
    ob_start();
    try {
        include 'process_update_invoice.php';
        $output = ob_get_clean();
        echo "✅ Save successful!\n";
        echo $output;
    } catch (Throwable $e) {
        $output = ob_get_clean();
        echo "❌ Error occurred:\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . "\n";
        echo "Line: " . $e->getLine() . "\n";
        echo "\nStack Trace:\n" . $e->getTraceAsString();
        echo "\n\nOutput before error:\n" . $output;
    }
}
?>
