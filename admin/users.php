<?php
// --- PHP LOGIC MUST BE FIRST ---
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// Admin Auth Check (moved to the top of the file, assuming admin_header.php handles session validity)
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ' . APP_URL . '/admin/login.php');
    exit();
}

$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get all users
$users = $conn->query("
    SELECT u.*, COUNT(DISTINCT c.id) as complaint_count
    FROM users u
    LEFT JOIN complaints c ON u.id = c.user_id
    WHERE u.user_type = 'citizen'
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT $offset, $limit
")->fetch_all(MYSQLI_ASSOC);

// Get total count
$total = $conn->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'citizen'")->fetch_assoc()['total'];
$pages = ceil($total / $limit);

// Set page title and include template header (Admin structure)
$pageTitle = 'Manage Citizens';
require_once __DIR__ . '/admin_header.php';
?>

<!-- Start of User Management Content -->
<h1 class="mb-4"><i class="fas fa-users me-2 text-primary"></i>Manage Citizens</h1>

<div class="card shadow-3 mb-4">
    <div class="card-body p-4">
        <h5 class="mb-3 text-muted">Total Citizens: <?php echo $total; ?></h5>
        
        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Complaints</th>
                        <th scope="col">Status</th>
                        <th scope="col">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No citizen accounts found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><span class="badge bg-primary rounded-pill"><?php echo $user['complaint_count']; ?></span></td>
                                <td>
                                    <!-- Status Badge -->
                                    <span class="badge <?php echo $user['is_blocked'] ? 'bg-danger' : 'bg-success'; ?> rounded-pill">
                                        <?php echo $user['is_blocked'] ? 'Blocked' : 'Active'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<nav aria-label="Page navigation example" class="d-flex justify-content-center">
    <ul class="pagination mb-0">
        <!-- Previous Button -->
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo APP_URL; ?>/admin/users.php?page=<?php echo $page - 1; ?>" aria-disabled="<?php echo $page <= 1 ? 'true' : 'false'; ?>">
                Previous
            </a>
        </li>
        
        <!-- Page Info -->
        <li class="page-item active" aria-current="page">
            <a class="page-link" href="#">Page <?php echo $page; ?> of <?php echo $pages; ?></a>
        </li>

        <!-- Next Button -->
        <li class="page-item <?php echo $page >= $pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo APP_URL; ?>/admin/users.php?page=<?php echo $page + 1; ?>" aria-disabled="<?php echo $page >= $pages ? 'true' : 'false'; ?>">
                Next
            </a>
        </li>
    </ul>
</nav>

<!-- End of User Management Content -->

<?php
require_once __DIR__ . '/admin_footer.php';
?>