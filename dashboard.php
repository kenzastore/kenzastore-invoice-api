<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$email = htmlspecialchars($_SESSION['email']);
$userId = $_SESSION['user_id'];

// Check if the user is already verified
require_once 'db_connect.php';

try {
    $stmt = $pdo->prepare("SELECT verified FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $isVerified = $user['verified'];
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$page_title = "Dashboard";
include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card-custom">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="font-weight-bold text-navy mb-0">Dashboard</h2>
                <span class="badge badge-primary px-3 py-2" style="font-size: 0.9rem; border-radius: 20px;">
                    <i class="fas fa-user mr-2"></i><?php echo $email; ?>
                </span>
            </div>

            <?php
            // Display the registration success notification if it exists
            if (isset($_SESSION['registration_success']) && $_SESSION['registration_success']) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Registration successful!</strong> Please verify your account below.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                      </div>';
                // Clear the session variable
                unset($_SESSION['registration_success']);
            }
            ?>

            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="p-4 rounded" style="background-color: var(--bg-surface-alt); border: 1px solid var(--border-color);">
                        <h4 class="text-navy mb-3">Account Status</h4>
                        <?php if ($isVerified): ?>
                            <div class="d-flex align-items-center text-success">
                                <i class="fas fa-check-circle fa-2x mr-3"></i>
                                <div>
                                    <h5 class="mb-1">Verified</h5>
                                    <p class="mb-0 text-muted">Your account is fully verified and active.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="d-flex align-items-center text-warning mb-3">
                                <i class="fas fa-exclamation-circle fa-2x mr-3"></i>
                                <div>
                                    <h5 class="mb-1">Unverified</h5>
                                    <p class="mb-0 text-muted">Your account is not verified yet.</p>
                                </div>
                            </div>
                            <form action="verify_user.php" method="POST">
                                <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                                <button type="submit" class="btn btn-warning text-white">
                                    <i class="fas fa-envelope mr-2"></i>Verify Account
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <a href="index.php" class="btn btn-outline-primary btn-block py-3">
                        <i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>
                        Manage Invoices
                    </a>
                </div>
                <div class="col-md-6 mb-3">
                    <a href="view_logs.php" class="btn btn-outline-info btn-block py-3">
                        <i class="fas fa-history fa-2x mb-2 d-block"></i>
                        View Activity Logs
                    </a>
                </div>
            </div>
            
            <div class="mt-4 text-center">
                <a href="logout.php" class="btn btn-danger px-4">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>