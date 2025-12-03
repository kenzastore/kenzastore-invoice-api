<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db_connect.php';
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/* =========================
   Helpers
   ========================= */
function cleanNumber($value): float
{
    if ($value === null || $value === '') return 0.0;
    if (is_numeric($value)) return (float)$value;
    $normalized = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', (string)$value));
    return $normalized === '' ? 0.0 : (float)$normalized;
}

function formatCurrency(float $value): string
{
    return 'IDR ' . number_format($value, 2, '.', ',');
}

function formatQuantity($value): string
{
    $number = cleanNumber($value);
    $formatted = number_format($number, 2, '.', ',');
    $formatted = rtrim(rtrim($formatted, '0'), '.');
    return $formatted === '' ? '0' : $formatted;
}

function formatText(?string $value): string
{
    if ($value === null) return '';
    return nl2br(htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8'));
}

function formatDateLabel(?string $value): string
{
    if (!$value) return '';
    $timestamp = strtotime($value);
    if ($timestamp === false) return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    return date('M d, Y', $timestamp);
}

function detectMimeType(string $path): string
{
    if (function_exists('mime_content_type')) {
        $mime = @mime_content_type($path);
        if ($mime !== false && $mime !== '') return $mime;
    }
    $finfo = @finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo !== false) {
        $mime = @finfo_file($finfo, $path);
        @finfo_close($finfo);
        if ($mime !== false && $mime !== '') return $mime;
    }
    return '';
}

/* =========================
   Vars
   ========================= */
$userId = (int) $_SESSION['user_id'];

$invoiceData = [];
$items = [];
$brandName = 'KENZA STORE';
$storeInfo = "KZ-Store\n62 895 3376 74116\nKendangsari Gg 4/45 A-7, Surabaya 60292";
$amountPaid = 0.0;
$balanceDue = 0.0;
$pdfFileName = '';
$logoDataUri = '';
$logoMimeType = '';
$logoBinary = null;
$logoStoragePath = '';
$logoSlug = '';
$logoFileExtension = '';
$logoAction = '';

