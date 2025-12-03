<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle invoice deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    try {
        $delete_id = $_POST['delete_id'];

        // Fetch invoice number before deleting
        $stmt = $pdo->prepare("SELECT invoice_number FROM invoices WHERE id = ?");
        $stmt->execute([$delete_id]);
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($invoice) {
            $stmt = $pdo->prepare("DELETE FROM invoices WHERE id = ?");
            $stmt->execute([$delete_id]);

            logAction($pdo, $_SESSION['user_id'], "Deleted Invoice", null, "Deleted Invoice Number: " . $invoice['invoice_number']);
        }

        echo json_encode(["status" => "success"]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        exit;
    }
}

$page_title = "My Invoices";
include 'includes/header.php';
?>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="card-custom">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">My Invoices</h2>
        <a href="new_invoice.php" class="btn btn-success">
            <i class="fas fa-plus mr-2"></i>New Invoice
        </a>
    </div>

    <div class="mb-4">
        <form method="GET" action="index.php" class="form-inline">
            <div class="input-group w-100">
                <input type="text" name="search" class="form-control"
                    placeholder="Search by invoice number or customer..."
                    value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php
    $user_id = $_SESSION['user_id'];
    $search = trim($_GET['search'] ?? '');

    // Pagination setup
    $invoices_per_page = 10;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($page - 1) * $invoices_per_page;

    // Get total invoices count
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE user_id = :user_id" . ($search ? " AND (invoice_number LIKE :search OR bill_to LIKE :search)" : ""));
    $total_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    if ($search) {
        $total_stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }
    $total_stmt->execute();
    $total_invoices = $total_stmt->fetchColumn();
    $total_pages = ceil($total_invoices / $invoices_per_page);

    // Fetch invoices with pagination
    $invoices = [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM invoices WHERE user_id = :user_id" . ($search ? " AND (invoice_number LIKE :search OR bill_to LIKE :search)" : "") . " ORDER BY id DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        if ($search) {
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $invoices_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Failed to retrieve invoices: ' . $e->getMessage() . '</div>';
    }
    ?>

    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Reference</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($invoices)): ?>
                    <?php foreach ($invoices as $row): ?>
                        <tr id="invoice-row-<?php echo $row['id']; ?>">
                            <td>
                                <div class="font-weight-bold"><?php echo htmlspecialchars($row['bill_to']); ?></div>
                            </td>
                            <td>
                                <a href="view_invoice.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="text-primary font-weight-bold">
                                    <?php echo htmlspecialchars($row['invoice_number']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($row['invoice_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                            <td>
                                <?php echo ($row['is_paid'] == 1) 
                                    ? '<span class="badge badge-custom badge-success">PAID</span>' 
                                    : '<span class="badge badge-custom badge-danger">UNPAID</span>'; ?>
                            </td>
                            <td class="font-weight-bold">IDR <?php echo number_format($row['total'], 2); ?></td>
                            <td class="text-right">
                                <a href="update_invoice.php?id=<?php echo htmlspecialchars($row['id']); ?>"
                                    class="btn btn-warning btn-sm mr-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-danger btn-sm"
                                    onclick="deleteInvoice(<?php echo $row['id']; ?>)" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-file-invoice fa-3x mb-3"></i>
                                <p>No invoices found.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Invoices pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
    function deleteInvoice(id) {
        if (!confirm("Are you sure you want to delete this invoice?")) {
            return;
        }
        $.ajax({
            url: "index.php",
            type: "POST",
            data: {
                delete_id: id
            },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $("#invoice-row-" + id).fadeOut();
                } else {
                    alert("Failed to delete invoice: " + response.message);
                }
            },
            error: function () {
                alert("An error occurred while deleting the invoice.");
            }
        });
    }


</script>

<?php include 'includes/footer.php'; ?>