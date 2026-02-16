<?php
// --- PHP LOGIC MUST BE FIRST ---
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/public/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = false;
$error = '';

// Get user info (initial fetch)
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = ($_POST['name'] ?? '');
    $email = ($_POST['email'] ?? '');
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $ward_id = (int)($_POST['ward_id'] ?? 0);

    if (empty($name) || empty($email)) {
        $error = 'Name and email are required';
    } else {
        // Check if email is already taken
        if ($email !== $user['email']) {
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt_check->bind_param("si", $email, $user_id);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                $error = 'Email already in use';
            }
            $stmt_check->close();
        }

        if (!$error && !empty($new_password)) {
            if (!password_verify($old_password, $user['password_hash'])) {
                $error = 'Current password is incorrect';
            } elseif (strlen($new_password) < 8) {
                $error = 'New password must be at least 8 characters';
            }
        }

        if (!$error) {
            $stmt_update = $conn->prepare("UPDATE users SET name = ?, email = ?, default_ward_id = ? WHERE id = ?");
            $stmt_update->bind_param("ssii", $name, $email, $ward_id, $user_id);
            $stmt_update->execute();
            $stmt_update->close();

            if (!empty($new_password)) {
                $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt_pass = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt_pass->bind_param("si", $password_hash, $user_id);
                $stmt_pass->execute();
                $stmt_pass->close();
            }

            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $success = true;
            
            // Refresh user data after update
            $stmt_refresh = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt_refresh->bind_param("i", $user_id);
            $stmt_refresh->execute();
            $user = $stmt_refresh->get_result()->fetch_assoc();
            $stmt_refresh->close();
        }
    }
}

// Get wards for dropdown (placed outside the POST logic to run every time)
$wards = [];
$ward_result = $conn->query("SELECT id, name FROM wards WHERE is_active = TRUE ORDER BY name");
if ($ward_result) $wards = $ward_result->fetch_all(MYSQLI_ASSOC);

// Set page title and include template header
$pageTitle = 'My Profile';
require_once __DIR__ . '/header.php';
?>

<div class="container my-5">
    <a href="<?php echo APP_URL; ?>/public/dashboard.php" class="btn btn-outline-primary btn-sm mb-3" data-mdb-ripple-init>
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>

    <div style="max-width: 700px; margin: 0 auto;">
        <div class="card shadow-3">
            <div class="card-body p-5">
                <h2 class="mb-4"><i class="fas fa-user-edit me-2 text-primary"></i>My Profile</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success mb-3">Profile updated successfully</div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger mb-3"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required />
                        <label class="form-label" for="name">Full Name</label>
                    </div>

                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required />
                        <label class="form-label" for="email">Email</label>
                    </div>

                    <select class="form-select mb-4" name="ward_id">
                        <option value="">Select your default ward</option>
                        <?php foreach ($wards as $ward): ?>
                            <option value="<?php echo $ward['id']; ?>" <?php echo $user['default_ward_id'] == $ward['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ward['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <hr class="my-4">

                    <h4 class="mb-3">Change Password (Optional)</h4>

                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="password" id="old_password" name="old_password" class="form-control" />
                        <label class="form-label" for="old_password">Current Password</label>
                    </div>

                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="password" id="new_password" name="new_password" class="form-control" />
                        <label class="form-label" for="new_password">New Password</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" data-mdb-ripple-init>
                        <i class="fas fa-save me-2"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-3 mt-4">
            <div class="card-body">
                <h4 class="mb-3">Account Information</h4>
                <p><strong>Member Since:</strong> <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                <p class="mb-0"><strong>Last Login:</strong> <?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></p>
            </div>
        </div>
    </div>
</div>

<?php
// Include template footer
require_once __DIR__ . '/footer.php';
?>