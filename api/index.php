<?php
require_once __DIR__ . '/config/config.php';

// Set CORS headers based on environment
if (Config::allowCORS()) {
    header("Access-Control-Allow-Origin: *");
} else {
    // In production, you may want to restrict CORS to specific domains
    header("Access-Control-Allow-Origin: https://inv.kenzastore.my.id");
}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

// Adjust based on your local path structure. 
// Assuming URL is like /api/v1/resource
// $uri[0] is empty, $uri[1] is 'api', $uri[2] is 'v1', $uri[3] is resource
// If running at root of domain, adjustments might be needed.
// Let's assume the user accesses via /api/index.php/resource or rewritten to /api/resource

// Simple router
$resource = null;
$id = null;

// Find where 'api' is in the path to determine resource location
$apiIndex = array_search('api', $uri);
if ($apiIndex !== false) {
    // Check if next segment is index.php, if so skip it
    $nextIndex = $apiIndex + 1;
    if (isset($uri[$nextIndex]) && $uri[$nextIndex] === 'index.php') {
        $nextIndex++;
    }

    if (isset($uri[$nextIndex])) {
        $resource = $uri[$nextIndex];
        if (isset($uri[$nextIndex + 1])) {
            $id = $uri[$nextIndex + 1];
        }
    }
} else {
    // Fallback if 'api' is not in path (e.g. if document root is api folder)
    // This part depends heavily on server config. 
    // For now, let's assume standard /api/resource
}

// Route
switch ($resource) {
    case 'auth':
        require_once __DIR__ . '/controllers/AuthController.php';
        $controller = new AuthController();
        if ($id === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
        }
        break;
    
    case 'invoices':
        require_once __DIR__ . '/controllers/InvoiceController.php';
        $controller = new InvoiceController();
        
        // Check if there's a sub-resource (e.g., /invoices/123/generate)
        $subResource = isset($uri[$nextIndex + 2]) ? $uri[$nextIndex + 2] : null;
        
        if ($subResource === 'generate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->generatePdf($id);
        } else {
            $controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Resource not found: ' . $resource]);
        break;
}
