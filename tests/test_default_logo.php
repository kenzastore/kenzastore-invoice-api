<?php
/**
 * Test Suite for Default Kenza Store Logo Resolution and Handling
 */

require_once __DIR__ . '/../includes/env_loader.php';

$allPassed = true;
$testCount = 0;
$passedCount = 0;

function assertCondition($name, $condition, $message = '') {
    global $allPassed, $testCount, $passedCount;
    $testCount++;
    if ($condition) {
        $passedCount++;
        echo "[PASS] {$name}\n";
    } else {
        $allPassed = false;
        echo "[FAIL] {$name}" . ($message ? " - {$message}" : "") . "\n";
    }
}

echo "=== Running Default Logo Handling Tests ===\n\n";

// Helper function that mirrors resolution logic in pages
function resolveBrandLogo($session, $storedInvoiceLogo = null) {
    // Expected default logo constant/path
    $defaultLogo = 'assets/images/logo-kenza-store.png';
    $defaultLogoFullPath = dirname(__DIR__) . '/' . $defaultLogo;

    // Check if session has explicitly removed logo
    if (isset($session['brand_logo_action']) && $session['brand_logo_action'] === 'removed') {
        return '';
    }

    // Check if explicit session logo path is valid
    if (isset($session['brand_logo_path']) && $session['brand_logo_path'] !== '') {
        $candidatePath = dirname(__DIR__) . '/' . ltrim($session['brand_logo_path'], '/');
        if (is_file($candidatePath) && is_readable($candidatePath)) {
            return $session['brand_logo_path'];
        }
    }

    // Check if invoice has stored logo
    if (!empty($storedInvoiceLogo)) {
        $candidatePath = dirname(__DIR__) . '/' . ltrim($storedInvoiceLogo, '/');
        if (is_file($candidatePath) && is_readable($candidatePath)) {
            return $storedInvoiceLogo;
        }
    }

    // Default fallback to Kenza Store logo
    if (is_file($defaultLogoFullPath) && is_readable($defaultLogoFullPath)) {
        return $defaultLogo;
    }

    return '';
}

// 1. Verify default logo asset exists and is readable
assertCondition(
    "Default logo asset exists",
    is_file(dirname(__DIR__) . '/assets/images/logo-kenza-store.png'),
    "Expected assets/images/logo-kenza-store.png to exist"
);

// 2. Test fresh session on new_invoice resolves to default Kenza Store logo
$freshSession = [];
$resolved = resolveBrandLogo($freshSession);
assertCondition(
    "Fresh session defaults to Kenza Store logo",
    $resolved === 'assets/images/logo-kenza-store.png',
    "Expected assets/images/logo-kenza-store.png, got: '{$resolved}'"
);

// 3. Test explicit removed action leaves logo empty
$removedSession = [
    'brand_logo_action' => 'removed',
    'brand_logo_path' => ''
];
$resolvedRemoved = resolveBrandLogo($removedSession);
assertCondition(
    "Explicit removed action leaves logo empty",
    $resolvedRemoved === '',
    "Expected empty logo, got: '{$resolvedRemoved}'"
);

// 4. Test custom uploaded logo in session overrides default
$customSession = [
    'brand_logo_path' => 'invoices/logos/INV-JAN-XXVI-003.jpg'
];
$resolvedCustom = resolveBrandLogo($customSession);
assertCondition(
    "Custom uploaded logo overrides default logo",
    $resolvedCustom === 'invoices/logos/INV-JAN-XXVI-003.jpg',
    "Expected custom logo path, got: '{$resolvedCustom}'"
);

// 5. Test update_invoice fallback when invoice has no custom logo
$invoiceWithoutLogo = null;
$resolvedUpdateFallback = resolveBrandLogo([], $invoiceWithoutLogo);
assertCondition(
    "Update invoice fallback uses default Kenza Store logo",
    $resolvedUpdateFallback === 'assets/images/logo-kenza-store.png',
    "Expected fallback to default logo, got: '{$resolvedUpdateFallback}'"
);

// 6. Test actual new_invoice.php content contains default logo integration
$newInvoiceContent = file_get_contents(dirname(__DIR__) . '/new_invoice.php');
assertCondition(
    "new_invoice.php references default logo asset",
    strpos($newInvoiceContent, 'assets/images/logo-kenza-store.png') !== false,
    "new_invoice.php does not reference assets/images/logo-kenza-store.png yet"
);

// 7. Test actual update_invoice.php content contains default logo integration
$updateInvoiceContent = file_get_contents(dirname(__DIR__) . '/update_invoice.php');
assertCondition(
    "update_invoice.php references default logo asset",
    strpos($updateInvoiceContent, 'assets/images/logo-kenza-store.png') !== false,
    "update_invoice.php does not reference assets/images/logo-kenza-store.png yet"
);

echo "\nSummary: {$passedCount}/{$testCount} tests passed.\n";

if (!$allPassed) {
    exit(1);
}
exit(0);
