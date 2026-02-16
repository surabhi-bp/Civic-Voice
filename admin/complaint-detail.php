<?php
// --- PHP LOGIC MUST BE FIRST ---
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/Complaint.php'; // Required for consistency

// Admin Auth Check (moved to the top of the file)
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ' . APP_URL . '/admin/login.php');
    exit();
}

$complaint_id = (int)($_GET['id'] ?? 0);

if (!$complaint_id) {
    header('Location: ' . APP_URL . '/admin/complaints.php');
    exit();
}

// Get complaint details
$stmt = $conn->prepare("
    SELECT c.*, u.name as user_name, u.email as user_email, 
           cat.name as category_name, w.name as ward_name, 
           d.name as department_name, au.name as assigned_name
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    JOIN wards w ON c.ward_id = w.id
    LEFT JOIN departments d ON c.assigned_department_id = d.id
    LEFT JOIN users au ON c.assigned_to_user_id = au.id
    WHERE c.id = ?
");

$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$complaint) {
    header('Location: ' . APP_URL . '/admin/complaints.php');
    exit();
}

// Handle status update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = sanitizeInput($_POST['status'] ?? '');
    $resolution_notes = sanitizeInput($_POST['resolution_notes'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $assigned_to = (int)($_POST['assigned_to'] ?? 0);

    $update_query = "UPDATE complaints SET status = ?, resolution_notes = ?";
    $types = "ss";
    $params = [$new_status, $resolution_notes];

    if ($department_id > 0) {
        $update_query .= ", assigned_department_id = ?";
        $types .= "i";
        $params[] = $department_id;
    } else {
        $update_query .= ", assigned_department_id = NULL"; // Allow unassigning
    }

    if ($assigned_to > 0) {
        $update_query .= ", assigned_to_user_id = ?";
        $types .= "i";
        $params[] = $assigned_to;
    } else {
        $update_query .= ", assigned_to_user_id = NULL"; // Allow unassigning
    }

    // Set resolved_at timestamp if status is changed to resolved
    if ($new_status === 'resolved' && $complaint['status'] !== 'resolved') {
        $update_query .= ", resolved_at = NOW()";
    } elseif ($new_status !== 'resolved' && $complaint['status'] === 'resolved') {
        $update_query .= ", resolved_at = NULL";
    }

    $update_query .= " WHERE id = ?";
    $types .= "i";
    $params[] = $complaint_id;

    $stmt = $conn->prepare($update_query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        // Refresh complaint data via redirect
        header("Location: " . APP_URL . "/admin/complaint-detail.php?id=" . $complaint_id);
        exit();
    } else {
        $message = '<div class="alert alert-danger">Failed to update complaint</div>';
    }
    $stmt->close();
}

// Get comments
$comments = $conn->query("
    SELECT cc.*, u.name as user_name, u.user_type
    FROM complaint_comments cc
    JOIN users u ON cc.user_id = u.id
    WHERE cc.complaint_id = $complaint_id
    ORDER BY cc.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Get departments for assignment
$departments = $conn->query("SELECT id, name FROM departments WHERE is_active = TRUE")->fetch_all(MYSQLI_ASSOC);

// Get admin users for assignment
$admin_users = $conn->query("SELECT u.id, u.name FROM users u JOIN admin_roles ar ON u.id = ar.user_id WHERE ar.role IN ('municipal_official', 'department_worker')")->fetch_all(MYSQLI_ASSOC);

// --- HELPER FUNCTIONS ---
function getStatusColor($status) {
    $colors = ['pending' => 'warning', 'in_progress' => 'info', 'resolved' => 'success'];
    return $colors[$status] ?? 'primary';
}

// Set page title and include template header (Admin structure)
$pageTitle = 'Complaint #' . str_pad($complaint_id, 6, '0', STR_PAD_LEFT);
require_once __DIR__ . '/admin_header.php';
?>

<a href="<?php echo APP_URL; ?>/admin/complaints.php" class="btn btn-outline-secondary btn-sm mb-3" data-mdb-ripple-init>
    <i class="fas fa-arrow-left me-2"></i>Back to Complaints
</a>

<?php echo $message; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-3 mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h2 class="mb-0">#<?php echo str_pad($complaint['id'], 6, '0', STR_PAD_LEFT); ?>: <?php echo htmlspecialchars($complaint['title']); ?></h2>
                        <small class="text-muted">Reported by: <?php echo htmlspecialchars($complaint['user_name']); ?></small>
                    </div>
                    <span class="badge badge-<?php echo getStatusColor($complaint['status']); ?> fs-6 py-2 px-3">
                        <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                    </span>
                </div>

                <hr class="mt-0">

                <p class="mb-3"><strong>Description:</strong></p>
                <p style="font-size: 1.05rem;"><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></p>

                <?php if ($complaint['photo_url']): ?>
                    <h5 class="mt-4 mb-2"><i class="fas fa-image me-2"></i>Attached Photo</h5>
                    <img src="<?php echo htmlspecialchars($complaint['photo_url']); ?>" alt="Complaint photo" class="img-fluid rounded shadow-1-strong mb-3" style="max-height: 400px; object-fit: cover;">
                <?php endif; ?>

                <h5 class="mt-4 mb-2"><i class="fas fa-info-circle me-2"></i>Metadata</h5>
                <div class="row">
                    <div class="col-md-6"><p class="mb-1"><strong>Category:</strong> <?php echo htmlspecialchars($complaint['category_name'] ?? 'N/A'); ?></p></div>
                    <div class="col-md-6"><p class="mb-1"><strong>Ward:</strong> <?php echo htmlspecialchars($complaint['ward_name']); ?></p></div>
                    <div class="col-md-6"><p class="mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($complaint['address']); ?></p></div>
                    <div class="col-md-6"><p class="mb-1"><strong>Submitted:</strong> <?php echo date('M d, Y H:i', strtotime($complaint['created_at'])); ?></p></div>
                    <div class="col-md-6"><p class="mb-1"><strong>Upvotes:</strong> <i class="fas fa-thumbs-up text-primary me-1"></i> <?php echo $complaint['upvotes']; ?></p></div>
                </div>

                <?php if ($complaint['resolution_notes']): ?>
                    <div class="alert alert-info mt-4">
                        <strong><i class="fas fa-clipboard-check me-1"></i> Resolution Notes:</strong><br>
                        <?php echo nl2br(htmlspecialchars($complaint['resolution_notes'])); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-3">
            <div class="card-body p-4">
                <h4 class="mb-4">Comments (<?php echo count($comments); ?>)</h4>
                <?php foreach ($comments as $comment): ?>
                    <div class="d-flex mb-4 pb-3 border-bottom">
                        <div class="flex-shrink-0 me-3">
                            <i class="fas fa-comment-alt fa-2x text-muted"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-0">
                                <?php echo htmlspecialchars($comment['user_name']); ?>
                                <?php if ($comment['user_type'] === 'admin'): ?>
                                    <span class="badge bg-danger ms-1">Official</span>
                                <?php endif; ?>
                            </h6>
                            <small class="text-muted"><i class="fas fa-clock me-1"></i> <?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></small>
                            <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($comments)): ?>
                    <p class="text-center text-muted">No comments yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <form method="POST" class="card shadow-3" style="position: sticky; top: 80px;">
            <div class="card-body p-4">
                <h4 class="mb-3"><i class="fas fa-edit me-2"></i>Management & Assignment</h4>

                <div class="form-group mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="pending" <?php echo $complaint['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo $complaint['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $complaint['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Assign to Department</label>
                    <select name="department_id" class="form-select">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" <?php echo $complaint['assigned_department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Currently: <?php echo htmlspecialchars($complaint['department_name'] ?? 'N/A'); ?></small>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Assign to Staff</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">Select Staff Member</option>
                        <?php foreach ($admin_users as $admin): ?>
                            <option value="<?php echo $admin['id']; ?>" <?php echo $complaint['assigned_to_user_id'] == $admin['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($admin['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Currently: <?php echo htmlspecialchars($complaint['assigned_name'] ?? 'N/A'); ?></small>
                </div>

                <div data-mdb-input-init class="form-outline mb-4">
                    <textarea class="form-control" name="resolution_notes" id="resolution_notes" rows="3"><?php echo htmlspecialchars($complaint['resolution_notes'] ?? ''); ?></textarea>
                    <label class="form-label" for="resolution_notes">Resolution Notes</label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save me-2"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/admin_footer.php';
?>