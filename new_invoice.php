<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/db_connect.php';

function romanizeTwoDigitYear(int $number): string
{
    if ($number <= 0) {
        return 'N';
    }

    $map = [
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
        'I' => 1,
    ];

    $result = '';

    foreach ($map as $roman => $value) {
        while ($number >= $value) {
            $result .= $roman;
            $number -= $value;
        }
    }

    return $result;
}

function buildInvoicePrefix(): string
{
    $monthAbbreviation = strtoupper(date('M'));
    $twoDigitYear = (int) date('y');
    $yearRoman = romanizeTwoDigitYear($twoDigitYear) ?: 'N';

    return 'INV-' . $monthAbbreviation . '-' . $yearRoman;
}
function generateInvoiceReference(PDO $pdo): string
{
    $prefix = buildInvoicePrefix();
    $prefixParts = explode('-', $prefix);
    $yearRoman = $prefixParts[2] ?? romanizeTwoDigitYear((int) date('y'));
    if ($yearRoman === '') {
        $yearRoman = 'N';
    }

    $nextOdd = 1;

    try {
        $pattern = sprintf('INV-%%-%s-%%', $yearRoman);
        $statement = $pdo->prepare('SELECT invoice_number FROM invoices WHERE invoice_number LIKE :pattern ORDER BY id DESC LIMIT 1');
        $statement->execute([':pattern' => $pattern]);
        $lastInvoice = $statement->fetchColumn();

        if (is_string($lastInvoice) && preg_match('/(\d+)\s*$/', $lastInvoice, $matches)) {
            $lastOdd = (int) $matches[1];
            if (($lastOdd % 2) === 0) {
                $lastOdd -= 1;
            }
            $nextOdd = max($lastOdd + 2, 1);
        }
    } catch (Throwable $exception) {
        // Fall back to the default odd number when lookup fails.
    }

    $oddSuffix = str_pad((string) $nextOdd, 3, '0', STR_PAD_LEFT);

    return $prefix . '-' . $oddSuffix;
}
$generatedInvoiceNumber = generateInvoiceReference($pdo);
$defaultBrandName = 'Kenza Store';
$defaultStoreInfo = "KZ-Store
62 895 3376 74116
Kendangsari Gg 4/45 A-7, Surabaya 60292";

$storedBrandName = isset($_SESSION['brand_name_prefill']) ? (string) $_SESSION['brand_name_prefill'] : $defaultBrandName;
$storedBrandName = $storedBrandName === '' ? $defaultBrandName : $storedBrandName;

$storedStoreInfo = $defaultStoreInfo;

if (isset($_SESSION['store_info_prefill'])) {
    $sessionStoreInfo = trim((string) $_SESSION['store_info_prefill']);

    if ($sessionStoreInfo !== '') {
        $storedStoreInfo = $sessionStoreInfo;
    }
}

$defaultLogoPath = 'assets/images/logo-kenza-store.png';
$defaultLogoFullPath = __DIR__ . '/' . $defaultLogoPath;

$storedLogoRelative = isset($_SESSION['brand_logo_path']) ? (string) $_SESSION['brand_logo_path'] : '';
$storedLogoRelative = trim($storedLogoRelative);

if ($storedLogoRelative !== '') {
    $storedLogoRelative = str_replace('\\', '/', $storedLogoRelative);
}

$storedLogoFullPath = $storedLogoRelative !== '' ? __DIR__ . '/' . $storedLogoRelative : '';

if ($storedLogoRelative !== '' && (!is_file($storedLogoFullPath) || !is_readable($storedLogoFullPath))) {
    $storedLogoRelative = '';
    $storedLogoFullPath = '';
    unset($_SESSION['brand_logo_path'], $_SESSION['brand_logo_mime']);
}

// Fallback to default logo if no session logo is active and user didn't explicitly remove it in this session
$brandLogoActionSession = $_SESSION['brand_logo_action'] ?? '';
if ($storedLogoRelative === '' && $brandLogoActionSession !== 'removed' && is_file($defaultLogoFullPath) && is_readable($defaultLogoFullPath)) {
    $storedLogoRelative = $defaultLogoPath;
}

$storedLogoPublicPath = $storedLogoRelative !== '' ? $storedLogoRelative : '';
$hasStoredLogo = $storedLogoPublicPath !== '';
$brandLogoActionValue = $hasStoredLogo ? 'keep' : '';

$page_title = "Invoice Generator";
include 'includes/header.php';
?>

