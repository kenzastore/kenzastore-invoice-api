<?php
session_start();
include 'db_connect.php';

// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ✅ Pagination setup
$logs_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $logs_per_page;

// ✅ Get total logs count
$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM logs");
$total_stmt->execute();
$total_logs = $total_stmt->fetchColumn();
$total_pages = ceil($total_logs / $logs_per_page);

// ✅ Fetch logs from the database with pagination
$stmt = $pdo->prepare("SELECT logs.*, users.email FROM logs JOIN users ON logs.user_id = users.id ORDER BY logs.created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $logs_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Activity Logs";
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card-custom">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="font-weight-bold text-navy mb-0">Activity Logs</h2>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Invoice ID</th>
                            <th>Details</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-navy"><?= htmlspecialchars($log['email']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border"><?= htmlspecialchars($log['action']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($log['invoice_id']): ?>
                                            <a href="update_invoice.php?id=<?= htmlspecialchars($log['invoice_id']); ?>" class="text-primary font-weight-bold">
                                                #<?= htmlspecialchars($log['invoice_id']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?= htmlspecialchars($log['details']); ?>
                                    </td>
                                    <td>
                                        <span class="text-muted small">
                                            <i class="far fa-clock mr-1"></i>
                                            <?= date('M d, Y H:i', strtotime($log['created_at'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-history fa-3x mb-3 text-muted-light"></i>
                                        <p>No activity logs found.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Logs pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>