/* =========================
   Build Data
   ========================= */
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['invoice_number']) || empty($_POST['items']) || !is_array($_POST['items'])) {
            throw new RuntimeException('Invoice data incomplete.');
        }

        $brandName = trim($_POST['brand_name'] ?? '') ?: 'KENZA STORE';
        $storeInfo = trim($_POST['store_info'] ?? $storeInfo);
        $_SESSION['brand_name_prefill'] = $brandName;

        $sessionLogoPath = isset($_SESSION['brand_logo_path']) ? trim((string) $_SESSION['brand_logo_path']) : '';
        $sessionLogoPath = $sessionLogoPath !== '' ? str_replace('\\', '/', $sessionLogoPath) : '';
        $sessionLogoMime = (string) ($_SESSION['brand_logo_mime'] ?? '');
        $logoAction = $_POST['brand_logo_action'] ?? '';

        // Upload logo baru
        if (!empty($_FILES['brand_logo']) && is_uploaded_file((string) ($_FILES['brand_logo']['tmp_name'] ?? ''))) {
            $logoFile = $_FILES['brand_logo'];
            if (($logoFile['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Logo upload failed.');
            }
            $fileSize = (int) ($logoFile['size'] ?? 0);
            if ($fileSize <= 0 || $fileSize > 2 * 1024 * 1024) {
                throw new RuntimeException('Logo must be greater than 0 bytes and under 2MB.');
            }

            $tmpPath = (string) $logoFile['tmp_name'];
            $mimeType = detectMimeType($tmpPath);
            $allowedMime = ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'image/svg+xml'];
            $extensionMap = [
                'image/png' => 'png',
                'image/jpeg' => 'jpg',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'image/svg+xml' => 'svg',
            ];
            if (!in_array($mimeType, $allowedMime, true)) {
                throw new RuntimeException('Unsupported logo format. Please upload PNG, JPG, GIF, SVG, or WEBP.');
            }

            $fileContents = file_get_contents($tmpPath);
            if ($fileContents === false) throw new RuntimeException('Unable to read uploaded logo.');

            $logoBinary = $fileContents;
            $logoMimeType = $mimeType;
            $logoFileExtension = $extensionMap[$mimeType] ?? '';
            $logoDataUri = 'data:' . $mimeType . ';base64,' . base64_encode($fileContents);
            $logoAction = 'uploaded';

        // Pertahankan logo sebelumnya
        } elseif ($logoAction === 'keep') {
            if ($sessionLogoPath !== '') {
                $existingLogoPath = __DIR__ . '/' . $sessionLogoPath;
                if (is_file($existingLogoPath) && is_readable($existingLogoPath)) {
                    $fileContents = file_get_contents($existingLogoPath);
                    if ($fileContents !== false) {
                        $logoBinary = $fileContents;
                        $logoFileExtension = strtolower((string) pathinfo($existingLogoPath, PATHINFO_EXTENSION)) ?: 'png';
                        $logoMimeType = detectMimeType($existingLogoPath) ?: ($sessionLogoMime ?: ($logoFileExtension === 'svg' ? 'image/svg+xml' : 'image/' . $logoFileExtension));
                        if ($logoMimeType !== '') {
                            $logoDataUri = 'data:' . $logoMimeType . ';base64,' . base64_encode($fileContents);
                        }
                    } else {
                        $logoAction = 'removed';
                    }
                } else {
                    $logoAction = 'removed';
                }
            } else {
                $logoAction = '';
            }
        }

        // Hapus logo
        if ($logoAction === 'removed') {
            $logoDataUri = '';
            $logoBinary = null;
            $logoMimeType = '';
            $logoFileExtension = '';
            unset($_SESSION['brand_logo_path'], $_SESSION['brand_logo_mime']);
        }

        // Data invoice
        $invoiceData = [
            'user_id' => $userId,
            'invoice_number' => trim($_POST['invoice_number']),
            'invoice_date' => $_POST['invoice_date'] ?? date('Y-m-d'),
            'payment_terms' => trim($_POST['payment_terms'] ?? '0'),
            'due_date' => $_POST['due_date'] ?? ($_POST['invoice_date'] ?? date('Y-m-d')),
            'bill_to' => trim($_POST['bill_to'] ?? ''),
            'ship_to' => trim($_POST['ship_to'] ?? ''),
            'notes' => $_POST['notes'] ?? '',
            'subtotal' => cleanNumber($_POST['subtotal'] ?? 0),
            'discount' => cleanNumber($_POST['discount'] ?? 0),
            'tax' => cleanNumber($_POST['tax'] ?? 0),
            'shipping' => cleanNumber($_POST['shipping'] ?? 0),
            'total' => cleanNumber($_POST['total'] ?? 0),
        ];

        $amountPaid = cleanNumber($_POST['amount_paid'] ?? 0);
        $balanceDue = max(cleanNumber($_POST['balance_due'] ?? ($invoiceData['total'] - $amountPaid)), 0);
        $taxPercent = $invoiceData['tax'];
        $taxAmount = $invoiceData['subtotal'] * ($taxPercent / 100);
        $recomputedTotal = max($invoiceData['subtotal'] - $invoiceData['discount'] + $taxAmount + $invoiceData['shipping'], 0);

        if ($invoiceData['total'] <= 0) {
            $invoiceData['total'] = $recomputedTotal;
        }

        $pdfFileName = 'invoices/' . $invoiceData['invoice_number'] . '.pdf';
        $invoiceData['pdf_path'] = $pdfFileName;

        $logoSlug = preg_replace('/[^A-Za-z0-9_-]/', '_', $invoiceData['invoice_number']) ?: ('invoice_logo_' . time());

        // Simpan ke DB
        $pdo->beginTransaction();
        $insertInvoice = $pdo->prepare('
            INSERT INTO invoices (
                user_id, invoice_number, invoice_date, payment_terms, due_date,
                bill_to, ship_to, notes, subtotal, discount, tax, shipping, total, pdf_path
            ) VALUES (
                :user_id, :invoice_number, :invoice_date, :payment_terms, :due_date,
                :bill_to, :ship_to, :notes, :subtotal, :discount, :tax, :shipping, :total, :pdf_path
            )
        ');
        $insertInvoice->execute([
            ':user_id' => $invoiceData['user_id'],
            ':invoice_number' => $invoiceData['invoice_number'],
            ':invoice_date' => $invoiceData['invoice_date'],
            ':payment_terms' => $invoiceData['payment_terms'],
            ':due_date' => $invoiceData['due_date'],
            ':bill_to' => $invoiceData['bill_to'],
            ':ship_to' => $invoiceData['ship_to'],
            ':notes' => $invoiceData['notes'],
            ':subtotal' => $invoiceData['subtotal'],
            ':discount' => $invoiceData['discount'],
            ':tax' => $invoiceData['tax'],
            ':shipping' => $invoiceData['shipping'],
            ':total' => $invoiceData['total'],
            ':pdf_path' => $invoiceData['pdf_path'],
        ]);
        $invoiceId = (int) $pdo->lastInsertId();
        $invoiceData['id'] = $invoiceId;

        $insertItem = $pdo->prepare('
            INSERT INTO invoice_items (invoice_id, description, quantity, rate, amount)
            VALUES (:invoice_id, :description, :quantity, :rate, :amount)
        ');
        foreach ($_POST['items'] as $itemPayload) {
            $description = trim($itemPayload['description'] ?? '');
            if ($description === '') continue;
            $quantity = cleanNumber($itemPayload['quantity'] ?? 0);
            $rate = cleanNumber($itemPayload['rate'] ?? 0);
            $amount = cleanNumber($itemPayload['amount'] ?? ($quantity * $rate));

            $items[] = [
                'description' => $description,
                'quantity' => $quantity,
                'rate' => $rate,
                'amount' => $amount,
            ];
            $insertItem->execute([
                ':invoice_id' => $invoiceId,
                ':description' => $description,
                ':quantity' => $quantity,
                ':rate' => $rate,
                ':amount' => $amount,
            ]);
        }

        if (function_exists('logAction')) {
            logAction($pdo, $userId, 'Created Invoice', $invoiceId, 'Invoice Number: ' . $invoiceData['invoice_number']);
        }
        $pdo->commit();

    } elseif (isset($_GET['id'])) {
        // Ambil dari DB
        $invoiceId = (int) $_GET['id'];
        $invoiceStmt = $pdo->prepare('SELECT * FROM invoices WHERE id = :id AND user_id = :user_id');
        $invoiceStmt->execute([':id' => $invoiceId, ':user_id' => $userId]);
        $invoice = $invoiceStmt->fetch(PDO::FETCH_ASSOC);
        if (!$invoice) throw new RuntimeException('Invoice not found.');

        $invoiceData = $invoice;
        $pdfFileName = $invoice['pdf_path'] ?? ('invoices/' . $invoice['invoice_number'] . '.pdf');

        $itemsStmt = $pdo->prepare('SELECT description, quantity, rate, amount FROM invoice_items WHERE invoice_id = :invoice_id');
        $itemsStmt->execute([':invoice_id' => $invoiceId]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $brandName = ($invoice['brand_name'] ?? '') !== '' ? $invoice['brand_name'] : $brandName;
        $storeInfo = ($invoice['store_info'] ?? '') !== '' ? $invoice['store_info'] : $storeInfo;

        $amountPaid = isset($invoice['amount_paid']) ? cleanNumber($invoice['amount_paid']) : 0.0;
        $balanceDue = isset($invoice['balance_due']) ? cleanNumber($invoice['balance_due']) : max(cleanNumber($invoice['total']) - $amountPaid, 0);

        $logoSlug = preg_replace('/[^A-Za-z0-9_-]/', '_', $invoice['invoice_number'] ?? '') ?: ('invoice_logo_' . $invoiceId);

        // Ambil logo dari storage (jika ada)
        $logosDir = 'invoices/logos';
        if (is_dir($logosDir)) {
            foreach (['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'] as $extension) {
                $candidate = $logosDir . '/' . $logoSlug . '.' . $extension;
                if (!is_file($candidate)) continue;
                $fileContents = file_get_contents($candidate);
                if ($fileContents === false) continue;
                $mimeType = detectMimeType($candidate) ?: ($extension === 'svg' ? 'image/svg+xml' : 'image/' . $extension);
                $logoDataUri = 'data:' . $mimeType . ';base64,' . base64_encode($fileContents);
                $logoFileExtension = $extension;
                break;
            }
        }
    } else {
        throw new RuntimeException('Invalid request.');
    }
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die($exception->getMessage());
}

/* =========================
   Persist logo upload/removal
   ========================= */
if (!is_dir('invoices') && !mkdir('invoices', 0777, true) && !is_dir('invoices')) {
    die('Unable to create invoices directory.');
}

if ($logoBinary !== null && $logoFileExtension !== '') {
    $logosDir = 'invoices/logos';
    if (!is_dir($logosDir) && !mkdir($logosDir, 0777, true) && !is_dir($logosDir)) {
        die('Unable to prepare logo directory.');
    }
    $logoStoragePath = $logosDir . '/' . $logoSlug . '.' . $logoFileExtension;

    // Hapus varian lain dengan ekstensi berbeda
    foreach (['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'] as $ext) {
        if ($ext === $logoFileExtension) continue;
        $candidate = $logosDir . '/' . $logoSlug . '.' . $ext;
        if (is_file($candidate)) @unlink($candidate);
    }

    if (file_put_contents($logoStoragePath, $logoBinary) === false) {
        die('Unable to store uploaded logo.');
    }
    $_SESSION['brand_logo_path'] = $logoStoragePath;
    if ($logoMimeType !== '') $_SESSION['brand_logo_mime'] = $logoMimeType;
    else unset($_SESSION['brand_logo_mime']);

} elseif ($logoAction === 'removed' && $logoSlug !== '') {
    $logosDir = 'invoices/logos';
    if (is_dir($logosDir)) {
        foreach (['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'] as $ext) {
            $candidate = $logosDir . '/' . $logoSlug . '.' . $ext;
            if (is_file($candidate)) @unlink($candidate);
        }
    }
}

/* =========================
   Compute view vars
   ========================= */
$total = cleanNumber($invoiceData['total'] ?? 0);
$subtotal = cleanNumber($invoiceData['subtotal'] ?? 0);
$discount = cleanNumber($invoiceData['discount'] ?? 0);
$shipping = cleanNumber($invoiceData['shipping'] ?? 0);
$taxPercent = cleanNumber($invoiceData['tax'] ?? 0);
$taxAmount = $subtotal * ($taxPercent / 100);

if ($balanceDue <= 0) $balanceDue = max($total - $amountPaid, 0);

$invoiceNumberDisplay = htmlspecialchars($invoiceData['invoice_number'] ?? 'INVOICE', ENT_QUOTES, 'UTF-8');
$invoiceDateLabel = formatDateLabel($invoiceData['invoice_date'] ?? '');
$dueDateLabel = formatDateLabel($invoiceData['due_date'] ?? '');
$paymentTermsDisplay = htmlspecialchars($invoiceData['payment_terms'] ?? '0', ENT_QUOTES, 'UTF-8');

$brandDisplay = strtoupper(htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8'));
$storeInfoHtml = formatText($storeInfo);
$billToHtml = formatText($invoiceData['bill_to'] ?? '');
$shipToHtml = formatText($invoiceData['ship_to'] ?? '');

if ($billToHtml === '') $billToHtml = 'YDSF';
if ($shipToHtml === '') $shipToHtml = 'Kertajaya 8C No 17';

$subtotalDisplay = formatCurrency($subtotal > 0 ? $subtotal : $total);
$totalDisplay = formatCurrency($total > 0 ? $total : $subtotal);
$balanceDisplay = $balanceDue > 0 ? formatCurrency($balanceDue) : $totalDisplay;

// Items rows
$itemsRows = '';
if (count($items) === 0) {
    $itemsRows .= '
        <tr class="item-row">
            <td class="desc">&nbsp;</td><td class="qty">&nbsp;</td><td class="rate">&nbsp;</td><td class="amount">&nbsp;</td>
        </tr>';
} else {
    foreach ($items as $item) {
        $description = formatText($item['description'] ?? '');
        $quantity = formatQuantity($item['quantity'] ?? 0);
        $rate = formatCurrency(cleanNumber($item['rate'] ?? 0));
        $amountFormatted = formatCurrency(cleanNumber($item['amount'] ?? 0));
        $itemsRows .= '
            <tr class="item-row">
                <td class="desc">'.$description.'</td>
                <td class="qty">'.$quantity.'</td>
                <td class="rate currency">'.$rate.'</td>
                <td class="amount currency">'.$amountFormatted.'</td>
            </tr>';
    }
}

// Notes
$rawNotes = trim((string) ($invoiceData['notes'] ?? ''));
$defaultNotes = [
    '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD',
    '*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).',
    '*)Rate PP hari ini Per $1 USD Rp.17,700.00',
];
$notesLinesHtml = '';
if ($rawNotes === '') {
    foreach ($defaultNotes as $noteLine) {
        $notesLinesHtml .= '<p class="note-line">' . htmlspecialchars($noteLine, ENT_QUOTES, 'UTF-8') . '</p>';
    }
} else {
    foreach (preg_split('/\r\n|\r|\n/', $rawNotes) as $seg) {
        $seg = trim($seg);
        if ($seg === '') continue;
        $notesLinesHtml .= '<p class="note-line">' . htmlspecialchars($seg, ENT_QUOTES, 'UTF-8') . '</p>';
    }
}

/* =========================
   HTML & CSS
   ========================= */
$logoStateClass = $logoDataUri !== '' ? ' has-image' : '';
$logoImageTag = $logoDataUri !== '' ? '<img src="' . $logoDataUri . '" alt="' . $brandDisplay . '" class="logo-img">' : '';

$html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{$invoiceNumberDisplay}</title>
<style>
@page { size: A4; margin: 18mm 14mm 20mm 14mm; }
* { box-sizing: border-box; }
body { margin:0; font-family:'Helvetica Neue', Arial, Helvetica, sans-serif; color:#202124; font-size:12.5px; line-height:1.45; background:#ffffff; }
.invoice-page { max-width:760px; margin:0 auto; }

.hdr-table { width:100%; border-collapse:collapse; margin-bottom:18px; }
.hdr-table td { vertical-align:top; }
.hdr-left { padding-right:24px; }
.hdr-right { text-align:right; white-space:nowrap; }

.logo { width:150px; height:42px; border:1px solid #d6d6d6; background:#f4f4f4; border-radius:4px; display:flex; align-items:center; justify-content:center; padding:4px; position:relative; overflow:hidden; }
.logo-img { position:absolute; inset:4px; width:calc(100% - 8px); height:calc(100% - 8px); object-fit:contain; display:none; }
.logo.has-image .logo-img { display:block; }
.logo-text { width:100%; text-align:center; text-transform:uppercase; letter-spacing:.42em; font-weight:600; font-size:11px; color:#616161; }
.logo.has-image .logo-text { display:none; }

.seller-details { margin-top:10px; font-size:12.3px; color:#202124; line-height:1.55; }

.invoice-title { margin:0; font-size:28px; font-weight:600; letter-spacing:.4px; color:#202124; }
.invoice-code { margin:8px 0 0; font-size:12.5px; color:#6b6b6b; letter-spacing:.28em; }

.info-table { width:100%; border-collapse:collapse; margin-bottom:18px; }
.info-table td { vertical-align:top; }
.info-card { padding-right:22px; padding-bottom:12px; border-bottom:1px solid #dedede; }
.info-label { display:block; font-size:11.2px; color:#70757a; letter-spacing:.3px; text-transform:uppercase; font-weight:600; margin-bottom:4px; }
.info-value { font-size:12.6px; color:#202124; font-weight:700; line-height:1.45; }

.meta-wrap { display:inline-block; min-width:220px; }
.meta-table { width:100%; border-collapse:separate; border-spacing:0 6px; margin-left:auto; }
.meta-label { font-size:11.2px; color:#70757a; letter-spacing:.3px; text-transform:uppercase; font-weight:600; padding-right:12px; }
.meta-value { font-size:12.6px; color:#202124; font-weight:500; text-align:right; white-space:nowrap; }
.total-cell { text-align:right; padding-top:4px; }
.total-pill { display:inline-flex; align-items:center; gap:14px; background:#f5f5f5; border-radius:10px; padding:8px 16px; margin-top:6px; font-weight:700; }
.total-pill .label { font-size:12.5px; color:#202124; }
.total-pill .value { font-size:13px; color:#202124; }

.items-section { margin-top:10px; }
.items-table { width:100%; border-collapse:collapse; font-size:12.3px; }
.items-table thead th { background:#3a3a3a; color:#ffffff; font-weight:600; padding:12px 14px; letter-spacing:.3px; text-transform:uppercase; text-align:left; }
.items-table thead th.qty,
.items-table thead th.rate,
.items-table thead th.amount { text-align:right; }
.items-table tbody tr { page-break-inside:avoid; }
.items-table tbody td { border-bottom:1px solid #e2e2e2; padding:13px 14px; color:#202124; }
.items-table tbody td.desc { width:64%; padding-right:28px; }
.items-table tbody td.qty,
.items-table tbody td.rate,
.items-table tbody td.amount { text-align:right; white-space:nowrap; }

.summary-section { margin:16px 0 28px; }
.summary-table { width:100%; border-collapse:collapse; }
.summary-table td { font-size:12.6px; padding:6px 14px 0; }
.summary-table .label { text-align:right; color:#5f6368; font-weight:600; }
.summary-table .value { text-align:right; color:#202124; font-weight:700; }

.notes-section { margin-top:26px; }
.notes-section h2 { margin:0 0 8px; font-size:12px; font-weight:700; letter-spacing:.2px; color:#202124; }
.note-line { margin:0 0 4px; font-size:11.5px; color:#5f6368; }
.note-line:last-child { margin-bottom:0; }
.currency { font-variant-numeric:tabular-nums; white-space:nowrap; }

@media print { body { -webkit-print-color-adjust:exact; print-color-adjust:exact; } }
</style>
</head>
<body>
<div class="invoice-page">

  <table class="hdr-table">
    <tr>
      <td class="hdr-left" style="width:60%;">
        <div class="logo{$logoStateClass}" aria-label="{$brandDisplay}">
          {$logoImageTag}
          <span class="logo-text">{$brandDisplay}</span>
        </div>
        <div class="seller-details">{$storeInfoHtml}</div>
      </td>
      <td class="hdr-right" style="width:40%;">
        <h1 class="invoice-title">Bukti Pembelian</h1>
        <p class="invoice-code"># {$invoiceNumberDisplay}</p>
      </td>
    </tr>
  </table>

  <table class="info-table">
    <tr>
      <td class="info-card" style="width:33%;">
        <span class="info-label">Kepada</span>
        <div class="info-value">{$billToHtml}</div>
      </td>
      <td class="info-card" style="width:33%;">
        <span class="info-label">Dikirim ke</span>
        <div class="info-value">{$shipToHtml}</div>
      </td>
      <td style="width:34%; text-align:right;">
        <div class="meta-wrap">
          <table class="meta-table">
            <tr>
              <td class="meta-label">Date</td>
              <td class="meta-value">{$invoiceDateLabel}</td>
            </tr>
            <tr>
              <td class="meta-label">Payment Terms</td>
              <td class="meta-value">{$paymentTermsDisplay}</td>
            </tr>
            <tr>
              <td class="meta-label">Due Date</td>
              <td class="meta-value">{$dueDateLabel}</td>
            </tr>
            <tr>
              <td colspan="2" class="total-cell">
                <span class="total-pill">
                  <span class="label">Total Keseluruhan:</span>
                  <span class="value currency">{$totalDisplay}</span>
                </span>
              </td>
            </tr>
          </table>
        </div>
      </td>
    </tr>
  </table>

  <section class="items-section">
    <table class="items-table">
      <colgroup>
        <col style="width:64%">
        <col style="width:12%">
        <col style="width:12%">
        <col style="width:12%">
      </colgroup>
      <thead>
        <tr>
          <th class="desc">Item</th>
          <th class="qty">Quantity(USD)</th>
          <th class="rate">Rate</th>
          <th class="amount">Amount</th>
        </tr>
      </thead>
      <tbody>
        {$itemsRows}
      </tbody>
    </table>
  </section>

  <section class="summary-section">
    <table class="summary-table">
      <colgroup>
        <col style="width:64%">
        <col style="width:12%">
        <col style="width:12%">
        <col style="width:12%">
      </colgroup>
      <tr>
        <td></td>
        <td></td>
        <td class="label">Total:</td>
        <td class="value currency">{$totalDisplay}</td>
      </tr>
    </table>
  </section>

  <section class="notes-section">
    <h2>Notes:</h2>
    {$notesLinesHtml}
  </section>

</div>
</body>
</html>
HTML;

/* =========================
   Render PDF
   ========================= */
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$output = $dompdf->output();

if ($pdfFileName === '') {
    $pdfFileName = 'invoices/' . $invoiceNumberDisplay . '.pdf';
}

file_put_contents($pdfFileName, $output);
file_put_contents('invoice_ready.txt', $invoiceData['invoice_number']);

$dompdf->stream($invoiceData['invoice_number'] . '.pdf', ['Attachment' => true]);
exit;
