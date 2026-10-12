<?php
session_start();

// Mock session data for testing
$_SESSION['user_id'] = 1;
$_SESSION['email'] = 'test@example.com';

require_once __DIR__ . '/db_connect.php';

$userId    = (int) ($_SESSION['user_id'] ?? 0);
$invoiceId = isset($_GET['id']) ? (int) $_GET['id'] : 647; // Default to first invoice for testing

if ($invoiceId <= 0) {
    die('⚠️ Invalid Invoice ID.');
}

try {
    $invoiceStmt = $pdo->prepare('SELECT * FROM invoices WHERE id = :id');
    $invoiceStmt->execute([':id' => $invoiceId]);
    $invoice = $invoiceStmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
        die('⚠️ Invoice not found.');
    }

    $itemsStmt = $pdo->prepare('SELECT * FROM invoice_items WHERE invoice_id = :invoice_id ORDER BY id ASC');
    $itemsStmt->execute([':invoice_id' => $invoiceId]);
    $invoiceItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    die('⚠️ Database error: ' . $exception->getMessage());
}

if (!$invoiceItems) {
    $invoiceItems = [];
}
if (count($invoiceItems) === 0) {
    $invoiceItems[] = [
        'description' => '',
        'quantity'    => 0,
        'rate'        => 0,
        'amount'      => 0,
    ];
}

function escape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function formatNumberInput($value, int $decimals = 2): string
{
    if ($value === null || $value === '') {
        return number_format(0, $decimals, '.', '');
    }
    if (!is_numeric($value)) {
        $value = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', (string)$value));
    }
    if ($value === '' || $value === null) {
        $value = 0;
    }
    $number = (float)$value;
    return number_format($number, $decimals, '.', '');
}

// ===== Prefill Brand & Store Info (fallback ke session/default) =====
$defaultBrandName = 'Kenza Store';
$defaultStoreInfo = "KZ-Store\n62 895 3376 74116\nKendangsari Gg 4/45 A-7, Surabaya 60292";

$storedBrandName = trim((string)($invoice['brand_name'] ?? ''));
if ($storedBrandName === '') {
    $storedBrandName = (string)($_SESSION['brand_name_prefill'] ?? $defaultBrandName);
    if ($storedBrandName === '') $storedBrandName = $defaultBrandName;
}

$storedStoreInfo = trim((string)($invoice['store_info'] ?? ''));
if ($storedStoreInfo === '') {
    $storedStoreInfo = (string)($_SESSION['store_info_prefill'] ?? $defaultStoreInfo);
    if ($storedStoreInfo === '') $storedStoreInfo = $defaultStoreInfo;
}

// ===== Prefill field invoice dasar =====
$generatedInvoiceNumber = (string)($invoice['invoice_number'] ?? '');
$invoiceDate            = (string)($invoice['invoice_date'] ?? date('Y-m-d'));
$paymentTerms           = (string)($invoice['payment_terms'] ?? '0');
$dueDate                = (string)($invoice['due_date'] ?? $invoiceDate);
$billTo                 = (string)($invoice['bill_to'] ?? '');
$shipTo                 = (string)($invoice['ship_to'] ?? '');
$notes                  = (string)($invoice['notes'] ?? '');

// ===== Angka ringkasan =====
$subtotal     = (float)($invoice['subtotal'] ?? 0);
$discount     = (float)($invoice['discount'] ?? 0);
$tax          = (float)($invoice['tax'] ?? 0);
$shipping     = (float)($invoice['shipping'] ?? 0);
$total        = (float)($invoice['total'] ?? 0);
$amountPaid   = (float)($invoice['amount_paid'] ?? 0);

// Sesuai permintaan: Total Keseluruhan = Total (mirror)
$balanceDue   = $total;

// ===== Logo penyimpanan lokal (deteksi file) =====
$logoBaseName        = preg_replace('/[^A-Za-z0-9_-]/', '_', $storedBrandName) ?: ('invoice_logo_' . $invoiceId);
$allowedLogoExts     = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];
$storedLogoPublicPath= '';
$logoDirAbs          = __DIR__ . '/invoices/logos';

