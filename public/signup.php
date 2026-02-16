<?php
// --- PHP LOGIC MUST BE FIRST ---
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/Auth.php';

$error = '';
$success = '';

// Check if user is already logged in
$isLoggedIn = isset($_SESSION['user_id']);
if ($isLoggedIn) {
    header('Location: ' . APP_URL . '/public/dashboard.php');
    exit();
}

// Process POST request for signup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = ($_POST['name'] ?? '');
    $email = ($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $ward_id = $_POST['ward_id'] ?? null;

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } else {
        $auth = new Auth($conn);
        $result = $auth->register($name, $email, $password, $ward_id);
        
        if ($result['success']) {
            // Log the user in immediately after successful registration
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_type'] = 'citizen';
            header('Location: ' . APP_URL . '/public/dashboard.php');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}

// Fetch Wards for the dropdown (runs every time the page loads)
$wards = [];
$ward_result = $conn->query("SELECT id, name FROM wards WHERE is_active = TRUE ORDER BY name");
if ($ward_result) {
    $wards = $ward_result->fetch_all(MYSQLI_ASSOC);
}

// Set page title and include template header
$pageTitle = 'Sign Up';
require_once __DIR__ . '/header.php';
?>

<div class="container my-5">
    <div style="max-width: 500px; margin: 3rem auto;">
        <div class="card shadow-3">
            <div class="card-body p-5">
                <!-- FIXED: Added professional icon to title -->
                <h2 class="text-center mb-4"><i class="fas fa-user-plus me-2 text-primary"></i>Create Account</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Name -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="text" id="name" name="name" class="form-control" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" />
                        <label class="form-label" for="name">Full Name</label>
                    </div>

                    <!-- Email -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="email" id="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
                        <label class="form-label" for="email">Email</label>
                    </div>
                    
                    <!-- Ward Selection -->
                    <select class="form-select mb-4" name="ward_id" required>
                        <option value="">Select your ward</option>
                        <?php foreach ($wards as $ward): ?>
                            <option value="<?php echo $ward['id']; ?>" <?php echo (($_POST['ward_id'] ?? '') == $ward['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ward['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Password -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="password" id="password" name="password" class="form-control" required />
                        <label class="form-label" for="password">Password (At least 8 characters)</label>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" required />
                        <label class="form-label" for="password_confirm">Confirm Password</label>
                    </div>

                    <button type="submit" data-mdb-ripple-init class="btn btn-primary btn-block btn-lg mb-4">
                        <i class="fas fa-user-plus me-1"></i> Sign Up
                    </button>
                </form>

                <p class="text-center" style="margin-top: 1rem;">
                    Already have an account? <a href="<?php echo APP_URL; ?>/public/login.php">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php
// Include template footer
require_once __DIR__ . '/footer.php';
?>