<?php
// --- PHP LOGIC MUST BE FIRST ---
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/Complaint.php'; // Required for Complaint object

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/public/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$complaintObj = new Complaint($conn);
$complaints = $complaintObj->getByUserId($user_id); // Gets all complaints for this user

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get user name for navbar (relies on $user being fetched)
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? $user['name'];

// --- HELPER FUNCTIONS ---
function getStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'in_progress' => 'info',
        'resolved' => 'success'
    ];
    return $colors[$status] ?? 'primary';
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Set page title and include template header
$pageTitle = 'My Dashboard';
require_once __DIR__ . '/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4"><i class="fas fa-tachometer-alt me-2 text-primary"></i>My Dashboard</h1>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-3 h-100">
                <div class="card-body">
                    <h4 class="mb-3">Profile Information</h4>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Member Since:</strong> <?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                    <a href="<?php echo APP_URL; ?>/public/profile.php" class="btn btn-primary btn-sm mt-2" data-mdb-ripple-init>
                        <i class="fas fa-user-edit me-1"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-3 h-100">
                <div class="card-body">
                    <h4 class="mb-3">Your Statistics</h4>
                    <div class="row">
                        <div class="col-4 text-center">
                            <h2 class="text-primary fw-bold"><?php echo count($complaints); ?></h2>
                            <p class="text-muted mb-0">Total</p>
                        </div>
                        <div class="col-4 text-center">
                            <h2 class="text-warning fw-bold"><?php echo count(array_filter($complaints, function($c) { return $c['status'] === 'pending' || $c['status'] === 'in_progress'; })); ?></h2>
                            <p class="text-muted mb-0">Active</p>
                        </div>
                        <div class="col-4 text-center">
                            <h2 class="text-success fw-bold"><?php echo count(array_filter($complaints, function($c) { return $c['status'] === 'resolved'; })); ?></h2>
                            <p class="text-muted mb-0">Resolved</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Your Submitted Complaints</h3>
                <a href="<?php echo APP_URL; ?>/public/submit-complaint.php" class="btn btn-primary btn-sm" data-mdb-ripple-init>
                    <i class="fas fa-plus me-1"></i> New Complaint
                </a>
            </div>

            <?php if (empty($complaints)): ?>
                <div class="text-center p-4">
                    <p class="text-muted mb-3">You haven't submitted any complaints yet.</p>
                    <a href="<?php echo APP_URL; ?>/public/submit-complaint.php" class="btn btn-primary" data-mdb-ripple-init>
                        <i class="fas fa-plus me-2"></i> Report an Issue
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Title</th>
                                <th scope="col">Category</th>
                                <th scope="col">Status</th>
                                <th scope="col">Upvotes</th>
                                <th scope="col">Date</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as $complaint): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo APP_URL; ?>/public/complaint-detail.php?id=<?php echo $complaint['id']; ?>">
                                            <?php echo htmlspecialchars(substr($complaint['title'], 0, 30)); ?>...
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($complaint['category_name'] ?? 'Uncategorized'); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo getStatusColor($complaint['status']); ?> rounded-pill d-inline">
                                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-thumbs-up text-primary me-1"></i> <?php echo $complaint['upvotes_count']; ?>
                                    </td>
                                    <td>
                                        <?php echo formatDate($complaint['created_at']); ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo APP_URL; ?>/public/complaint-detail.php?id=<?php echo $complaint['id']; ?>" class="btn btn-sm btn-outline-primary" data-mdb-ripple-init>
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php
// Include template footer
require_once __DIR__ . '/footer.php';
?>