// First, check if session has a valid logo path
$sessionLogoPath = isset($_SESSION['brand_logo_path']) ? trim((string) $_SESSION['brand_logo_path']) : '';
$brandLogoActionSession = $_SESSION['brand_logo_action'] ?? '';
$defaultLogoPath = 'assets/images/logo-kenza-store.png';
$defaultLogoFullPath = __DIR__ . '/' . $defaultLogoPath;

if ($sessionLogoPath !== '' && is_file(__DIR__ . '/' . $sessionLogoPath)) {
    $storedLogoPublicPath = $sessionLogoPath;
} elseif ($brandLogoActionSession !== 'removed') {
    // Detect based on brand name or invoice number
    $invoiceSlugCand = preg_replace('/[^A-Za-z0-9_-]/', '_', $generatedInvoiceNumber);
    $slugCandidates = array_filter([$logoBaseName, $invoiceSlugCand]);
    foreach ($slugCandidates as $candSlug) {
        foreach ($allowedLogoExts as $ext) {
            $candidate = $logoDirAbs . '/' . $candSlug . '.' . $ext;
            if (is_file($candidate)) {
                $storedLogoPublicPath = 'invoices/logos/' . $candSlug . '.' . $ext;
                break 2;
            }
        }
    }

    // If still no logo, fallback to default Kenza Store logo
    if ($storedLogoPublicPath === '' && is_file($defaultLogoFullPath) && is_readable($defaultLogoFullPath)) {
        $storedLogoPublicPath = $defaultLogoPath;
    }
}

$hasStoredLogo       = $storedLogoPublicPath !== '';
$brandLogoActionValue= $hasStoredLogo ? 'keep' : '';

// Simpan prefill ke session
$_SESSION['brand_name_prefill'] = $storedBrandName;
$_SESSION['store_info_prefill'] = $storedStoreInfo;
if ($hasStoredLogo) {
    $_SESSION['brand_logo_path'] = $storedLogoPublicPath;
}

// ===== Siapkan data JSON untuk JS prefill =====
$invoiceData = [
    'invoice_number' => $generatedInvoiceNumber,
    'invoice_date'   => $invoiceDate,
    'payment_terms'  => $paymentTerms,
    'due_date'       => $dueDate,
    'bill_to'        => $billTo,
    'ship_to'        => $shipTo,
    'notes'          => $notes,
    'store_info'     => $storedStoreInfo,
    'brand_name'     => $storedBrandName,
    'subtotal'       => $subtotal,
    'discount'       => $discount,
    'tax'            => $tax,
    'shipping'       => $shipping,
    'total'          => $total,
    'amount_paid'    => $amountPaid,
    // mirror sesuai requirement
    'balance_due'    => $balanceDue,
    'items'          => []
];

foreach ($invoiceItems as $item) {
    $invoiceData['items'][] = [
        'description' => $item['description'] ?? '',
        'quantity'    => (float)($item['quantity'] ?? 0),
        'rate'        => (float)($item['rate'] ?? 0),
        'amount'      => isset($item['amount'])
            ? (float)$item['amount']
            : (float)($item['quantity'] ?? 0) * (float)($item['rate'] ?? 0),
    ];
}

