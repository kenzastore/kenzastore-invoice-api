<?php
session_start();
require_once 'db_connect.php';

// Initialize variables
$errors = [];
$email = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $captcha_input = trim($_POST['captcha']);

    // Validate CAPTCHA
    if (!isset($_SESSION['captcha']) || strtolower($captcha_input) !== strtolower($_SESSION['captcha'])) {
        $errors[] = 'Invalid CAPTCHA.';
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    // Validate password
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    // If no errors, proceed with authentication
    if (empty($errors)) {
        try {
            // Prepare and execute query to fetch user by email
            $stmt = $pdo->prepare("SELECT id, email, password_hash FROM users WHERE email = :email and verified=1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                header('Location: index.php'); // Redirect to dashboard
                exit;
            } else {
                $errors[] = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

$page_title = "Login";
include 'includes/header.php';
?>

<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-5 col-lg-4">
        <div class="card-custom">
            <div class="text-center mb-4">
                <h2 class="font-weight-bold text-navy">Welcome Back</h2>
                <p class="text-muted">Login to your account</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="font-weight-600">Email Address</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-envelope text-muted"></i></span>
                        </div>
                        <input type="email" id="email" name="email" class="form-control border-left-0" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password" class="font-weight-600">Password</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-lock text-muted"></i></span>
                        </div>
                        <input type="password" id="password" name="password" class="form-control border-left-0" placeholder="Enter your password" required>
                    </div>
                    <div class="text-right mt-1">
                        <a href="#" class="small text-muted">Forgot Password?</a>
                    </div>
                </div>

                <!-- CAPTCHA -->
                <div class="form-group">
                    <label for="captcha" class="font-weight-600">Security Check</label>
                    <div class="d-flex align-items-center mb-2">
                        <img src="captcha.php" alt="CAPTCHA Image" class="rounded border mr-2" style="height: 40px;">
                        <input type="text" id="captcha" name="captcha" class="form-control" placeholder="Enter code" required>
                    </div>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn btn-primary btn-block py-2 mt-4">
                    Login <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="mb-0 text-muted">Don't have an account? <a href="register.php" class="font-weight-bold">Register</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>