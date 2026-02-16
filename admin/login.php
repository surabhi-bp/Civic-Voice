<?php
// --- PHP LOGIC MUST BE FIRST ---

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/Auth.php'; // MUST be loaded before Auth methods

$error = '';

$debug = isset($_GET['debug']) ? true : false;
$debug_info = [];

// Checks if user is already logged in (using Auth::isAdminLoggedIn which handles session_start internally)
if (Auth::isAdminLoggedIn()) {
    header('Location: ' . APP_URL . '/admin/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // *** FINAL FIX: Removed sanitizeInput() for login credentials ***
    // Credentials MUST be passed RAW for database lookup/password_verify.
    $email = $_POST['email'] ?? ''; 
    $password = $_POST['password'] ?? ''; 

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        $auth = new Auth($conn);
        
        // Debug block removed for clean execution
        
        $result = $auth->adminLogin($email, $password);
        
        if ($result['success']) {
            // The Auth class successfully set the session and we redirect
            header('Location: ' . APP_URL . '/admin/dashboard.php');
            exit();
        } else {
            // Error message from Auth::adminLogin (Invalid admin credentials)
            $error = $result['message']; 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CivicVoice</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.css" rel="stylesheet"/>
    
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/styles.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
      <div class="container-fluid justify-content-between">
        <a class="navbar-brand text-white" href="<?php echo APP_URL; ?>/public/index.php">
            <i class="fas fa-landmark me-2"></i> CivicVoice
        </a>
        <a href="<?php echo APP_URL; ?>/public/login.php" class="btn btn-outline-light btn-sm">
            Citizen Login
        </a>
      </div>
    </nav>

    <main class="content">
        <div class="container my-5">
            <div style="max-width: 500px; margin: 3rem auto;">
                <div class="card shadow-5">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-2"><i class="fas fa-user-shield me-2 text-danger"></i>Admin Login</h2>
                        <p class="text-center text-muted mb-4">Municipal Officials Only</p>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <span><?php echo htmlspecialchars($error); ?></span>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="admin-login-form">
                            <div data-mdb-input-init class="form-outline mb-4">
                                <input type="email" id="email" name="email" class="form-control" required />
                                <label class="form-label" for="email">Email</label>
                            </div>

                            <div data-mdb-input-init class="form-outline mb-4">
                                <input type="password" id="password" name="password" class="form-control" required />
                                <label class="form-label" for="password">Password</label>
                            </div>

                            <button type="submit" class="btn btn-danger btn-block btn-lg mb-4" data-mdb-ripple-init>
                                <i class="fas fa-lock me-1"></i> Login to Admin Panel
                            </button>
                        </form>

                        <p class="text-center" style="margin-top: 1rem;">
                            <a href="<?php echo APP_URL; ?>/public/login.php">Not an admin? Go back to Citizen Login</a>
                        </p>
                        
                        <hr class="my-4">
                        
                        <p class="text-center text-muted small">
                            <i class="fas fa-info-circle me-1"></i> 
                            First time setting up admin? 
                            <a href="<?php echo APP_URL; ?>/admin/create-admin.php" class="text-decoration-none">
                                Create an admin user here
                            </a>
                        </p>
                        
                        <p class="text-center text-muted small">
                            <i class="fas fa-stethoscope me-1"></i>
                            Having issues? <a href="<?php echo APP_URL; ?>/admin/diagnostics.php" class="text-decoration-none">Run diagnostics</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.umd.min.js"></script>
    
    <script src="<?php echo APP_URL; ?>/assets/js/app.js"></script>
</body>
</html>
```eof