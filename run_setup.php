<?php
// Load env manually because db_connect might fail if DB doesn't exist
require_once __DIR__ . '/includes/env_loader.php';
EnvLoader::load();

$host = EnvLoader::get('DATABASE_HOST', 'localhost');
$dbname = EnvLoader::get('DATABASE_NAME', 'invoicing_system');
$user = EnvLoader::get('DATABASE_USER', 'root');
$pass = EnvLoader::get('DATABASE_PASS', '');

try {
    // Try connecting to MySQL without a database first
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "Database `$dbname` ensured.\n";
    
    // Connect to the specific database
    $pdo->exec("USE `$dbname`");
    
    // Import invoicing_system.sql
    if (file_exists(__DIR__ . '/db/invoicing_system.sql')) {
        $sql = file_get_contents(__DIR__ . '/db/invoicing_system.sql');
        $pdo->exec($sql);
        echo "Base schema (invoicing_system.sql) imported.\n";
    }

    // Import recurring_invoices.sql
    if (file_exists(__DIR__ . '/db/recurring_invoices.sql')) {
        $sql = file_get_contents(__DIR__ . '/db/recurring_invoices.sql');
        $pdo->exec($sql);
        echo "Recurring invoices schema imported.\n";
    }

    echo "All migrations completed successfully.\n";

} catch (PDOException $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "Tip: Check your MySQL credentials in .env\n";
    }
}
