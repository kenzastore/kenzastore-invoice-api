<?php
header('Content-Type: application/json');

$flagFile = 'invoice_ready.txt';

if (file_exists($flagFile)) {
    $invoiceNumber = file_get_contents($flagFile); // Read invoice number
    echo json_encode(['status' => 'ready', 'invoice_number' => $invoiceNumber]);
} else {
    echo json_encode(['status' => 'pending']);
}
