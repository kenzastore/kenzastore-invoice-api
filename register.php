<?php
session_start();
require_once 'db_connect.php';

$errors = [];
$email = '';
$password = '';
$confirm_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
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
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    // Validate confirm password
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Check if email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $email_exists = $stmt->fetchColumn();

            if ($email_exists) {
                $errors[] = 'Email is already registered.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));

        // Insert user into the database (not verified by default)
        try {
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, verified, verification_token) VALUES (:email, :password_hash, 0, :verification_token)");
            $stmt->execute([
                'email' => $email,
                'password_hash' => $password_hash,
                'verification_token' => $verification_token
            ]);

            // Store success message in session
            $_SESSION['registration_success'] = true;
            
            // Display verification link (since no email server)
            $verification_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/verify_email.php?token=" . $verification_token;
            
            $page_title = "Registration Successful";
            include 'includes/header.php';
            ?>
            <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
                <div class="col-md-6 col-lg-5">
                    <div class="card-custom text-center">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success fa-4x"></i>
                        </div>
                        <h2 class="font-weight-bold text-navy mb-3">Registration Successful!</h2>
                        <p class="text-muted mb-4">Please verify your email address to complete the registration process.</p>
                        
                        <div class="alert alert-warning text-break">
                            <strong>Verification Link (Simulation):</strong><br>
                            <a href="<?php echo htmlspecialchars($verification_link); ?>"><?php echo htmlspecialchars($verification_link); ?></a>
                        </div>
                        
                        <div class="mt-4">
                            <a href="login.php" class="btn btn-primary">Go to Login</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            include 'includes/footer.php';
            exit;

        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

$page_title = "Register";
include 'includes/header.php';
?>

<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-5 col-lg-4">
        <div class="card-custom">
            <div class="text-center mb-4">
                <h2 class="font-weight-bold text-navy">Create Account</h2>
                <p class="text-muted">Join us today!</p>
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

            <form action="register.php" method="POST">
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
                        <input type="password" id="password" name="password" class="form-control border-left-0" placeholder="Create a password" required>
                    </div>
                    <small class="form-text text-muted">Must be at least 8 characters long.</small>
                </div>

                <!-- Confirm Password Field -->
                <div class="form-group">
                    <label for="confirm_password" class="font-weight-600">Confirm Password</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-lock text-muted"></i></span>
                        </div>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control border-left-0" placeholder="Confirm your password" required>
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

                <!-- Register Button -->
                <button type="submit" class="btn btn-primary btn-block py-2 mt-4">
                    Register <i class="fas fa-user-plus ml-2"></i>
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="mb-0 text-muted">Already have an account? <a href="login.php" class="font-weight-bold">Login</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>