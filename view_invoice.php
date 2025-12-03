<?php
session_start();

include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if an invoice ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Invoice ID.");
}

$invoice_id = $_GET['id'];

// Fetch invoice details from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $invoice_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invoice) {
        die("Invoice not found.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$page_title = $invoice['invoice_number'];
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card-custom">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="font-weight-bold text-navy mb-0">Invoice Details</h2>
                <a href="index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-2"></i>Back to List
                </a>
            </div>

            <div class="text-center mb-5">
                <h1 class="font-weight-bold text-primary mb-2"><?php echo htmlspecialchars($invoice['invoice_number']); ?></h1>
                <p class="text-muted mb-0">
                    <i class="far fa-calendar-alt mr-2"></i>
                    <?php echo date('F d, Y', strtotime($invoice['invoice_date'])); ?>
                </p>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="p-3 rounded bg-light border">
                        <label class="small text-uppercase text-muted font-weight-bold">Bill To</label>
                        <div class="font-weight-bold text-dark" style="white-space: pre-line;"><?php echo htmlspecialchars($invoice['bill_to']); ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded bg-light border">
                        <label class="small text-uppercase text-muted font-weight-bold">Due Date</label>
                        <div class="font-weight-bold text-dark">
                            <?php echo date('F d, Y', strtotime($invoice['due_date'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="p-3 rounded bg-light border">
                        <label class="small text-uppercase text-muted font-weight-bold">Status</label>
                        <div>
                            <?php if ($invoice['is_paid'] == 1): ?>
                                <span id="status-badge" class="badge badge-success px-3 py-2">PAID</span>
                            <?php else: ?>
                                <span id="status-badge" class="badge badge-danger px-3 py-2">UNPAID</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded bg-light border">
                        <label class="small text-uppercase text-muted font-weight-bold">Total Amount</label>
                        <div class="h4 font-weight-bold text-success mb-0">
                            IDR <?php echo number_format($invoice['total'], 2); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-3 mt-5">
                <a href="generate_invoice.php?id=<?= $invoice_id  ?>" target="_blank" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-file-pdf mr-2"></i>Download PDF
                </a>
                <a href="update_invoice.php?id=<?= $invoice_id ?>" class="btn btn-outline-primary btn-lg px-5 ml-3">
                    <i class="fas fa-edit mr-2"></i>Edit Invoice
                </a>
                <button onclick="toggleStatus(<?= $invoice_id ?>)" class="btn btn-outline-success btn-lg px-5 ml-3" id="toggle-btn">
                    <i class="fas fa-sync-alt mr-2"></i>Toggle Status
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleStatus(id) {
        const badge = $("#status-badge");
        const currentStatus = badge.text().trim();

        if (currentStatus === 'UNPAID') {
            if (!confirm("Are you sure you want to mark this invoice as PAID?")) {
                return;
            }
        }

        $.ajax({
            url: "toggle_invoice_status.php",
            type: "POST",
            data: { id: id },
            dataType: "json",
            success: function(response) {
                if (response.status === 'success') {
                    if (response.new_status == 1) {
                        badge.removeClass('badge-danger').addClass('badge-success').text('PAID');
                    } else {
                        badge.removeClass('badge-success').addClass('badge-danger').text('UNPAID');
                    }
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Failed to update status. Please try again.');
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>