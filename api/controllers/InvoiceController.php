<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class InvoiceController {
    private $db;
    private $userId;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function processRequest($method, $id) {
        // Authenticate all invoice requests
        $user = AuthMiddleware::authenticate();
        $this->userId = $user['user_id'];

        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getInvoice($id);
                } else {
                    $this->getAllInvoices();
                }
                break;
            case 'POST':
                $this->createInvoice();
                break;
            case 'PUT':
                if ($id) {
                    $this->updateInvoice($id);
                } else {
                    $this->respondBadRequest('ID required for update');
                }
                break;
            case 'DELETE':
                if ($id) {
                    $this->deleteInvoice($id);
                } else {
                    $this->respondBadRequest('ID required for deletion');
                }
                break;
            default:
                $this->respondMethodNotAllowed();
                break;
        }
    }

    private function getAllInvoices() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':user_id', $this->userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count for pagination
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM invoices WHERE user_id = :user_id");
        $countStmt->execute(['user_id' => $this->userId]);
        $total = $countStmt->fetchColumn();

        echo json_encode([
            'status' => 'success',
            'data' => $invoices,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_records' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
    }

    private function getInvoice($id) {
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $this->userId]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$invoice) {
            $this->respondNotFound('Invoice not found');
            return;
        }

        // Get items
        $itemStmt = $this->db->prepare("SELECT * FROM invoice_items WHERE invoice_id = :invoice_id");
        $itemStmt->execute(['invoice_id' => $id]);
        $invoice['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $invoice]);
    }

    private function createInvoice() {
        $data = json_decode(file_get_contents("php://input"), true);
        $this->saveInvoice($data);
    }

    private function updateInvoice($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        $this->saveInvoice($data, $id);
    }

    private function deleteInvoice($id) {
        $stmt = $this->db->prepare("SELECT id FROM invoices WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $this->userId]);
        if (!$stmt->fetch()) {
            $this->respondNotFound('Invoice not found');
            return;
        }

        try {
            $this->db->beginTransaction();
            
            // Delete items first
            $this->db->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$id]);
            
            // Delete invoice
            $this->db->prepare("DELETE FROM invoices WHERE id = ?")->execute([$id]);
            
            $this->db->commit();
            echo json_encode(['status' => 'success', 'message' => 'Invoice deleted']);
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->respondInternalError($e->getMessage());
        }
    }

    public function generatePdf($id) {
        // Authenticate
        $user = AuthMiddleware::authenticate();
        $this->userId = $user['user_id'];
        
        if (!$id) {
            $this->respondBadRequest('Invoice ID required');
            return;
        }
        
        // Get invoice data
        $stmt = $this->db->prepare("SELECT * FROM invoices WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $this->userId]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$invoice) {
            $this->respondNotFound('Invoice not found');
            return;
        }
        
        // Get items
        $itemStmt = $this->db->prepare("SELECT * FROM invoice_items WHERE invoice_id = :invoice_id");
        $itemStmt->execute(['invoice_id' => $id]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Generate PDF using dompdf
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $dompdf = new \Dompdf\Dompdf();
        $html = $this->generateInvoiceHtml($invoice, $items);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Save PDF to file
        $pdfDir = __DIR__ . '/../../invoices';
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0777, true);
        }
        
        $pdfPath = $pdfDir . '/' . $invoice['invoice_number'] . '.pdf';
        file_put_contents($pdfPath, $dompdf->output());
        
        // Return success with download URL (environment-aware)
        $downloadUrl = Config::getInvoiceBaseUrl() . $invoice['invoice_number'] . '.pdf';
        echo json_encode([
            'status' => 'success',
            'message' => 'PDF generated successfully',
            'pdf_url' => $downloadUrl,
            'invoice_number' => $invoice['invoice_number']
        ]);
    }
    
    private function generateInvoiceHtml($invoice, $items) {
        // Format helper functions
        $formatCurrency = function($value) {
            return 'IDR ' . number_format($value, 2, '.', ',');
        };
        
        $formatQuantity = function($value) {
            $formatted = number_format($value, 2, '.', ',');
            $formatted = rtrim(rtrim($formatted, '0'), '.');
            return $formatted === '' ? '0' : $formatted;
        };
        
        $formatText = function($value) {
            if ($value === null) return '';
            return nl2br(htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8'));
        };
        
        $formatDateLabel = function($value) {
            if (!$value) return '';
            $timestamp = strtotime($value);
            if ($timestamp === false) return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            return date('M d, Y', $timestamp);
        };
        
        // Format invoice data
        $brandName = 'KENZA STORE';
        $storeInfo = "KZ-Store\n62 895 3376 74116\nKendangsari Gg 4/45 A-7, Surabaya 60292";
        
        $invoiceNumberDisplay = htmlspecialchars($invoice['invoice_number'] ?? 'INVOICE', ENT_QUOTES, 'UTF-8');
        $invoiceDateLabel = $formatDateLabel($invoice['invoice_date'] ?? '');
        $dueDateLabel = $formatDateLabel($invoice['due_date'] ?? '');
        $paymentTermsDisplay = htmlspecialchars($invoice['payment_terms'] ?? '0', ENT_QUOTES, 'UTF-8');
        
        $brandDisplay = strtoupper(htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8'));
        $storeInfoHtml = $formatText($storeInfo);
        $billToHtml = $formatText($invoice['bill_to'] ?? '');
        $shipToHtml = $formatText($invoice['ship_to'] ?? '');
        
        if ($billToHtml === '') $billToHtml = 'YDSF';
        if ($shipToHtml === '') $shipToHtml = 'Kertajaya 8C No 17';
        
        $subtotal = floatval($invoice['subtotal'] ?? 0);
        $discount = floatval($invoice['discount'] ?? 0);
        $taxPercent = floatval($invoice['tax'] ?? 0);
        $shipping = floatval($invoice['shipping'] ?? 0);
        $total = floatval($invoice['total'] ?? 0);
        
        $taxAmount = $subtotal * ($taxPercent / 100);
        
        $subtotalDisplay = $formatCurrency($subtotal > 0 ? $subtotal : $total);
        $totalDisplay = $formatCurrency($total > 0 ? $total : $subtotal);
        
        // Build items rows
        $itemsRows = '';
        if (count($items) === 0) {
            $itemsRows .= '
                <tr class="item-row">
                    <td class="desc">&nbsp;</td><td class="qty">&nbsp;</td><td class="rate">&nbsp;</td><td class="amount">&nbsp;</td>
                </tr>';
        } else {
            foreach ($items as $item) {
                $description = $formatText($item['description'] ?? '');
                $quantity = $formatQuantity($item['quantity'] ?? 0);
                $rate = $formatCurrency(floatval($item['rate'] ?? 0));
                $amountFormatted = $formatCurrency(floatval($item['amount'] ?? 0));
                $itemsRows .= '
                    <tr class="item-row">
                        <td class="desc">'.$description.'</td>
                        <td class="qty">'.$quantity.'</td>
                        <td class="rate currency">'.$rate.'</td>
                        <td class="amount currency">'.$amountFormatted.'</td>
                    </tr>';
            }
        }
        
        // Build notes
        $rawNotes = trim((string) ($invoice['notes'] ?? ''));
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
        
        return <<<HTML
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
.logo-text { width:100%; text-align:center; text-transform:uppercase; letter-spacing:.42em; font-weight:600; font-size:11px; color:#616161; }

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
        <div class="logo" aria-label="{$brandDisplay}">
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
    }

    private function saveInvoice($data, $id = null) {
        // Validation
        if (empty($data['items']) || !is_array($data['items'])) {
            $this->respondBadRequest('At least one item is required');
            return;
        }

        // Calculations
        $subtotal = 0;
        $items = [];
        foreach ($data['items'] as $item) {
            $qty = $this->cleanNumber($item['quantity'] ?? 0);
            $rate = $this->cleanNumber($item['rate'] ?? 0);
            $amount = $qty * $rate;
            $subtotal += $amount;
            $items[] = [
                'description' => $item['description'] ?? '',
                'quantity' => $qty,
                'rate' => $rate,
                'amount' => $amount
            ];
        }

        $discount = $this->cleanNumber($data['discount'] ?? 0);
        $taxPercent = $this->cleanNumber($data['tax'] ?? 0);
        $shipping = $this->cleanNumber($data['shipping'] ?? 0);
        
        $taxAmount = ($subtotal - $discount) * ($taxPercent / 100);
        // Ensure tax is not negative if discount > subtotal
        if ($taxAmount < 0) $taxAmount = 0;
        
        $total = max($subtotal - $discount + $taxAmount + $shipping, 0);

        // Prepare data for DB
        $invoiceNumber = $data['invoice_number'] ?? 'INV-' . time(); // Simple fallback
        $invoiceDate = $data['invoice_date'] ?? date('Y-m-d');
        $dueDate = $data['due_date'] ?? $invoiceDate;
        $paymentTerms = (int)($data['payment_terms'] ?? 0);
        
        try {
            $this->db->beginTransaction();

            if ($id) {
                // Update
                $sql = "UPDATE invoices SET 
                        invoice_number = ?, invoice_date = ?, due_date = ?, payment_terms = ?,
                        bill_to = ?, ship_to = ?, notes = ?, 
                        subtotal = ?, discount = ?, tax = ?, shipping = ?, total = ?
                        WHERE id = ? AND user_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $invoiceNumber, $invoiceDate, $dueDate, $paymentTerms,
                    $data['bill_to'] ?? '', $data['ship_to'] ?? '', $data['notes'] ?? '',
                    $subtotal, $discount, $taxPercent, $shipping, $total,
                    $id, $this->userId
                ]);
                
                // Delete old items
                $this->db->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$id]);
                $invoiceId = $id;
            } else {
                // Insert
                $sql = "INSERT INTO invoices (
                        user_id, invoice_number, invoice_date, due_date, payment_terms,
                        bill_to, ship_to, notes, 
                        subtotal, discount, tax, shipping, total, pdf_path
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $pdfPath = Config::getInvoiceBaseUrl() . $invoiceNumber . '.pdf';
                $stmt->execute([
                    $this->userId, $invoiceNumber, $invoiceDate, $dueDate, $paymentTerms,
                    $data['bill_to'] ?? '', $data['ship_to'] ?? '', $data['notes'] ?? '',
                    $subtotal, $discount, $taxPercent, $shipping, $total, $pdfPath
                ]);
                $invoiceId = $this->db->lastInsertId();
            }

            // Insert items
            $ins = $this->db->prepare("INSERT INTO invoice_items (invoice_id, description, quantity, rate, amount) VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $ins->execute([$invoiceId, $item['description'], $item['quantity'], $item['rate'], $item['amount']]);
            }

            $this->db->commit();
            echo json_encode(['status' => 'success', 'data' => ['id' => $invoiceId, 'total' => $total]]);

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->respondInternalError($e->getMessage());
        }
    }

    private function cleanNumber($value) {
        if ($value === null || $value === '') return 0.0;
        if (is_numeric($value)) return (float)$value;
        $normalized = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', (string)$value));
        return $normalized === '' ? 0.0 : (float)$normalized;
    }

    private function respondBadRequest($message) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }

    private function respondNotFound($message) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }

    private function respondMethodNotAllowed() {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        exit;
    }

    private function respondInternalError($message) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }
}
