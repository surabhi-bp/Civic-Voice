<?php
// --- PHP LOGIC MUST BE FIRST ---
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// IMPORTANT: This file now uses the admin_header.php template which handles the
// session check and redirect based on $_SESSION['admin_id'].
// We require the Auth class to define the required methods.
require_once __DIR__ . '/../src/Auth.php'; 

// --- FETCH ANALYTICS DATA (UNCHANGED) ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ' . APP_URL . '/admin/login.php');
    exit();
}

$stats = [
    'total_complaints' => $conn->query("SELECT COUNT(*) as count FROM complaints")->fetch_assoc()['count'],
    'total_resolved' => $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'resolved'")->fetch_assoc()['count'],
    // Calculate AVG resolution time, ensuring it handles NULL gracefully
    'avg_resolution_time' => $conn->query("SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours FROM complaints WHERE resolved_at IS NOT NULL")->fetch_assoc()['avg_hours'],
    'total_citizens' => $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'citizen'")->fetch_assoc()['count'],
];

// Calculate resolution rate safely
$resolutionRate = $stats['total_complaints'] > 0 
                  ? round(($stats['total_resolved'] / $stats['total_complaints']) * 100) 
                  : 0;

// Get complaints by ward
$by_ward = $conn->query("
    SELECT w.name, COUNT(c.id) as count
    FROM complaints c
    JOIN wards w ON c.ward_id = w.id
    GROUP BY c.ward_id
    ORDER BY count DESC
")->fetch_all(MYSQLI_ASSOC);

// Get resolution time by category
$by_category = $conn->query("
    SELECT COALESCE(cat.name, 'Uncategorized') as category, 
           COUNT(c.id) as count,
           AVG(TIMESTAMPDIFF(HOUR, c.created_at, c.resolved_at)) as avg_hours
    FROM complaints c
    LEFT JOIN categories cat ON c.category_id = cat.id
    GROUP BY c.category_id
    ORDER BY count DESC
")->fetch_all(MYSQLI_ASSOC);

// Find the maximum count for progress bar scaling
$max_ward_count = max(array_column($by_ward, 'count') ?: [1]);
$max_category_count = max(array_column($by_category, 'count') ?: [1]);

// Set page title and include template header (Admin structure)
$pageTitle = 'Analytics & Reports';
require_once __DIR__ . '/admin_header.php';
?>

<style>
    .metric-card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        min-height: 180px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
    }
    
    .metric-card .card-body {
        position: relative;
        padding: 1.5rem;
    }
    
    .metric-card .card-title {
        font-size: 0.95rem;
        font-weight: 600;
        opacity: 0.9;
        margin-bottom: 1rem;
    }
    
    .metric-card .display-4 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0;
    }
    
    .metric-card .icon-box {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        font-size: 2rem;
        opacity: 0.2;
    }
    
    /* Color Scheme - Professional & Modern */
    .metric-resolution {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .metric-time {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
    }
    
    .metric-total {
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        color: white;
    }
    
    .metric-citizens {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    .analytics-section-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1.5rem;
    }
    
    .analytics-section-title i {
        color: #3b82f6;
        margin-right: 0.5rem;
    }
</style>

<!-- Start of Analytics Content -->
<h1 class="mb-5"><i class="fas fa-chart-bar me-2 text-primary"></i>Analytics & Reports</h1>

<!-- Key Metrics Cards -->
<div class="row g-4 mb-5">
    <!-- Metric 1: Resolution Rate -->
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-3 metric-card metric-resolution">
            <div class="card-body">
                <div class="icon-box">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h5 class="card-title">Resolution Rate</h5>
                <h1 class="display-4"><?php echo $resolutionRate; ?>%</h1>
            </div>
        </div>
    </div>
    <!-- Metric 2: Avg Resolution Time -->
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-3 metric-card metric-time">
            <div class="card-body">
                <div class="icon-box">
                    <i class="fas fa-clock"></i>
                </div>
                <h5 class="card-title">Avg Resolution Time</h5>
                <h1 class="display-4"><?php echo round($stats['avg_resolution_time'] ?? 0); ?> hrs</h1>
            </div>
        </div>
    </div>
    <!-- Metric 3: Total Complaints -->
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-3 metric-card metric-total">
            <div class="card-body">
                <div class="icon-box">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h5 class="card-title">Total Complaints</h5>
                <h1 class="display-4"><?php echo $stats['total_complaints']; ?></h1>
            </div>
        </div>
    </div>
    <!-- Metric 4: Total Citizens -->
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-3 metric-card metric-citizens">
            <div class="card-body">
                <div class="icon-box">
                    <i class="fas fa-users"></i>
                </div>
                <h5 class="card-title">Active Citizens</h5>
                <h1 class="display-4"><?php echo $stats['total_citizens']; ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Complaints by Ward Section -->
    <div class="col-lg-6">
        <div class="card shadow-3 h-100">
            <div class="card-body p-4">
                <h3 class="analytics-section-title"><i class="fas fa-city"></i>Complaints by Ward</h3>
                <?php foreach ($by_ward as $ward): ?>
                    <?php $percentage = round(($ward['count'] / $max_ward_count) * 100); ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong style="color: #1f2937;"><?php echo htmlspecialchars($ward['name']); ?></strong>
                            <span class="badge bg-light text-dark" style="font-size: 0.85rem;"><?php echo $ward['count']; ?> complaints</span>
                        </div>
                        <!-- Progress Bar -->
                        <div class="progress" style="height: 8px; background-color: #e5e7eb; border-radius: 4px;">
                            <div 
                                class="progress-bar" 
                                role="progressbar" 
                                style="width: <?php echo $percentage; ?>%; background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%); border-radius: 4px;" 
                                aria-valuenow="<?php echo $percentage; ?>" 
                                aria-valuemin="0" 
                                aria-valuemax="100"
                            ></div>
                        </div>
                        <small style="color: #6b7280; display: block; margin-top: 0.3rem;"><?php echo $percentage; ?>% of all complaints</small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Complaints & Resolution Time by Category Section -->
    <div class="col-lg-6">
        <div class="card shadow-3 h-100">
            <div class="card-body p-4">
                <h3 class="analytics-section-title"><i class="fas fa-chart-pie"></i>Resolution Time by Category</h3>
                <div class="table-responsive">
                    <table class="table align-middle" style="margin-bottom: 0;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e5e7eb; background-color: #f9fafb;">
                                <th scope="col" style="color: #6b7280; font-weight: 600; padding: 1rem 0.5rem;">Category</th>
                                <th scope="col" style="color: #6b7280; font-weight: 600; padding: 1rem 0.5rem;">Count</th>
                                <th scope="col" style="color: #6b7280; font-weight: 600; padding: 1rem 0.5rem;">Avg Hrs</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($by_category as $cat): ?>
                                <tr style="border-bottom: 1px solid #f3f4f6;">
                                    <td style="padding: 1rem 0.5rem; color: #1f2937;">
                                        <strong><?php echo htmlspecialchars($cat['category']); ?></strong>
                                    </td>
                                    <td style="padding: 1rem 0.5rem;">
                                        <span class="badge" style="background-color: #dbeafe; color: #0c4a6e; font-weight: 600;">
                                            <?php echo $cat['count']; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem 0.5rem; color: #1f2937;">
                                        <?php 
                                            $avg_hours = round($cat['avg_hours'] ?? 0, 1);
                                            if ($avg_hours > 0) {
                                                echo '<span style="background-color: #fef3c7; color: #92400e; padding: 0.3rem 0.6rem; border-radius: 4px; font-weight: 600;">' . $avg_hours . ' hrs</span>';
                                            } else {
                                                echo '<span style="color: #9ca3af;">N/A</span>';
                                            }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End of Analytics Content -->

<?php
require_once __DIR__ . '/admin_footer.php';
?>