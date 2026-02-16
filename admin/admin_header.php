<?php
// Start session and include configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// IMPORTANT: Admin Auth Check. We assume Auth.php has been included previously.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    // Redirect if not logged in or not authorized as admin
    header('Location: ' . APP_URL . '/admin/login.php');
    exit();
}
$adminName = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - CivicVoice Admin</title>
    
    <!-- MDBootstrap & Font Awesome CDN Links -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.css" rel="stylesheet"/>
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/styles.css">
    
    <style>
        /* Sidebar Container */
        .sidebar-nav {
            min-height: calc(100vh - 60px);
            background-color: var(--mdb-body-bg);
            border-right: 1px solid var(--mdb-border-color);
            position: sticky;
            top: 60px;
            padding-top: 0 !important;
        }

        /* Responsive: hide on small screens */
        @media (max-width: 991.98px) {
            .sidebar-nav { display: none !important; }
        }

        /* New perfect sidebar styling */
        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 16px;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            text-decoration: none;
            color: var(--text-color);
            transition: 0.2s ease;
            white-space: nowrap;
        }

        .sidebar-item i {
            font-size: 17px;
            width: 20px;
            text-align: center;
        }

        .sidebar-item:hover {
            background-color: var(--primary-light);
            color: var(--primary-dark);
        }

        /* Logout special styling */
        .sidebar-item.logout {
            color: var(--danger-color);
            font-weight: 600;
        }

        .sidebar-item.logout:hover {
            background-color: rgba(220, 53, 69, 0.15);
        }

        .content-wrapper {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
    </style>
</head>
<body>
    
    <!-- Top Fixed Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-1-strong">
      <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo APP_URL; ?>/admin/dashboard.php">
          <i class="fas fa-user-shield me-2"></i> CivicVoice Admin
        </a>
        <div class="d-flex align-items-center">
            <span class="navbar-text me-3 text-white">Welcome, <?php echo htmlspecialchars($adminName); ?></span>
            <a href="<?php echo APP_URL; ?>/admin/logout.php" class="btn btn-sm btn-outline-light">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
        </div>
      </div>
    </nav>

    <div class="container-fluid">
        <div class="row">

            <!-- NEW FIXED SIDEBAR -->
            <div class="col-lg-2 col-md-3 sidebar-nav p-0 d-md-block">
                <div class="sidebar-menu">

                    <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="sidebar-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="<?php echo APP_URL; ?>/admin/complaints.php" class="sidebar-item">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Manage Complaints</span>
                    </a>

                    <a href="<?php echo APP_URL; ?>/admin/users.php" class="sidebar-item">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </a>

                    <a href="<?php echo APP_URL; ?>/admin/analytics.php" class="sidebar-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Analytics</span>
                    </a>

                    <a href="<?php echo APP_URL; ?>/admin/settings.php" class="sidebar-item">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>

                    <hr>

                    <a href="<?php echo APP_URL; ?>/admin/logout.php" class="sidebar-item logout">
                        <i class="fas fa-door-open"></i>
                        <span>Logout</span>
                    </a>

                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-10 col-md-9 content-wrapper">
                <main class="p-4">
