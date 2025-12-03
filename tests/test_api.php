<?php

$baseUrl = 'http://localhost:8080/kenzastore.my.id/api/index.php'; // Adjust if needed
$email = 'apitest@example.com';
$password = 'password123';

// 1. Login
echo "1. Testing Login...\n";
$ch = curl_init($baseUrl . '/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => $email, 'password' => $password]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); // Added Content-Type header
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response: $response\n";
$data = json_decode($response, true);

if ($httpCode === 200 && isset($data['token'])) { // Modified condition for success
    $token = $data['token'];
    echo "Login successful. Token received.\n";
    echo "Token: " . substr($token, 0, 20) . "...\n\n"; // Added token display
} else {
    die("Login failed. Please ensure valid credentials in the test script. Response: $response\n"); // More detailed error
}


// Helper function for authenticated requests
function apiRequest($method, $endpoint, $data = null) {
    global $baseUrl, $token;
    $ch = curl_init($baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    $headers = ['Authorization: Bearer ' . $token];
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers[] = 'Content-Type: application/json';
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// 2. Create Invoice
echo "2. Testing Create Invoice...\n";
$invoiceData = [
    'invoice_number' => 'API-TEST-' . time(),
    'bill_to' => 'Test Customer',
    'items' => [
        ['description' => 'Item 1', 'quantity' => 2, 'rate' => 100],
        ['description' => 'Item 2', 'quantity' => 1, 'rate' => 50]
    ],
    'tax' => 10,
    'discount' => 0,
    'shipping' => 0,
    'payment_terms' => 14
];
$createRes = apiRequest('POST', '/invoices', $invoiceData);
print_r($createRes);
$invoiceId = $createRes['data']['id'] ?? null;

if (!$invoiceId) {
    die("Failed to create invoice.\n");
}
echo "Invoice created with ID: $invoiceId\n\n";

// 3. Get Invoice
echo "3. Testing Get Invoice...\n";
$getRes = apiRequest('GET', '/invoices/' . $invoiceId);
print_r($getRes);
echo "Invoice retrieved.\n\n";

// 4. Update Invoice
echo "4. Testing Update Invoice...\n";
$invoiceData['items'][0]['quantity'] = 5; // Change quantity
$updateRes = apiRequest('PUT', '/invoices/' . $invoiceId, $invoiceData);
print_r($updateRes);
echo "Invoice updated.\n\n";

// 5. List Invoices
echo "5. Testing List Invoices...\n";
$listRes = apiRequest('GET', '/invoices');
// print_r($listRes); // Too verbose
echo "Invoices listed. Count: " . count($listRes['data']) . "\n\n";

// 6. Delete Invoice
echo "6. Testing Delete Invoice...\n";
$deleteRes = apiRequest('DELETE', '/invoices/' . $invoiceId);
print_r($deleteRes);
echo "Invoice deleted.\n";
