<?php
// --- PHP LOGIC MUST BE FIRST ---
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// IMPORTANT: Admin Auth Check (moved to the top of the file, assuming admin_header.php repeats it)
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ' . APP_URL . '/admin/login.php');
    exit();
}

$status_filter = $_GET['status'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$query = "SELECT c.*, u.name as user_name, w.name as ward_name, cat.name as category_name, COUNT(DISTINCT cu.id) as upvotes_count
          FROM complaints c
          JOIN users u ON c.user_id = u.id
          JOIN wards w ON c.ward_id = w.id
          LEFT JOIN categories cat ON c.category_id = cat.id
          LEFT JOIN complaint_upvotes cu ON c.id = cu.complaint_id
          WHERE 1=1";

if ($status_filter) {
    // NOTE: Using prepared statements is better practice than real_escape_string
    $query .= " AND c.status = '" . $conn->real_escape_string($status_filter) . "'";
}

$query .= " GROUP BY c.id ORDER BY c.created_at DESC LIMIT $offset, $limit";

$complaints = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

// Get total count
$count_query = "SELECT COUNT(DISTINCT c.id) as total FROM complaints c WHERE 1=1";
if ($status_filter) {
    $count_query .= " AND c.status = '" . $conn->real_escape_string($status_filter) . "'";
}
$total = $conn->query($count_query)->fetch_assoc()['total'];
$pages = ceil($total / $limit);

// Set page title and include template header (Admin structure)
$pageTitle = 'Manage Complaints';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Start of Complaint Management Content -->
<h1 class="mb-4"><i class="fas fa-clipboard-check me-2 text-primary"></i>Manage Complaints</h1>

<div class="card shadow-3 mb-4">
    <div class="card-body p-4">
        <!-- Filters (using MDBoostrap Row/Col for responsiveness) -->
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div class="d-flex" style="gap: 10px;">
                <!-- Filter Buttons -->
                <a href="<?php echo APP_URL; ?>/admin/complaints.php" class="btn btn-sm <?php echo !$status_filter ? 'btn-primary' : 'btn-outline-primary'; ?>" data-mdb-ripple-init>
                    All
                </a>
                <a href="<?php echo APP_URL; ?>/admin/complaints.php?status=pending" class="btn btn-sm <?php echo $status_filter === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>" data-mdb-ripple-init>
                    Pending
                </a>
                <a href="<?php echo APP_URL; ?>/admin/complaints.php?status=in_progress" class="btn btn-sm <?php echo $status_filter === 'in_progress' ? 'btn-info' : 'btn-outline-info'; ?>" data-mdb-ripple-init>
                    In Progress
                </a>
                <a href="<?php echo APP_URL; ?>/admin/complaints.php?status=resolved" class="btn btn-sm <?php echo $status_filter === 'resolved' ? 'btn-success' : 'btn-outline-success'; ?>" data-mdb-ripple-init>
                    Resolved
                </a>
            </div>
            <span class="text-muted mt-2 mt-sm-0">Total: <?php echo $total; ?> complaints</span>
        </div>

        <!-- Complaints Table -->
        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead class="table-light">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Title</th>
                        <th scope="col">Citizen</th>
                        <th scope="col">Ward</th>
                        <th scope="col">Status</th>
                        <th scope="col"><i class="fas fa-thumbs-up"></i> Upvotes</th>
                        <th scope="col">Date</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($complaints)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No complaints found in this status queue.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($complaints as $complaint): ?>
                            <tr>
                                <td>#<?php echo str_pad($complaint['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <?php echo htmlspecialchars(substr($complaint['title'], 0, 30)); ?>...
                                </td>
                                <td>
                                    <?php echo htmlspecialchars(substr($complaint['user_name'], 0, 20)); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars(substr($complaint['ward_name'], 0, 15)); ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo getStatusColor($complaint['status']); ?> rounded-pill d-inline">
                                        <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $complaint['upvotes_count']; ?>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($complaint['created_at'])); ?>
                                </td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>/admin/complaint-detail.php?id=<?php echo $complaint['id']; ?>" class="btn btn-sm btn-primary" data-mdb-ripple-init>
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <nav aria-label="Page navigation example" class="d-flex justify-content-center">
        <ul class="pagination mb-0">
            <!-- Previous Button -->
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo APP_URL; ?>/admin/complaints.php?page=<?php echo $page - 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>" aria-disabled="<?php echo $page <= 1 ? 'true' : 'false'; ?>">
                    Previous
                </a>
            </li>
            
            <!-- Page Info -->
            <li class="page-item active" aria-current="page">
                <a class="page-link" href="#">Page <?php echo $page; ?> of <?php echo $pages; ?></a>
            </li>

            <!-- Next Button -->
            <li class="page-item <?php echo $page >= $pages ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?php echo APP_URL; ?>/admin/complaints.php?page=<?php echo $page + 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>" aria-disabled="<?php echo $page >= $pages ? 'true' : 'false'; ?>">
                    Next
                </a>
            </li>
        </ul>
    </nav>
</div>
<!-- End of Complaint Management Content -->

<?php
require_once __DIR__ . '/admin_footer.php';
?>

<?php
// Function retained at the end for compatibility, ideally this is in a utility file.
function getStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'in_progress' => 'info',
        'resolved' => 'success'
    ];
    return $colors[$status] ?? 'primary';
}
?>