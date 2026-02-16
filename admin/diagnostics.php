<?php
/**
 * Admin Setup Diagnostic Tool
 * This helps you understand and fix admin authentication issues
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

$diagnostics = [];

// 1. Check if users table exists and has data
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
$diagnostics['users_table'] = [
    'status' => $row['count'] > 0 ? 'OK' : 'EMPTY',
    'total_users' => $row['count'],
    'message' => $row['count'] > 0 ? "Database has {$row['count']} users" : "No users in database"
];

// 2. Check admin users
$result = $conn->query("
    SELECT u.id, u.name, u.email, u.user_type, ar.role 
    FROM users u
    LEFT JOIN admin_roles ar ON u.id = ar.user_id
    WHERE u.user_type = 'admin'
");
$admin_users = $result->fetch_all(MYSQLI_ASSOC);
$diagnostics['admin_users'] = [
    'status' => count($admin_users) > 0 ? 'OK' : 'MISSING',
    'count' => count($admin_users),
    'users' => $admin_users,
    'message' => count($admin_users) > 0 ? "Found " . count($admin_users) . " admin user(s)" : "No admin users found - you need to create one"
];

// 3. Check admin_roles table
$result = $conn->query("SELECT COUNT(*) as count FROM admin_roles");
$row = $result->fetch_assoc();
$diagnostics['admin_roles'] = [
    'status' => 'OK',
    'total_roles' => $row['count'],
    'message' => "admin_roles table has {$row['count']} entries"
];

// 4. Check citizen users
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'citizen'");
$row = $result->fetch_assoc();
$diagnostics['citizen_users'] = [
    'status' => 'OK',
    'count' => $row['count'],
    'message' => "Database has {$row['count']} citizen user(s)"
];

// 5. Try to authenticate test admin (if exists)
$test_email = 'admin@civicvoice.local';
$result = $conn->query("SELECT id, email, user_type FROM users WHERE email = '$test_email' AND user_type = 'admin'");
$test_user = $result->fetch_assoc();
$diagnostics['test_admin'] = [
    'status' => $test_user ? 'FOUND' : 'NOT_FOUND',
    'message' => $test_user ? "Test admin account exists" : "Test admin account not found"
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup Diagnostics - CivicVoice</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.css" rel="stylesheet"/>
    <style>
        .diagnostic-item {
            border-left: 4px solid #ddd;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            border-radius: 0.25rem;
        }
        .status-ok {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .status-warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .status-error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .status-missing {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .badge-status {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid justify-content-between">
            <a class="navbar-brand text-white" href="<?php echo APP_URL; ?>/public/index.php">
                <i class="fas fa-landmark me-2"></i> CivicVoice
            </a>
            <div>
                <a href="<?php echo APP_URL; ?>/admin/create-admin.php" class="btn btn-outline-light btn-sm me-2">
                    Create Admin User
                </a>
                <a href="<?php echo APP_URL; ?>/admin/login.php" class="btn btn-outline-light btn-sm">
                    Admin Login
                </a>
            </div>
        </div>
    </nav>

    <main class="content">
        <div class="container my-5">
            <div style="max-width: 900px; margin: 2rem auto;">
                <h1 class="mb-4">
                    <i class="fas fa-stethoscope me-2 text-danger"></i>
                    Admin Setup Diagnostics
                </h1>

                <!-- Database Status -->
                <div class="card shadow-3 mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-database me-2"></i>Database Status</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($diagnostics as $key => $diag): ?>
                            <div class="diagnostic-item status-<?php echo strtolower($diag['status']); ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-2">
                                            <strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?></strong>
                                        </h6>
                                        <p class="mb-0"><?php echo $diag['message']; ?></p>
                                        
                                        <?php if ($key === 'admin_users' && !empty($diag['users'])): ?>
                                            <div class="mt-2 small">
                                                <p class="mb-1"><strong>Existing Admin Users:</strong></p>
                                                <ul class="mb-0">
                                                    <?php foreach ($diag['users'] as $user): ?>
                                                        <li>
                                                            <strong><?php echo htmlspecialchars($user['name']); ?></strong> 
                                                            (<?php echo htmlspecialchars($user['email']); ?>) 
                                                            - Role: <span class="badge bg-primary"><?php echo htmlspecialchars($user['role'] ?? 'N/A'); ?></span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge badge-status bg-<?php 
                                        echo $diag['status'] === 'OK' || $diag['status'] === 'FOUND' ? 'success' : 
                                             ($diag['status'] === 'EMPTY' || $diag['status'] === 'MISSING' ? 'danger' : 'warning');
                                    ?>" style="font-size: 0.85rem;">
                                        <?php echo $diag['status']; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="card shadow-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-arrow-right me-2"></i>Next Steps</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($diagnostics['admin_users']['status'] === 'MISSING'): ?>
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>No admin users found!</strong> You need to create one to access the admin panel.
                            </div>
                            
                            <h6 class="mb-3">Step 1: Create Your First Admin User</h6>
                            <p>Click the button below to create an admin account:</p>
                            <a href="<?php echo APP_URL; ?>/admin/create-admin.php" class="btn btn-danger btn-lg mb-3">
                                <i class="fas fa-user-plus me-2"></i> Create Admin User
                            </a>
                            
                            <h6 class="mb-3 mt-4">Step 2: Login</h6>
                            <p>After creating an admin user, use those credentials to login:</p>
                            <a href="<?php echo APP_URL; ?>/admin/login.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> Go to Admin Login
                            </a>
                        <?php else: ?>
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Admin users found!</strong> You should be able to login with one of the existing admin accounts.
                            </div>
                            
                            <p>If you forgot your password or need another admin account, you can:</p>
                            <a href="<?php echo APP_URL; ?>/admin/create-admin.php" class="btn btn-danger">
                                <i class="fas fa-user-plus me-2"></i> Create Another Admin User
                            </a>
                            or
                            <a href="<?php echo APP_URL; ?>/admin/login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i> Go to Admin Login
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Troubleshooting -->
                <div class="card shadow-3 mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Troubleshooting</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-3">Common Issues:</h6>
                        <ul>
                            <li><strong>Login fails with "Invalid admin credentials"</strong>
                                <ul>
                                    <li>Make sure you're using the correct email and password</li>
                                    <li>Admin accounts must have both <code>user_type = 'admin'</code> AND an entry in <code>admin_roles</code> table</li>
                                    <li>Check the "Existing Admin Users" section above to verify your account exists</li>
                                </ul>
                            </li>
                            <li><strong>Can't find the create admin page</strong>
                                <ul>
                                    <li>Make sure you're accessing: <code><?php echo APP_URL; ?>/admin/create-admin.php</code></li>
                                </ul>
                            </li>
                            <li><strong>Database connection issues</strong>
                                <ul>
                                    <li>Check that your database is running and accessible</li>
                                    <li>Verify database credentials in <code>config/database.php</code></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.js"></script>
</body>
</html>
