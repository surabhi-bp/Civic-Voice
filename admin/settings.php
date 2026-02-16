<?php
// --- PHP LOGIC MUST BE FIRST ---
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// IMPORTANT: Admin Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ' . APP_URL . '/admin/login.php');
    exit();
}

$success = false;
$error = '';

// Handle POST request (Save Settings)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get posted values
    $site_name = sanitizeInput($_POST['site_name'] ?? 'CivicVoice');
    $site_email = sanitizeInput($_POST['site_email'] ?? '');
    $site_phone = sanitizeInput($_POST['site_phone'] ?? '');
    
    // In a real application, logic to write these to a settings table would go here.
    
    $success = true;
}

// Set page title and include template header (Admin structure)
$pageTitle = 'Settings';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Start of Settings Content -->
<h1 class="mb-4"><i class="fas fa-cog me-2 text-primary"></i>Settings</h1>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-3 h-100 p-4">
            <h3 class="mb-4">General Settings</h3>

            <?php if ($success): ?>
                <div class="alert alert-success mb-3"><i class="fas fa-check-circle me-1"></i> Settings updated successfully</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger mb-3"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <!-- Site Name -->
                <div data-mdb-input-init class="form-outline mb-4">
                    <input type="text" id="site_name" name="site_name" class="form-control" value="<?php echo htmlspecialchars($_POST['site_name'] ?? 'CivicVoice'); ?>" required />
                    <label class="form-label" for="site_name">Site Name</label>
                </div>

                <!-- Email for Notifications -->
                <div data-mdb-input-init class="form-outline mb-4">
                    <input type="email" id="site_email" name="site_email" class="form-control" value="<?php echo htmlspecialchars($_POST['site_email'] ?? 'admin@civicvoice.local'); ?>" placeholder="admin@civicvoice.local" />
                    <label class="form-label" for="site_email">Email for Notifications</label>
                </div>

                <!-- Support Phone -->
                <div data-mdb-input-init class="form-outline mb-4">
                    <input type="tel" id="site_phone" name="site_phone" class="form-control" value="<?php echo htmlspecialchars($_POST['site_phone'] ?? '+1-555-0000'); ?>" placeholder="+1-555-0000" />
                    <label class="form-label" for="site_phone">Support Phone</label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save me-2"></i> Save Settings
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <!-- Database Info Card -->
        <div class="card shadow-3 mb-4 p-4">
            <h3 class="mb-3"><i class="fas fa-database me-2"></i>Database Info</h3>
            <div class="p-3 border rounded">
                <p class="mb-1"><strong>Database Name:</strong> <code><?php echo DB_NAME; ?></code></p>
                <p class="mb-1"><strong>Host:</strong> <code><?php echo DB_HOST; ?></code></p>
                <p class="mb-0"><strong>App Version:</strong> 1.0.0</p>
            </div>
        </div>

        <!-- Documentation Card -->
        <div class="card shadow-3 p-4">
            <h3 class="mb-3"><i class="fas fa-book me-2"></i>Documentation</h3>
            <p>For more information on how to manage CivicVoice, refer to the documentation files:</p>
            <ul class="list-group list-group-light">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <i class="fas fa-file-alt me-2"></i> <a href="<?php echo APP_URL; ?>/README.md" target="_blank">README.md (Project Overview)</a>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <i class="fas fa-bolt me-2"></i> <a href="<?php echo APP_URL; ?>/QUICKSTART.md" target="_blank">QUICKSTART.md (Setup Guide)</a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- End of Settings Content -->

<?php
require_once __DIR__ . '/admin_footer.php';
?>