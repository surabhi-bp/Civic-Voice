<?php
// --- PHP LOGIC MUST BE FIRST ---
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/Auth.php'; // Your file requires this, so we keep it.

$error = '';

// Check if user is already logged in BEFORE processing POST request
$isLoggedIn = isset($_SESSION['user_id']);
if ($isLoggedIn) {
    header('Location: ' . APP_URL . '/public/dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = ($_POST['email'] ?? ''); 
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        $auth = new Auth($conn);
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            if ($result['user_type'] === 'admin') {
                header('Location: ' . APP_URL . '/admin/login.php');
            } else {
                header('Location: ' . APP_URL . '/public/dashboard.php');
            }
            exit();
        } else {
            $error = $result['message'];
        }
    }
}

// Set page title and include template files (using your requested structure)
$pageTitle = 'Login';
require_once __DIR__ . '/header.php'; 
?>

<div class="container my-5">
    <div style="max-width: 500px; margin: 3rem auto;">
        <div class="card shadow-3">
            <div class="card-body p-5">
                <!-- FIXED: Added professional icon to title -->
                <h2 class="text-center mb-4"><i class="fas fa-lock me-2 text-primary"></i>Login to CivicVoice</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Email Input -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="email" id="email" name="email" class="form-control" required />
                        <label class="form-label" for="email">Email</label>
                    </div>

                    <!-- Password Input -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="password" id="password" name="password" class="form-control" required />
                        <label class="form-label" for="password">Password</label>
                    </div>

                    <button type="submit" data-mdb-ripple-init class="btn btn-primary btn-block btn-lg mb-4">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </button>
                </form>

                <p class="text-center mb-2">
                    <a href="<?php echo APP_URL; ?>/public/forgot-password.php">Forgot password?</a>
                </p>

                <p class="text-center" style="margin-top: 1rem;">
                    Don't have an account? <a href="<?php echo APP_URL; ?>/public/signup.php">Sign up here</a>
                </p>

                <hr class="my-4">

                <p class="text-center text-muted">
                    <strong>Admin Login?</strong> <a href="<?php echo APP_URL; ?>/admin/login.php">Click here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php
// Include template footer
require_once __DIR__ . '/footer.php';
?>