$invoiceDataJson = json_encode($invoiceData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Update Invoice - Download Functionality</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --navy:#0f254a; --navy-soft:#1e3a8a; --muted:#64748b; --border:#d8dee9; --surface:#f8fafc; --success:#0f9d58; }
        body{background:#f4f6fb;font-family:'Poppins','Segoe UI',sans-serif;color:#1f2937;padding:32px 24px 64px}
        .test-banner{background:#28a745;color:white;padding:10px;text-align:center;margin-bottom:20px;border-radius:8px}
        .navbar{display:flex;align-items:center;justify-content:space-between;border-radius:16px;padding:12px 24px;background:#fff;box-shadow:0 14px 40px rgba(15,23,42,.08);margin-bottom:28px}
        .navbar-brand{font-weight:700;letter-spacing:.08em;color:var(--navy)!important;text-transform:uppercase}
        .navbar-nav{display:flex;align-items:center;gap:18px;margin:0}
        .navbar-nav .nav-item{list-style:none}
        .navbar .nav-link{font-weight:500;color:#475569!important}
        #darkModeToggle{border-radius:50%;border:1px solid rgba(15,37,74,.12);width:44px;height:44px;display:inline-flex;align-items:center;justify-content:center;color:var(--navy);background:#fff;transition:transform .2s ease}
        #darkModeToggle:hover{transform:translateY(-1px)}
        .invoice-wrapper{max-width:1140px;margin:0 auto}
        .invoice-card{background:#fff;border-radius:24px;box-shadow:0 24px 60px rgba(15,23,42,.12);padding:40px 44px}
        .invoice-header{display:flex;flex-wrap:wrap;gap:32px;justify-content:space-between}
        .header-left{flex:1 1 340px;display:flex;flex-direction:column;gap:20px}
        .brand-box{display:flex;flex-direction:column;gap:16px;align-items:stretch;padding:28px 24px;border-radius:18px;border:1px dashed rgba(15,37,74,.25);background:var(--surface)}
        .brand-logo-shell{position:relative;min-height:140px;border-radius:14px;border:1px dashed rgba(15,37,74,.2);background:rgba(15,37,74,.04);display:flex;align-items:center;justify-content:center;overflow:hidden}
        .brand-logo-preview{max-width:100%;max-height:140px;object-fit:contain}
        .brand-logo-placeholder{font-size:.85rem;font-weight:600;color:var(--muted);letter-spacing:.08em;text-transform:uppercase}
        .brand-logo-controls{display:flex;gap:10px;justify-content:center}
        .brand-name-field{background:transparent;border:none;width:100%;text-align:center;font-size:26px;font-weight:700;letter-spacing:.24em;text-transform:uppercase;color:var(--navy);resize:none}
        .brand-name-field:focus{outline:none;border:none;box-shadow:none}
        .is-hidden{display:none!important}
        .info-card{display:flex;flex-direction:column;gap:8px}
        .info-card textarea{min-height:120px}
        .invoice-title{font-size:32px;font-weight:700;color:var(--navy);text-align:right;margin:0}
        .header-right{flex:1 1 320px;display:flex;flex-direction:column;gap:20px;align-items:flex-end}
        .invoice-number{display:flex;gap:12px;align-items:center}
        .invoice-number .prefix{display:inline-flex;align-items:center;justify-content:center;padding:8px 14px;border-radius:12px;background:rgba(30,58,138,.12);color:var(--navy-soft);font-weight:600;letter-spacing:.04em}
        .invoice-number input{width:200px}
        .meta-grid{width:100%;display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px}
        .meta-field{display:flex;flex-direction:column;gap:6px}
        .meta-field input{text-align:right;font-weight:600}
        label{font-size:.8rem;font-weight:600;color:var(--muted);letter-spacing:.08em;text-transform:uppercase;margin-bottom:6px}
        label[contenteditable="true"]{cursor:text}
        input[type="text"],input[type="number"],input[type="date"],textarea{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:12px 14px;font-size:.95rem;color:#0f172a;width:100%;transition:border-color .2s ease,box-shadow .2s ease}
        input[type="text"]:focus,input[type="number"]:focus,input[type="date"]:focus,textarea:focus{border-color:var(--navy-soft);box-shadow:0 0 0 3px rgba(30,58,138,.12);outline:none}
        textarea{resize:vertical}
        .address-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:24px;margin-top:24px}
        .address-card{display:flex;flex-direction:column;gap:8px}
        .items-section{margin-top:36px}
        .items-table{width:100%;border-collapse:separate;border-spacing:0;border-radius:16px;overflow:hidden;background:#fff;box-shadow:inset 0 0 0 1px #e2e8f0}
        .items-table thead th{background:var(--navy);color:#fff;padding:16px 20px;font-size:.9rem;font-weight:600;letter-spacing:.05em;border:none}
        .items-table tbody td{padding:12px 16px;border-bottom:1px solid #e2e8f0;vertical-align:top;background:#f9fbff}
        .items-table tbody tr:last-child td{border-bottom:none}
        .items-table textarea{min-height:68px}
        .items-table .control{max-width:120px;margin-left:auto}
        .items-table td.action{width:120px;text-align:right}
        .remove-item{background:#fee2e2;border:none;color:#b91c1c;padding:8px 14px;border-radius:12px;font-weight:600;cursor:pointer;transition:background .2s ease}
        .remove-item:hover{background:#fecaca}
        .add-item-btn{display:inline-flex;align-items:center;gap:8px;margin-top:18px;background:rgba(15,157,88,.12);color:var(--success);border:none;padding:10px 20px;border-radius:999px;font-weight:600;cursor:pointer;transition:background .2s ease,transform .2s ease}
        .add-item-btn i{font-size:.85rem}
        .add-item-btn:hover{background:rgba(15,157,88,.18);transform:translateY(-1px)}
        .notes-summary-row{display:flex;flex-wrap:wrap;gap:28px;margin-top:36px}
        .notes-card{flex:1 1 340px;display:flex;flex-direction:column;gap:8px}
        .notes-card textarea{min-height:180px}
        .summary-card{flex:1 1 280px;background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:24px 24px 28px;display:flex;flex-direction:column;gap:14px}
        .summary-row{display:flex;align-items:center;justify-content:space-between;gap:16px}
        .summary-row span{font-weight:600;color:#0f172a}
        .summary-row input{text-align:right;font-weight:600;background:#fff}
        .summary-row.accent span{color:var(--navy-soft)}
        .summary-divider{height:1px;background:#e2e8f0;margin:8px 0}
        .summary-row.total span{font-size:1rem;color:var(--navy)}
        .summary-row.total input{font-size:1rem}
        .summary-row.balance span{color:var(--navy);font-size:1.05rem}
        .summary-row.balance input{font-size:1.05rem;color:var(--navy)}
        .invoice-actions{margin-top:40px;display:flex;justify-content:flex-end}
        .generate-btn{background:var(--navy-soft);color:#fff;border:none;padding:14px 28px;border-radius:16px;font-size:1rem;font-weight:600;letter-spacing:.06em;cursor:pointer;transition:transform .2s ease,box-shadow .2s ease}
        .generate-btn:hover{transform:translateY(-1px);box-shadow:0 12px 30px rgba(30,58,138,.22)}
        @media (max-width: 768px){
            body{padding:24px 16px 48px}
            .invoice-card{padding:28px 24px}
            .header-right{align-items:stretch}
            .invoice-title{text-align:left}
            .invoice-number input{width:100%}
            .summary-card{flex:1 1 100%}
            .summary-row{flex-direction:column;align-items:flex-start}
            .summary-row input{width:100%;text-align:left}
            .invoice-actions{justify-content:stretch}
            .generate-btn{width:100%}
        }
    </style>
</head>
<body>
    <div class="test-banner">
        <strong>TEST MODE:</strong> Testing Download Button Functionality - Invoice #<?php echo escape($generatedInvoiceNumber); ?>
    </div>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php">Invoice-System (TEST)</a>
        <div class="container-fluid collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">My Invoices</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Settings</a>
                </li>
                <li class="nav-item">
                    <span class="nav-link">
                        Test User
                    </span>

                </li>
                <li class="nav-item">
                    <a class="nav-link" href="view_logs.php">Activity Logs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
                <li class="nav-item">
                    <button class="btn btn-outline-secondary ml-3" id="darkModeToggle">
                        <i id="darkModeIcon" class="fas fa-moon"></i>
                    </button>
                </li>



            </ul>
        </div>
    </nav>

    <main class="invoice-wrapper">
        <form id="invoiceForm" method="POST" action="generate_invoice.php" class="invoice-card" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo (int)$userId; ?>">

            <div class="invoice-header">
                <div class="header-left">
                    <div class="brand-box<?php echo $hasStoredLogo ? ' has-logo' : '' ?>" id="brandBox">
                        <div class="brand-logo-shell">
                            <img src="<?php echo $hasStoredLogo ? escape($storedLogoPublicPath) : '' ?>" alt="Brand logo preview" id="brandLogoPreview" class="brand-logo-preview<?php echo $hasStoredLogo ? '' : ' is-hidden' ?>">
                            <div class="brand-logo-placeholder<?php echo $hasStoredLogo ? ' is-hidden' : '' ?>" id="brandLogoPlaceholder">No logo selected</div>
                        </div>
                        <div class="brand-logo-controls">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectLogoBtn">Choose Logo</button>
                            <button type="button" class="btn btn-sm btn-outline-danger<?php echo $hasStoredLogo ? '' : ' is-hidden' ?>" id="removeLogoBtn">Remove</button>
                        </div>
                        <input type="file" id="brandLogo" name="brand_logo" accept="image/*" hidden>
                        <input type="hidden" id="brandLogoAction" name="brand_logo_action" value="<?php echo escape($brandLogoActionValue); ?>">
                        <textarea id="brand_name" name="brand_name" class="brand-name-field"><?php echo escape($storedBrandName); ?></textarea>
                    </div>
                    <div class="info-card">
                        <label for="store_info">From</label>
                        <textarea id="store_info" name="store_info"><?php echo escape($storedStoreInfo); ?></textarea>
                    </div>
                </div>

                <div class="header-right">
                    <h2 class="invoice-title">Bukti Pembelian</h2>
                    <div class="invoice-number">
                        <span class="prefix">#</span>
                        <input type="text" name="invoice_number" value="<?php echo escape($generatedInvoiceNumber); ?>" required>
                    </div>
                    <div class="meta-grid">
                        <div class="meta-field">
                            <label for="invoice_date">Date</label>
                            <input type="date" id="invoice_date" name="invoice_date" value="<?php echo escape($invoiceDate); ?>" required>
                        </div>
                        <div class="meta-field">
                            <label for="payment_terms">Payment Terms</label>
                            <input type="number" id="payment_terms" name="payment_terms" value="<?php echo escape($paymentTerms); ?>" required>
                        </div>
                        <div class="meta-field">
                            <label for="due_date">Due Date</label>
                            <input type="date" id="due_date" name="due_date" value="<?php echo escape($dueDate); ?>" required>
                        </div>
                        <div class="meta-field">
                            <label for="header_total">Total Keseluruhan</label>
                            <input type="text" id="header_total" value="<?php echo number_format($total, 2, '.', ''); ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="address-grid">
                <div class="address-card">
                    <label for="bill_to">Kepada</label>
                    <textarea id="bill_to" name="bill_to" placeholder="Nama penerima, perusahaan, alamat" required><?php echo escape($billTo); ?></textarea>
                </div>
                <div class="address-card">
                    <label for="ship_to">Dikirim Ke</label>
                    <textarea id="ship_to" name="ship_to" placeholder="Alamat pengiriman" required><?php echo escape($shipTo); ?></textarea>
                </div>
            </div>

            <div class="items-section">
                <label>Item</label>
                <table class="items-table" id="invoiceItems">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Rate</th>
                            <th>Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><textarea name="items[0][description]" placeholder="Deskripsi item" required></textarea></td>
                            <td><input type="number" class="quantity control" name="items[0][quantity]" step="any" min="0" placeholder="Qty" required></td>
                            <td><input type="number" class="rate control" name="items[0][rate]" step="any" min="0" placeholder="Rate" required></td>
                            <td><input type="number" class="amount control" name="items[0][amount]" step="any" min="0" value="0.00" readonly></td>
                            <td class="action"><button type="button" class="remove-item">Remove</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" id="addItem" class="add-item-btn"><i class="fas fa-plus"></i> Line Item</button>
            </div>

            <div class="notes-summary-row">
                <div class="notes-card">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes"><?php echo escape($notes); ?></textarea>
                </div>
                <div class="summary-card">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <input type="text" id="subtotal" name="subtotal" value="<?php echo number_format($subtotal, 2, '.', ''); ?>" readonly>
                    </div>
                    <div class="summary-row accent">
                        <span>+ Discount</span>
                        <input type="number" id="discount" name="discount" value="<?php echo formatNumberInput($discount, 2); ?>" step="any" min="0">
                    </div>
                    <div class="summary-row accent">
                        <span>+ Tax (%)</span>
                        <input type="number" id="tax" name="tax" value="<?php echo formatNumberInput($tax, 2); ?>" step="any" min="0">
                    </div>
                    <div class="summary-row accent">
                        <span>+ Shipping</span>
                        <input type="number" id="shipping" name="shipping" value="<?php echo formatNumberInput($shipping, 2); ?>" step="any" min="0">
                    </div>
                    <div class="summary-divider"></div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <input type="text" id="total" name="total" value="<?php echo number_format($total, 2, '.', ''); ?>" readonly>
                    </div>
                    <div class="summary-row">
                        <span>Amount Paid</span>
                        <input type="number" id="amount_paid" name="amount_paid" value="<?php echo formatNumberInput($amountPaid, 2); ?>" step="any" min="0">
                    </div>
                    <div class="summary-row balance">
                        <span>Total Keseluruhan</span>
                        <input type="text" id="balance_due" name="balance_due" value="<?php echo number_format($balanceDue, 2, '.', ''); ?>" readonly>
                    </div>
                </div>
            </div>

            <div class="invoice-actions">
                <button type="button" id="generatePdfBtn" class="generate-btn">Download (Test New Functionality)</button>
            </div>
        </form>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function parseNumber(value) { return parseFloat(value) || 0; }

        function calculateTotals() {
            let subtotal = 0;
            $('.amount').each(function () {
                subtotal += parseNumber($(this).val());
            });
            $('#subtotal').val(subtotal.toFixed(2));

            const discount = parseNumber($('#discount').val());
            const taxRate  = parseNumber($('#tax').val());
            const shipping = parseNumber($('#shipping').val());

            const taxAmount = subtotal * (taxRate / 100);
            const total     = Math.max(subtotal - discount + taxAmount + shipping, 0);

            // Mirror: Total, Header Total, dan Total Keseluruhan sama
            $('#total').val(total.toFixed(2));
            $('#header_total').val(total.toFixed(2));
            $('#balance_due').val(total.toFixed(2));

            // Amount paid tidak mempengaruhi "Total Keseluruhan" sesuai permintaan
        }

        // Prefill data dari PHP
        (function () {
            const invoiceData = <?php echo $invoiceDataJson ?: 'null'; ?>;
            if (!invoiceData) return;

            const setValue = (selector, value) => {
                const el = document.querySelector(selector);
                if (el) el.value = (value ?? '');
            };

            setValue('input[name="invoice_number"]', invoiceData.invoice_number || '');
            setValue('#invoice_date', invoiceData.invoice_date || '');
            setValue('#payment_terms', invoiceData.payment_terms || '');
            setValue('#due_date', invoiceData.due_date || '');
            setValue('#header_total', Number(invoiceData.total || 0).toFixed(2));

            setValue('#bill_to', invoiceData.bill_to || '');
            setValue('#ship_to', invoiceData.ship_to || '');
            setValue('#store_info', invoiceData.store_info || '');
            setValue('#brand_name', invoiceData.brand_name || '');
            setValue('#notes', invoiceData.notes || '');

            setValue('#subtotal', Number(invoiceData.subtotal || 0).toFixed(2));
            setValue('#discount', invoiceData.discount || 0);
            setValue('#tax', invoiceData.tax || 0);
            setValue('#shipping', invoiceData.shipping || 0);
            setValue('#total', Number(invoiceData.total || 0).toFixed(2));
            setValue('#amount_paid', invoiceData.amount_paid || 0);

            // Mirror "Total Keseluruhan" dengan Total
            setValue('#balance_due', Number(invoiceData.total || 0).toFixed(2));

            const itemsBody = document.querySelector('#invoiceItems tbody');
            if (itemsBody) {
                itemsBody.innerHTML = '';
                (invoiceData.items || []).forEach((item, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML =
                        `<td><textarea name="items[${index}][description]" required></textarea></td>` +
                        `<td><input type="number" class="quantity control" name="items[${index}][quantity]" step="any" min="0" required></td>` +
                        `<td><input type="number" class="rate control" name="items[${index}][rate]" step="any" min="0" required></td>` +
                        `<td><input type="number" class="amount control" name="items[${index}][amount]" step="any" min="0" readonly></td>` +
                        `<td class="action"><button type="button" class="remove-item">Remove</button></td>`;
                    itemsBody.appendChild(row);

                    const description = row.querySelector('textarea');
                    const quantity    = row.querySelector('.quantity');
                    const rate        = row.querySelector('.rate');
                    const amount      = row.querySelector('.amount');

                    if (description) description.
