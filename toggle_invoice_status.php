<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$invoice_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$user_id = $_SESSION['user_id'];

if ($invoice_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid invoice ID']);
    exit;
}

try {
    // Check if invoice exists and belongs to user
    $stmt = $pdo->prepare("SELECT is_paid FROM invoices WHERE id = ? AND user_id = ?");
    $stmt->execute([$invoice_id, $user_id]);
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
        echo json_encode(['status' => 'error', 'message' => 'Invoice not found']);
        exit;
    }

    // Toggle status
    $new_status = $invoice['is_paid'] == 1 ? 0 : 1;

    $update_stmt = $pdo->prepare("UPDATE invoices SET is_paid = ? WHERE id = ?");
    $update_stmt->execute([$new_status, $invoice_id]);

    // Log action if function exists
    if (function_exists('logAction')) {
        $status_text = $new_status == 1 ? "PAID" : "UNPAID";
        logAction($pdo, $user_id, "Updated Invoice Status", $invoice_id, "Changed status to $status_text");
    }

    echo json_encode([
        'status' => 'success', 
        'new_status' => $new_status,
        'message' => 'Invoice marked as ' . ($new_status == 1 ? 'PAID' : 'UNPAID')
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