<style>
    /* Specific styles for invoice generator that are not in global style.css */
    .invoice-wrapper {
        max-width: 1140px;
        margin: 0 auto;
    }

    .invoice-header {
        display: flex;
        flex-wrap: wrap;
        gap: 32px;
        justify-content: space-between;
    }

    .header-left {
        flex: 1 1 340px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .brand-box {
        display: flex;
        flex-direction: column;
        gap: 16px;
        align-items: stretch;
        padding: 28px 24px;
        border-radius: 18px;
        border: 1px dashed rgba(15, 37, 74, 0.25);
        background: var(--bg-surface-alt);
    }

    .brand-logo-shell {
        position: relative;
        min-height: 140px;
        border-radius: 14px;
        border: 1px dashed rgba(15, 37, 74, 0.2);
        background: rgba(15, 37, 74, 0.04);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .brand-logo-preview {
        max-width: 100%;
        max-height: 140px;
        object-fit: contain;
    }

    .brand-logo-placeholder {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .brand-logo-controls {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .brand-name-field {
        background: transparent;
        border: none;
        width: 100%;
        text-align: center;
        font-size: 26px;
        font-weight: 700;
        letter-spacing: 0.24em;
        text-transform: uppercase;
        color: var(--primary-navy);
        resize: none;
    }

    .brand-name-field:focus {
        outline: none;
        border: none;
        box-shadow: none;
    }

    .is-hidden {
        display: none !important;
    }

    .info-card {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .info-card textarea {
        min-height: 120px;
    }

    .invoice-title {
        font-size: 32px;
        font-weight: 700;
        color: var(--primary-navy);
        text-align: right;
        margin: 0;
    }

    .header-right {
        flex: 1 1 320px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        align-items: flex-end;
    }

    .invoice-number {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .invoice-number .prefix {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 14px;
        border-radius: 12px;
        background: rgba(30, 58, 138, 0.12);
        color: var(--primary-navy-soft);
        font-weight: 600;
        letter-spacing: 0.04em;
    }

    .invoice-number input {
        width: 200px;
    }

    .meta-grid {
        width: 100%;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 14px;
    }

    .meta-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .meta-field input {
        text-align: right;
        font-weight: 600;
    }

    label {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    label[contenteditable="true"] {
        cursor: text;
    }

    .address-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 24px;
        margin-top: 24px;
    }

    .address-card {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .items-section {
        margin-top: 36px;
    }

    .items-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 16px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: inset 0 0 0 1px #e2e8f0;
    }

    .items-table thead th {
        background: var(--primary-navy);
        color: #ffffff;
        padding: 16px 20px;
        font-size: 0.9rem;
        font-weight: 600;
        letter-spacing: 0.05em;
        border: none;
    }

    .items-table tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
        background: #f9fbff;
    }

    .items-table tbody tr:last-child td {
        border-bottom: none;
    }

    .items-table textarea {
        min-height: 68px;
    }

    .items-table .control {
        max-width: 120px;
        margin-left: auto;
    }

    .items-table td.action {
        width: 120px;
        text-align: right;
    }

    .remove-item {
        background: #fee2e2;
        border: none;
        color: #b91c1c;
        padding: 8px 14px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .remove-item:hover {
        background: #fecaca;
    }

    .add-item-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 18px;
        background: rgba(15, 157, 88, 0.12);
        color: var(--accent-green);
        border: none;
        padding: 10px 20px;
        border-radius: 999px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .add-item-btn i {
        font-size: 0.85rem;
    }

    .add-item-btn:hover {
        background: rgba(15, 157, 88, 0.18);
        transform: translateY(-1px);
    }

    .notes-summary-row {
        display: flex;
        flex-wrap: wrap;
        gap: 28px;
        margin-top: 36px;
    }

    .notes-card {
        flex: 1 1 340px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .notes-card textarea {
        min-height: 180px;
    }

    .summary-card {
        flex: 1 1 280px;
        background: var(--bg-surface-alt);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        padding: 24px 24px 28px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .summary-row span {
        font-weight: 600;
        color: var(--text-main);
    }

    .summary-row input {
        text-align: right;
        font-weight: 600;
        background: #ffffff;
    }

    .summary-row.accent span {
        color: var(--primary-navy-soft);
    }

    .summary-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 8px 0;
    }

    .summary-row.total span {
        font-size: 1rem;
        color: var(--primary-navy);
    }

    .summary-row.total input {
        font-size: 1rem;
    }

    .summary-row.balance span {
        color: var(--primary-navy);
        font-size: 1.05rem;
    }

    .summary-row.balance input {
        font-size: 1.05rem;
        color: var(--primary-navy);
    }

    .invoice-actions {
        margin-top: 40px;
        display: flex;
        justify-content: flex-end;
    }

    .generate-btn {
        background: var(--primary-navy-soft);
        color: #ffffff;
        border: none;
        padding: 14px 28px;
        border-radius: 16px;
        font-size: 1rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .generate-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 30px rgba(30, 58, 138, 0.22);
    }

    @media (max-width: 768px) {
        .invoice-card {
            padding: 28px 24px;
        }

        .header-right {
            align-items: stretch;
        }

        .invoice-title {
            text-align: left;
        }

        .invoice-number input {
            width: 100%;
        }

        .summary-card {
            flex: 1 1 100%;
        }

        .summary-row {
            flex-direction: column;
            align-items: flex-start;
        }

        .summary-row input {
            width: 100%;
            text-align: left;
        }

        .invoice-actions {
            justify-content: stretch;
        }

        .generate-btn {
            width: 100%;
        }
    }
</style>

<div class="invoice-wrapper">
    <form id="invoiceForm" method="POST" action="generate_invoice.php" class="card-custom invoice-card" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id']; ?>">

        <div class="invoice-header">
            <div class="header-left">
                <div class="brand-box<?= $hasStoredLogo ? ' has-logo' : '' ?>" id="brandBox">
                    <div class="brand-logo-shell">
                        <img src="<?= $hasStoredLogo ? htmlspecialchars($storedLogoPublicPath, ENT_QUOTES, 'UTF-8') : '' ?>" alt="Brand logo preview" id="brandLogoPreview" class="brand-logo-preview<?= $hasStoredLogo ? '' : ' is-hidden' ?>">
                        <div class="brand-logo-placeholder<?= $hasStoredLogo ? ' is-hidden' : '' ?>" id="brandLogoPlaceholder">No logo selected</div>
                    </div>
                    <div class="brand-logo-controls">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectLogoBtn">Choose Logo</button>
                        <button type="button" class="btn btn-sm btn-outline-danger<?= $hasStoredLogo ? '' : ' is-hidden' ?>" id="removeLogoBtn">Remove</button>
                    </div>
                    <input type="file" id="brandLogo" name="brand_logo" accept="image/*" hidden>
                    <input type="hidden" id="brandLogoAction" name="brand_logo_action" value="<?= htmlspecialchars($brandLogoActionValue, ENT_QUOTES, 'UTF-8'); ?>">
                    <textarea id="brand_name" name="brand_name" class="brand-name-field"><?= htmlspecialchars($storedBrandName, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="info-card">
                    <label for="store_info">From</label>
                    <textarea id="store_info" name="store_info"><?= htmlspecialchars($storedStoreInfo, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </div>
            <div class="header-right">
                <h2 class="invoice-title">Bukti Pembelian</h2>
                <div class="invoice-number">
                    <span class="prefix">#</span>
                    <input type="text" name="invoice_number" value="<?= htmlspecialchars($generatedInvoiceNumber, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="meta-grid">
                    <div class="meta-field">
                        <label for="invoice_date">Date</label>
                        <input type="date" id="invoice_date" name="invoice_date" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="meta-field">
                        <label for="payment_terms">Payment Terms</label>
                        <input type="number" id="payment_terms" name="payment_terms" value="1" required>
                    </div>
                    <div class="meta-field">
                        <label for="due_date">Due Date</label>
                        <input type="date" id="due_date" name="due_date" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="meta-field">
                        <label for="header_total">Total Keseluruhan</label>
                        <input type="text" id="header_total" value="0.00" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="address-grid">
            <div class="address-card">
                <label for="bill_to">Kepada</label>
                <textarea id="bill_to" name="bill_to" placeholder="Nama penerima, perusahaan, alamat" required></textarea>
            </div>
            <div class="address-card">
                <label for="ship_to">Dikirim Ke</label>
                <textarea id="ship_to" name="ship_to" placeholder="Alamat pengiriman" required></textarea>
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
                        <td>
                            <textarea name="items[0][description]" placeholder="Deskripsi item" required></textarea>
                        </td>
                        <td>
                            <input type="number" class="quantity control" name="items[0][quantity]" step="any" min="0" placeholder="Qty" required>
                        </td>
                        <td>
                            <input type="number" class="rate control" name="items[0][rate]" step="any" min="0" placeholder="Rate" required>
                        </td>
                        <td>
                            <input type="number" class="amount control" name="items[0][amount]" step="any" min="0" value="0.00" readonly>
                        </td>
                        <td class="action">
                            <button type="button" class="remove-item">Remove</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <button type="button" id="addItem" class="add-item-btn"><i class="fas fa-plus"></i> Line Item</button>
        </div>

        <div class="notes-summary-row">
            <div class="notes-card">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes">*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.
*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).
*)Rate PP hari ini Per $1 USD Rp.17.300,00
</textarea>
            </div>
            <div class="summary-card">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <input type="text" id="subtotal" name="subtotal" value="0.00" readonly>
                </div>
                <div class="summary-row accent">
                    <span>+ Discount</span>
                    <input type="number" id="discount" name="discount" value="0" step="any" min="0">
                </div>
                <div class="summary-row accent">
                    <span>+ Tax (%)</span>
                    <input type="number" id="tax" name="tax" value="0" step="any" min="0">
                </div>
                <div class="summary-row accent">
                    <span>+ Shipping</span>
                    <input type="number" id="shipping" name="shipping" value="0" step="any" min="0">
                </div>
                <div class="summary-divider"></div>
                <div class="summary-row total">
                    <span>Total</span>
                    <input type="text" id="total" name="total" value="0.00" readonly>
                </div>
                <div class="summary-row">
                    <span>Amount Paid</span>
                    <input type="number" id="amount_paid" name="amount_paid" value="0" step="any" min="0">
                </div>
                <div class="summary-row balance">
                    <span>Total Keseluruhan</span>
                    <input type="text" id="balance_due" name="balance_due" value="0.00" readonly>
                </div>
            </div>
        </div>

        <div class="invoice-actions">
            <button type="button" id="generatePdfBtn" class="generate-btn">Download</button>
        </div>
    </form>
</div>

<script>
    (function () {
        const brandBox = document.getElementById('brandBox');
        const selectLogoBtn = document.getElementById('selectLogoBtn');
        const removeLogoBtn = document.getElementById('removeLogoBtn');
        const logoInput = document.getElementById('brandLogo');
        const logoPreview = document.getElementById('brandLogoPreview');
        const logoPlaceholder = document.getElementById('brandLogoPlaceholder');
        const logoAction = document.getElementById('brandLogoAction');

        if (!brandBox) {
            return;
        }

        const toggleState = (hasLogo) => {
            brandBox.classList.toggle('has-logo', hasLogo);

            if (logoPreview) {
                logoPreview.classList.toggle('is-hidden', !hasLogo);
            }

            if (logoPlaceholder) {
                logoPlaceholder.classList.toggle('is-hidden', hasLogo);
            }

            if (removeLogoBtn) {
                removeLogoBtn.classList.toggle('is-hidden', !hasLogo);
            }
        };

        const setAction = (value) => {
            if (logoAction) {
                logoAction.value = value;
            }
        };

        const initialiseState = () => {
            const initialSrc = logoPreview ? logoPreview.getAttribute('src') : '';
            const hasInitialLogo = Boolean(initialSrc && initialSrc.trim() !== '');
            toggleState(hasInitialLogo);

            if (hasInitialLogo && logoAction && logoAction.value === '') {
                setAction('keep');
            }
        };

        const loadFilePreview = (file) => {
            if (!file || !logoPreview) {
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                const target = event.target;
                const result = target && typeof target.result === 'string' ? target.result : '';

                if (result !== '') {
                    logoPreview.src = result;
                    toggleState(true);
                    setAction('uploaded');
                }
            };

            reader.readAsDataURL(file);
        };

        if (selectLogoBtn) {
            selectLogoBtn.addEventListener('click', () => {
                if (logoInput) {
                    logoInput.click();
                }
            });
        }

        if (logoInput) {
            logoInput.addEventListener('change', () => {
                const file = logoInput.files && logoInput.files[0];

                if (!file) {
                    if (!brandBox.classList.contains('has-logo')) {
                        toggleState(false);
                        setAction('keep');
                    }
                    return;
                }

                loadFilePreview(file);
            });
        }

        if (removeLogoBtn) {
            removeLogoBtn.addEventListener('click', () => {
                if (logoInput) {
                    logoInput.value = '';
                }

                if (logoPreview) {
                    logoPreview.src = '';
                }

                toggleState(false);
                setAction('removed');
            });
        }

        initialiseState();
    })();
</script>
<script>
// Calculation script - Updated 2025-11-24 - Moved to footer to ensure jQuery is loaded
function initInvoiceCalculations() {
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }
    
    jQuery(document).ready(function($) {
        function parseNumber(value) {
            const num = parseFloat(value);
            return isNaN(num) ? 0 : num;
        }

        function calculateTotals() {
            console.log('calculateTotals called');
            let subtotal = 0;

            $('.amount').each(function () {
                subtotal += parseNumber($(this).val());
            });

            $('#subtotal').val(subtotal.toFixed(2));

            const discount = parseNumber($('#discount').val());
            const taxRate = parseNumber($('#tax').val());
            const shipping = parseNumber($('#shipping').val());

            const taxAmount = subtotal * (taxRate / 100);
            const total = Math.max(subtotal - discount + taxAmount + shipping, 0);

            $('#total').val(total.toFixed(2));
            $('#header_total').val(total.toFixed(2));

            const amountPaid = parseNumber($('#amount_paid').val());
            const balanceDue = Math.max(total - amountPaid, 0);

            $('#balance_due').val(balanceDue.toFixed(2));
            
            console.log('Calculations updated:', {
                subtotal: subtotal,
                discount: discount,
                taxRate: taxRate,
                shipping: shipping,
                total: total,
                amountPaid: amountPaid,
                balanceDue: balanceDue
            });
        }

        // Add new item
        $(document).on('click', '#addItem', function () {
            const index = $('#invoiceItems tbody tr').length;

            $('#invoiceItems tbody').append(`
                <tr>
                    <td>
                        <textarea name="items[${index}][description]" placeholder="Deskripsi item" required></textarea>
                    </td>
                    <td>
                        <input type="number" class="quantity control" name="items[${index}][quantity]" step="any" min="0" placeholder="Qty" required>
                    </td>
                    <td>
                        <input type="number" class="rate control" name="items[${index}][rate]" step="any" min="0" placeholder="Rate" required>
                    </td>
                    <td>
                        <input type="number" class="amount control" name="items[${index}][amount]" step="any" min="0" value="0.00" readonly>
                    </td>
                    <td class="action">
                        <button type="button" class="remove-item">Remove</button>
                    </td>
                </tr>
            `);

            calculateTotals();
        });

        // Remove item
        $(document).on('click', '.remove-item', function () {
            const rows = $('#invoiceItems tbody tr').length;

            if (rows > 1) {
                $(this).closest('tr').remove();
                calculateTotals();
            }
        });

        // Quantity and rate changes
        $(document).on('input', '.quantity, .rate', function () {
            const row = $(this).closest('tr');
            const quantity = parseNumber(row.find('.quantity').val());
            const rate = parseNumber(row.find('.rate').val());
            const amount = quantity * rate;

            row.find('.amount').val(amount.toFixed(2));
            calculateTotals();
        });

        // Summary field changes
        $('#discount, #tax, #shipping, #amount_paid').on('input', function () {
            calculateTotals();
        });

        // Initial calculation
        setTimeout(calculateTotals, 100);
    });
}

// Initialize when jQuery is available
if (typeof jQuery !== 'undefined') {
    initInvoiceCalculations();
} else {
    // Wait for jQuery to load
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof jQuery !== 'undefined') {
            initInvoiceCalculations();
        } else {
            // jQuery not loaded, try loading it
            var script = document.createElement('script');
            script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
            script.onload = initInvoiceCalculations;
            document.head.appendChild(script);
        }
    });
}
</script>

<script>
    document.getElementById('generatePdfBtn').addEventListener('click', function () {
        const form = document.getElementById('invoiceForm');

        if (!form) {
            return;
        }

        const targetName = 'invoice_pdf';
        const pdfWindow = window.open('', targetName);
        const originalTarget = form.getAttribute('target');
        const originalAction = form.getAttribute('action');

        form.setAttribute('target', targetName);
        form.setAttribute('action', 'generate_invoice.php');

        form.submit();

        if (originalTarget !== null) {
            form.setAttribute('target', originalTarget);
        } else {
            form.removeAttribute('target');
        }

        if (originalAction !== null) {
            form.setAttribute('action', originalAction);
        } else {
            form.removeAttribute('action');
        }

        const checkFile = setInterval(() => {
            fetch('check_invoice_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'ready') {
                        clearInterval(checkFile);

                        if (pdfWindow && !pdfWindow.closed) {
                            pdfWindow.close();
                        }

                        window.location.href = 'thanks.php';
                    }
                })
                .catch(error => console.error('Error:', error));
        }, 3000);
    });
</script>
<script>
    document.querySelectorAll('label').forEach((label) => {
        label.setAttribute('contenteditable', 'true');
        label.setAttribute('spellcheck', 'false');
        label.classList.add('editable-label');
    });
</script>

<?php include 'includes/footer.php'; ?>
