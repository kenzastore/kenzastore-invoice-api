<?php
// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/db_connect.php';

/** ======= Helper: JS redirect ======= */
function js_redirect(string $url): never {
    echo '<script>location.href=' . json_encode($url, JSON_UNESCAPED_SLASHES) . ';</script>';
    exit;
}

$isAjax = (
    (isset($_POST['ajax']) && in_array($_POST['ajax'], ['1', 'true'], true))
    || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
);

/** ======= Helper: JSON responses ======= */
function respond_error(string $message, bool $asJson): never {
    if ($asJson) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    die($message);
}

function respond_success(array $payload, bool $asJson, ?string $redirectUrl = null): never {
    if ($asJson) {
        header('Content-Type: application/json');
        $response = array_merge(['status' => 'success'], $payload);
        if ($redirectUrl !== null) {
            $response['redirect'] = $redirectUrl;
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    if ($redirectUrl !== null) {
        js_redirect($redirectUrl);
    }

    exit;
}

/** ======= Guard: Session ======= */
if (!isset($_SESSION['user_id'])) {
    if ($isAjax) {
        respond_error('⚠️ Session expired. Please log in again.', true);
    }
    js_redirect('login.php');
}

$userId    = (int)($_SESSION['user_id'] ?? 0);
$invoiceId = isset($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : 0;
if ($invoiceId <= 0) {
    respond_error('⚠️ Invalid Invoice ID.', $isAjax);
}

/** ======= Load existing invoice ======= */
try {
    $st = $pdo->prepare('SELECT * FROM invoices WHERE id = :id AND user_id = :user_id');
    $st->execute([':id' => $invoiceId, ':user_id' => $userId]);
    $existingInvoice = $st->fetch(PDO::FETCH_ASSOC);
    if (!$existingInvoice) {
        respond_error('⚠️ Invoice not found or unauthorized access.', $isAjax);
    }
} catch (PDOException $e) {
    die('⚠️ Database error: ' . $e->getMessage());
}

/** ======= Validate payload ======= */
if (!isset($_POST['items']) || !is_array($_POST['items'])) {
    respond_error('⚠️ At least one invoice item is required.', $isAjax);
}

/** ======= Helpers ======= */
function cleanNumber($value): float {
    if ($value === null || $value === '') return 0.0;
    if (is_numeric($value)) return (float)$value;
    $normalized = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', (string)$value));
    return $normalized === '' ? 0.0 : (float)$normalized;
}

function detectMimeType(string $path): string {
    if (function_exists('mime_content_type')) {
        $m = @mime_content_type($path);
        if ($m) return $m;
    }
    $fi = @finfo_open(FILEINFO_MIME_TYPE);
    if ($fi) {
        $m = @finfo_file($fi, $path);
        @finfo_close($fi);
        if ($m) return $m;
    }
    return '';
}

/** ======= Extract inputs (safe defaults) ======= */
// Prefer posted value; otherwise keep the existing one
$invoiceNumber = trim((string)($_POST['invoice_number'] ?? ''));
if ($invoiceNumber === '') {
    $invoiceNumber = (string)($existingInvoice['invoice_number'] ?? '');
    if ($invoiceNumber === '') {
        respond_error('⚠️ Invoice number cannot be empty.', $isAjax);
    }
}


$invoiceDate  = (string)($_POST['invoice_date']   ?? $existingInvoice['invoice_date']   ?? date('Y-m-d'));
$paymentTerms = (int)   ($_POST['payment_terms']  ?? $existingInvoice['payment_terms']  ?? 0); // force INT
$dueDate      = (string)($_POST['due_date']       ?? $existingInvoice['due_date']       ?? $invoiceDate);

$billTo        = trim((string)($_POST['bill_to']   ?? $existingInvoice['bill_to'] ?? ''));
$shipTo        = trim((string)($_POST['ship_to']   ?? $existingInvoice['ship_to'] ?? ''));
$notes         = (string)     ($_POST['notes']     ?? $existingInvoice['notes']     ?? '');

$storeInfo     = trim((string)($_POST['store_info'] ?? ($_SESSION['store_info_prefill'] ?? '')));
$brandName     = trim((string)($_POST['brand_name'] ?? ($_SESSION['brand_name_prefill'] ?? '')));

/** Angka ringkasan */
$subtotal = cleanNumber($_POST['subtotal'] ?? $existingInvoice['subtotal'] ?? 0);
$discount = cleanNumber($_POST['discount'] ?? $existingInvoice['discount'] ?? 0);
$tax      = cleanNumber($_POST['tax']      ?? $existingInvoice['tax']      ?? 0); // percent
$shipping = cleanNumber($_POST['shipping'] ?? $existingInvoice['shipping'] ?? 0);
$total    = cleanNumber($_POST['total']    ?? $existingInvoice['total']    ?? 0);

/** Recompute total jika <= 0 (atau tidak dikirim) */
if ($total <= 0) {
    $taxAmount = $subtotal * ($tax / 100);
    $total     = max($subtotal - $discount + $taxAmount + $shipping, 0);
}

/** Note: amount_paid dan balance_due tidak ada di database, hanya untuk form */
$amountPaid = cleanNumber($_POST['amount_paid'] ?? 0);
$balanceDue = $total; // Total Keseluruhan = Total (mirror)

/** ======= Logo handling (uploaded | removed | keep) ======= */
$brandLogoAction = (string)($_POST['brand_logo_action'] ?? '');  // '', 'uploaded', 'removed', 'keep'

$logoBaseFromBrandNew = preg_replace('/[^A-Za-z0-9_-]/', '_', $brandName);
if ($logoBaseFromBrandNew === '') $logoBaseFromBrandNew = 'invoice_logo_' . $invoiceId;

$logoBaseFromBrandOld = preg_replace('/[^A-Za-z0-9_-]/', '_', (string)($existingInvoice['brand_name'] ?? ''));
if ($logoBaseFromBrandOld === '') $logoBaseFromBrandOld = 'invoice_logo_' . $invoiceId;

$logoDirAbs       = __DIR__ . '/invoices/logos';
$allowedLogoExts  = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];
$currentLogoExt   = '';
$currentLogoPath  = ''; // absolute
$currentLogoPub   = ''; // public relative

// Deteksi logo yang saat ini tersimpan (pakai base OLD dulu, kalau ganti brand kita bisa rename)
if (is_dir($logoDirAbs)) {
    foreach ($allowedLogoExts as $ext) {
        $cand = $logoDirAbs . '/' . $logoBaseFromBrandOld . '.' . $ext;
        if (is_file($cand)) {
            $currentLogoExt  = $ext;
            $currentLogoPath = $cand;
            $currentLogoPub  = 'invoices/logos/' . $logoBaseFromBrandOld . '.' . $ext;
            break;
        }
    }
}

/** Data upload sementara */
$uploadBinary  = null;
$uploadExt     = '';
$uploadMime    = '';
$finalLogoBase = $logoBaseFromBrandNew; // target nama file setelah update

if (!empty($_FILES['brand_logo']['tmp_name']) && is_uploaded_file((string)$_FILES['brand_logo']['tmp_name'])) {
    $f = $_FILES['brand_logo'];

    $err = (int)($f['error'] ?? UPLOAD_ERR_OK);
    if ($err !== UPLOAD_ERR_OK) respond_error('⚠️ Logo upload failed.', $isAjax);

    $size = (int)($f['size'] ?? 0);
    if ($size <= 0 || $size > 2 * 1024 * 1024) {
        respond_error('⚠️ Logo must be greater than 0 bytes and under 2MB.', $isAjax);
    }

    $tmp  = (string)$f['tmp_name'];
    $mime = detectMimeType($tmp);
    $map  = [
        'image/png'     => 'png',
        'image/jpeg'    => 'jpg',
        'image/gif'     => 'gif',
        'image/webp'    => 'webp',
        'image/svg+xml' => 'svg',
    ];
    if (!isset($map[$mime])) {
        respond_error('⚠️ Unsupported logo format. Please upload PNG, JPG, GIF, SVG, or WEBP.', $isAjax);
    }

    $bin = @file_get_contents($tmp);
    if ($bin === false) respond_error('⚠️ Unable to read uploaded logo.', $isAjax);

    $uploadBinary   = $bin;
    $uploadExt      = $map[$mime];
    $uploadMime     = $mime;
    $brandLogoAction = 'uploaded';
} elseif ($brandLogoAction === 'removed') {
    // keep removed flag
} elseif ($brandLogoAction === '') {
    // default: kalau memang sudah ada logo sebelumnya, keep
    $brandLogoAction = $currentLogoExt !== '' ? 'keep' : '';
}

/** ======= DB Transaction ======= */
try {
    $pdo->beginTransaction();

    // Siapkan kolom update (hanya field yang ada di database)
    $updateCols = [
        'invoice_number' => $invoiceNumber,
        'invoice_date'   => $invoiceDate,
        'payment_terms'  => $paymentTerms,
        'due_date'       => $dueDate,
        'bill_to'        => $billTo,
        'ship_to'        => $shipTo,
        'notes'          => $notes,
        'subtotal'       => $subtotal,
        'discount'       => $discount,
        'tax'            => $tax,
        'shipping'       => $shipping,
        'total'          => $total,
    ];

    // Hanya set pdf_path jika kolom itu memang ada dalam result existing (menghindari error di skema yang berbeda)
    if (array_key_exists('pdf_path', $existingInvoice)) {
        $updateCols['pdf_path'] = 'invoices/' . $invoiceNumber . '.pdf';
    }

    $setParts = [];
    $params   = [];
    foreach ($updateCols as $col => $val) {
        $setParts[] = "$col = ?";
        $params[]   = $val;
    }
    $params[] = $invoiceId;
    $params[] = $userId;

    $sql = 'UPDATE invoices SET ' . implode(', ', $setParts) . ' WHERE id = ? AND user_id = ?';
    $pdo->prepare($sql)->execute($params);

    // Replace items
    $pdo->prepare('DELETE FROM invoice_items WHERE invoice_id = ?')->execute([$invoiceId]);

    $ins = $pdo->prepare('INSERT INTO invoice_items (invoice_id, description, quantity, rate, amount) VALUES (?, ?, ?, ?, ?)');
    foreach (($_POST['items'] ?? []) as $it) {
        $desc = trim((string)($it['description'] ?? ''));
        if ($desc === '') continue;
        $qty  = cleanNumber($it['quantity'] ?? 0);
        $rate = cleanNumber($it['rate'] ?? 0);
        $amt  = cleanNumber($it['amount'] ?? ($qty * $rate));
        $ins->execute([$invoiceId, $desc, $qty, $rate, $amt]);
    }

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Duplicate key (invoice_number UNIQUE) → friendlier message
    $errorInfo = $e->errorInfo ?? null;  // [SQLSTATE, driver_code, driver_message]
    if (is_array($errorInfo) && isset($errorInfo[1]) && (int)$errorInfo[1] === 1062) {
        respond_error('Invoice number already exists. Please choose another one.', $isAjax);
    }

    // Generic DB error
    respond_error('Database error: ' . $e->getMessage(), $isAjax);
}


/** ======= File ops (logo) setelah commit ======= */
if ($brandLogoAction === 'uploaded' && $uploadBinary !== null && $uploadExt !== '') {
    // Pastikan folder
    $invoicesDir = __DIR__ . '/invoices';
    if (!is_dir($invoicesDir) && !mkdir($invoicesDir, 0777, true) && !is_dir($invoicesDir)) {
        respond_error('⚠️ Unable to prepare invoices directory.', $isAjax);
    }
    if (!is_dir($logoDirAbs) && !mkdir($logoDirAbs, 0777, true) && !is_dir($logoDirAbs)) {
        respond_error('⚠️ Unable to prepare logo directory.', $isAjax);
    }

    // Hapus semua ext lama utk base lama & baru
    foreach ($allowedLogoExts as $ext) {
        $pOld = $logoDirAbs . '/' . $logoBaseFromBrandOld . '.' . $ext;
        $pNew = $logoDirAbs . '/' . $logoBaseFromBrandNew . '.' . $ext;
        if (is_file($pOld)) @unlink($pOld);
        if ($logoBaseFromBrandNew !== $logoBaseFromBrandOld && is_file($pNew)) @unlink($pNew);
    }

    // Tulis file baru ke base NEW
    $newPath = $logoDirAbs . '/' . $logoBaseFromBrandNew . '.' . $uploadExt;
    if (@file_put_contents($newPath, $uploadBinary) === false) {
        respond_error('⚠️ Unable to store uploaded logo.', $isAjax);
    }

    // Simpan ke session
    $_SESSION['brand_logo_path'] = 'invoices/logos/' . $logoBaseFromBrandNew . '.' . $uploadExt;
    $_SESSION['brand_logo_mime'] = $uploadMime;

} elseif ($brandLogoAction === 'removed') {
    // Hapus semua file logo utk base OLD dan NEW (jaga-jaga jika base berubah)
    foreach ($allowedLogoExts as $ext) {
        $pOld = $logoDirAbs . '/' . $logoBaseFromBrandOld . '.' . $ext;
        $pNew = $logoDirAbs . '/' . $logoBaseFromBrandNew . '.' . $ext;
        if (is_file($pOld)) @unlink($pOld);
        if (is_file($pNew)) @unlink($pNew);
    }
    unset($_SESSION['brand_logo_path'], $_SESSION['brand_logo_mime']);

} elseif ($brandLogoAction === 'keep' && $logoBaseFromBrandNew !== $logoBaseFromBrandOld && $currentLogoExt !== '') {
    // Rename file logo jika base berubah (brand_name diubah)
    $invoicesDir = __DIR__ . '/invoices';
    if (!is_dir($invoicesDir) && !mkdir($invoicesDir, 0777, true) && !is_dir($invoicesDir)) {
        respond_error('⚠️ Unable to prepare invoices directory.', $isAjax);
    }
    if (!is_dir($logoDirAbs) && !mkdir($logoDirAbs, 0777, true) && !is_dir($logoDirAbs)) {
        respond_error('⚠️ Unable to prepare logo directory.', $isAjax);
    }

    $oldFile = $logoDirAbs . '/' . $logoBaseFromBrandOld . '.' . $currentLogoExt;
    $newFile = $logoDirAbs . '/' . $logoBaseFromBrandNew . '.' . $currentLogoExt;

    if (is_file($oldFile) && $oldFile !== $newFile) {
        @rename($oldFile, $newFile);
    }
    $_SESSION['brand_logo_path'] = 'invoices/logos/' . $logoBaseFromBrandNew . '.' . $currentLogoExt;
}

/** ======= Prefill session for next time ======= */
$_SESSION['brand_name_prefill'] = $brandName;
$_SESSION['store_info_prefill'] = $storeInfo;

// Store brand_name and store_info in session since they're not in database
// These will be used for PDF generation and form prefilling
if ($brandName !== '') {
    $_SESSION['brand_name_prefill'] = $brandName;
}
if ($storeInfo !== '') {
    $_SESSION['store_info_prefill'] = $storeInfo;
}

/** ======= Optional logging ======= */
if (function_exists('logAction')) {
    logAction($userId, $invoiceId, 'Updated Invoice', 'invoices', 'Updated Invoice Number: ' . $invoiceNumber);
}

/** ======= Redirect / Response ======= */
respond_success(
    ['invoice_id' => $invoiceId, 'invoice_number' => $invoiceNumber],
    $isAjax,
    'index.php'
);
