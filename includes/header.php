<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Invoice System</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <script>
        // Check for dark mode preference immediately to prevent flash
        if (localStorage.getItem("darkMode") === "enabled") {
            document.documentElement.classList.add("dark-mode"); // Add to html tag for faster apply
            document.addEventListener("DOMContentLoaded", function() {
                document.body.classList.add("dark-mode");
            });
        }
    </script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-file-invoice-dollar mr-2"></i>Invoice System
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"><i class="fas fa-bars"></i></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if (isset($_SESSION['user_id'])): ?>
            <ul class="navbar-nav ml-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-list mr-1"></i> My Invoices
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'new_invoice.php' ? 'active' : ''; ?>" href="new_invoice.php">
                        <i class="fas fa-plus mr-1"></i> New Invoice
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="api_docs.html" target="_blank">
                        <i class="fas fa-book mr-1"></i> API Docs
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                        <i class="fas fa-user-circle mr-1"></i> 
                        <?php echo isset($_SESSION['email']) ? htmlspecialchars(explode('@', $_SESSION['email'])[0]) : "User"; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right border-0 shadow-lg">
                        <a class="dropdown-item" href="#"><i class="fas fa-cog mr-2"></i> Settings</a>
                        <a class="dropdown-item" href="view_logs.php"><i class="fas fa-history mr-2"></i> Activity Logs</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
                    </div>
                </li>
                <li class="nav-item ml-2">
                    <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode">
                        <i id="darkModeIcon" class="fas fa-moon"></i>
                    </button>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="showApiToken(event)">
                        <i class="fas fa-key mr-1"></i> Get Token
                    </a>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- API Token Modal -->
<div class="modal fade" id="tokenModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-key mr-2"></i>Your API Token</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Use this token in the <code>Authorization</code> header: <code>Bearer YOUR_TOKEN</code></p>
                <div class="input-group">
                    <input type="text" class="form-control" id="apiTokenInput" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button" onclick="copyToken()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div id="copySuccess" class="text-success small mt-2" style="display:none;">
                    <i class="fas fa-check-circle mr-1"></i> Token copied to clipboard!
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showApiToken(e) {
    e.preventDefault();
    fetch('get_api_token.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('apiTokenInput').value = data.token;
                $('#tokenModal').modal('show');
            } else {
                alert('Failed to generate token: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching the token');
        });
}

function copyToken() {
    var copyText = document.getElementById("apiTokenInput");
    copyText.select();
    copyText.setSelectionRange(0, 99999); /* For mobile devices */
    document.execCommand("copy");
    
    var successMsg = document.getElementById("copySuccess");
    successMsg.style.display = "block";
    setTimeout(function() {
        successMsg.style.display = "none";
    }, 2000);
}
</script>

<div class="main-container">
