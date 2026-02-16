<?php
/**
 * Admin User Creation Script
 * This script allows you to create an admin user for testing/setup purposes
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'municipal_official';

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email already exists';
        } else {
            // Create admin user
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $user_type = 'admin';
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, user_type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password_hash, $user_type);
            
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                
                // Create admin role
                $stmt = $conn->prepare("INSERT INTO admin_roles (user_id, role) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $role);
                
                if ($stmt->execute()) {
                    $message = "Admin user created successfully! Email: <strong>$email</strong><br>You can now login to the admin panel.";
                } else {
                    $error = "Failed to assign admin role: " . $conn->error;
                    // Delete the user we just created
                    $conn->prepare("DELETE FROM users WHERE id = ?")->bind_param("i", $user_id)->execute();
                }
            } else {
                $error = "Failed to create admin user: " . $conn->error;
            }
        }
    }
}

// Get current admin users for reference
$admin_users = [];
$result = $conn->query("
    SELECT u.id, u.name, u.email, ar.role 
    FROM users u
    INNER JOIN admin_roles ar ON u.id = ar.user_id
    WHERE u.user_type = 'admin'
");

if ($result) {
    $admin_users = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User - CivicVoice</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.css" rel="stylesheet"/>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid justify-content-between">
            <a class="navbar-brand text-white" href="<?php echo APP_URL; ?>/public/index.php">
                <i class="fas fa-landmark me-2"></i> CivicVoice
            </a>
            <a href="<?php echo APP_URL; ?>/admin/login.php" class="btn btn-outline-light btn-sm">
                Admin Login
            </a>
        </div>
    </nav>

    <main class="content">
        <div class="container my-5">
            <div style="max-width: 600px; margin: 3rem auto;">
                <div class="card shadow-5">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4"><i class="fas fa-user-shield me-2 text-danger"></i>Create Admin User</h2>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div data-mdb-input-init class="form-outline mb-4">
                                <input type="text" id="name" name="name" class="form-control" required />
                                <label class="form-label" for="name">Full Name</label>
                            </div>

                            <div data-mdb-input-init class="form-outline mb-4">
                                <input type="email" id="email" name="email" class="form-control" required />
                                <label class="form-label" for="email">Email</label>
                            </div>

                            <div data-mdb-input-init class="form-outline mb-4">
                                <input type="password" id="password" name="password" class="form-control" required />
                                <label class="form-label" for="password">Password</label>
                            </div>

                            <div data-mdb-input-init class="form-outline mb-4">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required />
                                <label class="form-label" for="confirm_password">Confirm Password</label>
                            </div>

                            <div class="mb-4">
                                <label class="form-label" for="role">Admin Role</label>
                                <select id="role" name="role" class="form-select">
                                    <option value="super_admin">Super Admin</option>
                                    <option value="municipal_official" selected>Municipal Official</option>
                                    <option value="department_worker">Department Worker</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-danger btn-block btn-lg mb-4" data-mdb-ripple-init>
                                <i class="fas fa-user-plus me-1"></i> Create Admin User
                            </button>
                        </form>

                        <hr class="my-4">

                        <h5 class="mb-3">Existing Admin Users</h5>
                        <?php if (!empty($admin_users)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($admin_users as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($user['role']); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No admin users exist yet. Create one above.</p>
                        <?php endif; ?>

                        <p class="text-center mt-4">
                            <a href="<?php echo APP_URL; ?>/admin/login.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i> Back to Admin Login
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.js"></script>
</body>
</html>
