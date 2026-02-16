<?php
// Start session on every page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config files (Go up one level from /public/)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// Check for user login
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';

// This variable will be set by each page individually
if (!isset($pageTitle)) {
    $pageTitle = 'CivicVoice';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - CivicVoice</title>
    
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.min.css"
      rel="stylesheet"
    />
    
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/styles.css">
    
    <style>
        .bottom-nav {
            display: none; /* Hidden on desktop */
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            /* Use MDB variables for background/colors */
            background-color: var(--mdb-body-bg);
            border-top: 1px solid var(--mdb-border-color);
            box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
        }
        @media (max-width: 767.98px) {
            .bottom-nav {
                display: flex;
                justify-content: space-around;
            }
            main.content {
                padding-bottom: 70px; /* Add padding to body */
            }
        }
        .bottom-nav-item {
            flex-grow: 1;
            text-align: center;
            padding: 10px 0;
            font-size: 0.75rem;
            color: var(--mdb-secondary-color);
            text-decoration: none;
        }
        .bottom-nav-item .bottom-nav-icon {
            font-size: 1.25rem;
            display: block;
            margin: 0 auto 4px;
        }
        .bottom-nav-item.active {
            color: var(--mdb-primary-color);
        }
    </style>
</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-light bg-body-tertiary sticky-top shadow-0">
      <div class="container">
        
        <a class="navbar-brand" href="<?php echo APP_URL; ?>/public/index.php">
            <i class="fas fa-landmark me-2 text-primary"></i>
            <strong>CivicVoice</strong>
        </a>
        
        <button
          data-mdb-collapse-init
          class="navbar-toggler"
          type="button"
          data-mdb-target="#navbarContent"
          aria-controls="navbarContent"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <i class="fas fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/public/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/public/complaints.php">Complaints</a>
                </li>
            </ul>

            <ul class="navbar-nav d-flex flex-row align-items-center">
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown">
                      <a
                        data-mdb-dropdown-init
                        class="nav-link dropdown-toggle"
                        href="#"
                        id="navbarDropdown"
                        role="button"
                        aria-expanded="false"
                      >
                        <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($userName); ?>
                      </a>
                      <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/public/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>My Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/public/profile.php"><i class="fas fa-user-edit me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/public/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                      </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item me-2 mb-2 mb-lg-0">
                      <a class="btn btn-outline-primary" data-mdb-ripple-init href="<?php echo APP_URL; ?>/public/login.php">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="btn btn-primary" data-mdb-ripple-init href="<?php echo APP_URL; ?>/public/signup.php">
                        <i class="fas fa-user-plus me-1"></i> Sign Up
                      </a>
                    </li>
                <?php endif; ?>
                <li class="nav-item ms-2">
                    <button id="theme-toggle" class="btn btn-outline-secondary btn-floating">
                        <i class="fas fa-moon" id="theme-icon-moon"></i>
                        <i class="fas fa-sun" id="theme-icon-sun" style="display: none;"></i>
                    </button>
                </li>
            </ul>
        </div>
      </div>
    </nav>
    <main class="content">
        <div class="container">