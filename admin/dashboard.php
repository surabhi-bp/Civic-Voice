<?php
// --- PHP LOGIC MUST BE FIRST ---
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/Complaint.php'; 

// Admin Auth Check (will redirect to login if session fails)
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ' . APP_URL . '/admin/login.php');
    exit();
}
$adminName = $_SESSION['user_name'] ?? 'Admin'; 

// Get dashboard statistics (KPIs)
$total_complaints = $conn->query("SELECT COUNT(*) as count FROM complaints")->fetch_assoc()['count'];
$pending_complaints = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'pending'")->fetch_assoc()['count'];
$in_progress = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'in_progress'")->fetch_assoc()['count'];
$resolved = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'resolved'")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'citizen'")->fetch_assoc()['count'];

// Get recent complaints
$recent_complaints = $conn->query("
    SELECT c.id, c.title, c.created_at, c.status, u.name as user_name, cat.name as category_name
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    LEFT JOIN categories cat ON c.category_id = cat.id
    ORDER BY c.created_at DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Get category breakdown
$category_breakdown = $conn->query("
    SELECT COALESCE(cat.name, 'Uncategorized') as name, COUNT(c.id) as count
    FROM complaints c
    LEFT JOIN categories cat ON c.category_id = cat.id
    GROUP BY c.category_id
    ORDER BY count DESC
")->fetch_all(MYSQLI_ASSOC);

// Calculate the resolution rate safely
$resolutionRate = $total_complaints > 0 ? round(($resolved / $total_complaints) * 100) : 0;

// Find the maximum category count for progress bar scaling
$max_category_count = max(array_column($category_breakdown, 'count') ?: [1]);


// --- HELPER FUNCTION ---
function getStatusColor($status) {
    $colors = ['pending' => 'warning', 'in_progress' => 'info', 'resolved' => 'success'];
    return $colors[$status] ?? 'primary';
}


// Set page title and include template header (Admin structure)
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Start of Dashboard Content -->
<h1 class="mb-4">Welcome back, <?php echo htmlspecialchars($adminName); ?>! 👋</h1>

<!-- KPI Cards -->
<div class="row g-4 mb-5">
    <!-- Card 1: Total Complaints (Primary) -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card shadow-3 h-100">
            <div class="card-body text-center p-3">
                <i class="fas fa-folder-open fa-2x mb-2 text-primary"></i>
                <h3 class="fw-bold mb-0 text-primary"><?php echo $total_complaints; ?></h3>
                <p class="mb-0 small text-muted">Total</p>
            </div>
        </div>
    </div>
    <!-- Card 2: Pending (Warning) -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card shadow-3 h-100">
            <div class="card-body text-center p-3">
                <i class="fas fa-hourglass-half fa-2x mb-2 text-warning"></i>
                <h3 class="fw-bold mb-0 text-warning"><?php echo $pending_complaints; ?></h3>
                <p class="mb-0 small text-muted">Pending</p>
            </div>
        </div>
    </div>
    <!-- Card 3: In Progress (Info) -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card shadow-3 h-100">
            <div class="card-body text-center p-3">
                <i class="fas fa-tools fa-2x mb-2 text-info"></i>
                <h3 class="fw-bold mb-0 text-info"><?php echo $in_progress; ?></h3>
                <p class="mb-0 small text-muted">In Progress</p>
            </div>
        </div>
    </div>
    <!-- Card 4: Resolved (Success) -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card shadow-3 h-100">
            <div class="card-body text-center p-3">
                <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                <h3 class="fw-bold mb-0 text-success"><?php echo $resolved; ?></h3>
                <p class="mb-0 small text-muted">Resolved</p>
            </div>
        </div>
    </div>
    <!-- Card 5: Total Users (Secondary) -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card shadow-3 h-100">
            <div class="card-body text-center p-3">
                <i class="fas fa-users fa-2x mb-2 text-secondary"></i>
                <h3 class="fw-bold mb-0 text-secondary"><?php echo $total_users; ?></h3>
                <p class="mb-0 small text-muted">Citizens</p>
            </div>
        </div>
    </div>
    <!-- Card 6: Resolution Rate (Dark/Black) -->
    <div class="col-xl-2 col-md-4 col-sm-6">
        <div class="card shadow-3 h-100">
            <div class="card-body text-center p-3">
                <i class="fas fa-percent fa-2x mb-2 text-dark"></i>
                <h3 class="fw-bold mb-0 text-dark"><?php echo $resolutionRate; ?>%</h3>
                <p class="mb-0 small text-muted">Resolution Rate</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Complaints Table -->
    <div class="col-lg-8">
        <div class="card shadow-3 h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Complaints (Last 10)</h5>
                <a href="<?php echo APP_URL; ?>/admin/complaints.php" class="btn btn-sm btn-outline-primary" data-mdb-ripple-init>View All</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_complaints)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No recent activity.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_complaints as $complaint): ?>
                                <tr>
                                    <td>#<?php echo str_pad($complaint['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars(substr($complaint['title'], 0, 30)); ?>...</td>
                                    <td>
                                        <span class="badge badge-<?php echo getStatusColor($complaint['status']); ?> rounded-pill">
                                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($complaint['category_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, H:i', strtotime($complaint['created_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo APP_URL; ?>/admin/complaint-detail.php?id=<?php echo $complaint['id']; ?>" class="btn btn-sm btn-primary" data-mdb-ripple-init>
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Category Breakdown Chart -->
    <div class="col-lg-4">
        <div class="card shadow-3 h-100 p-4">
            <h5 class="mb-4"><i class="fas fa-chart-pie me-2"></i>Complaints by Category</h5>
            <?php foreach ($category_breakdown as $cat): ?>
                <?php $percentage = round(($cat['count'] / $max_category_count) * 100); ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small><strong><?php echo htmlspecialchars($cat['name'] ?? 'Uncategorized'); ?></strong></small>
                        <small class="text-muted"><?php echo $cat['count']; ?> (<?php echo $percentage; ?>%)</small>
                    </div>
                    <!-- MDB Progress Bar -->
                    <div class="progress" style="height: 8px;">
                        <div 
                            class="progress-bar bg-primary" 
                            role="progressbar" 
                            style="width: <?php echo $percentage; ?>%" 
                            aria-valuenow="<?php echo $percentage; ?>" 
                            aria-valuemin="0" 
                            aria-valuemax="100"
                        ></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<!-- End of Dashboard Content -->

<?php
require_once __DIR__ . '/admin_footer.php';
?>