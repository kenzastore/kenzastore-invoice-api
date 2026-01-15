<?php
require_once __DIR__ . '/../../db_connect.php';
require_once __DIR__ . '/../config/config.php';

/**
 * Process Recurring Invoices
 * This script should be run periodically (e.g., via CRON)
 */

echo "Processing recurring invoices... " . date('Y-m-d H:i:s') . "\n";

try {
    // 1. Find due schedules
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM recurring_invoices WHERE status = 'active' AND next_run_date <= ?");
    $stmt->execute([$today]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($schedules) . " schedules to process.\n";

    foreach ($schedules as $schedule) {
        $pdo->beginTransaction();

        try {
            // 2. Fetch template invoice
            $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
            $stmt->execute([$schedule['invoice_template_id']]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$template) {
                throw new Exception("Template invoice not found: " . $schedule['invoice_template_id']);
            }

            // 3. Create new invoice
            $newInvoiceNumber = $template['invoice_number'] . '-' . date('YmdHi');
            $invoiceDate = date('Y-m-d');
            
            // Calculate due date based on payment terms
            $dueDate = date('Y-m-d', strtotime("+" . $template['payment_terms'] . " days"));

            $sql = "INSERT INTO invoices (
                user_id, invoice_number, invoice_date, due_date, payment_terms,
                bill_to, ship_to, notes, 
                subtotal, discount, tax, shipping, total, pdf_path
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $pdfPath = Config::getInvoiceBaseUrl() . $newInvoiceNumber . '.pdf';
            
            $stmt->execute([
                $schedule['user_id'], $newInvoiceNumber, $invoiceDate, $dueDate, $template['payment_terms'],
                $template['bill_to'], $template['ship_to'], $template['notes'],
                $template['subtotal'], $template['discount'], $template['tax'], $template['shipping'], $template['total'], $pdfPath
            ]);
            
            $newInvoiceId = $pdo->lastInsertId();

            // 4. Duplicate items
            $stmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
            $stmt->execute([$template['id']]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $insItem = $pdo->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, rate, amount) VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $insItem->execute([$newInvoiceId, $item['description'], $item['quantity'], $item['rate'], $item['amount']]);
            }

            // 5. Update schedule
            $nextRun = calculateNextRun($schedule['next_run_date'], $schedule['frequency']);
            $upd = $pdo->prepare("UPDATE recurring_invoices SET next_run_date = ?, last_run_at = CURRENT_TIMESTAMP WHERE id = ?");
            $upd->execute([$nextRun, $schedule['id']]);

            // 6. Log action
            logAction($pdo, $schedule['user_id'], 'recurring_invoice_generated', $newInvoiceId, "Generated from template {$template['id']} via schedule {$schedule['id']}");

            $pdo->commit();
            echo "Successfully generated invoice $newInvoiceNumber (ID: $newInvoiceId) for user {$schedule['user_id']}\n";

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Failed to process recurring schedule {$schedule['id']}: " . $e->getMessage());
            echo "ERROR: Schedule {$schedule['id']} - " . $e->getMessage() . "\n";
        }
    }

} catch (Exception $e) {
    error_log("Recurring process failed: " . $e->getMessage());
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}

function calculateNextRun($currentRunDate, $frequency) {
    $date = new DateTime($currentRunDate);
    switch ($frequency) {
        case 'weekly':
            $date->modify('+1 week');
            break;
        case 'monthly':
            $date->modify('+1 month');
            break;
        case 'annually':
            $date->modify('+1 year');
            break;
    }
    return $date->format('Y-m-d');
}
