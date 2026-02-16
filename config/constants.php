<?php
/**
 * Application Constants
 */

define('APP_NAME', 'CivicVoice');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/Civic-voice');
define('UPLOADS_DIR', __DIR__ . '/../public/uploads/');
define('UPLOADS_URL', APP_URL . '/public/uploads/');

// Session timeout (in minutes)
define('SESSION_TIMEOUT', 60);

// JWT Secret (change this in production)
define('JWT_SECRET', 'your_jwt_secret_key_change_in_production');

// Pagination
define('ITEMS_PER_PAGE', 12);

// Complaint statuses
define('STATUS_PENDING', 'pending');
define('STATUS_IN_PROGRESS', 'in_progress');
define('STATUS_RESOLVED', 'resolved');

// User roles
define('ROLE_CITIZEN', 'citizen');
define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_MUNICIPAL_OFFICIAL', 'municipal_official');
define('ROLE_DEPARTMENT_WORKER', 'department_worker');

// Complaint categories
$COMPLAINT_CATEGORIES = array(
    'pothole' => 'Pothole/Road Damage',
    'water' => 'Water Supply Issue',
    'waste' => 'Waste Management',
    'streetlight' => 'Streetlight Out',
    'drainage' => 'Drainage Problem',
    'traffic' => 'Traffic Issue',
    'pollution' => 'Pollution',
    'other' => 'Other'
);

// Email settings
define('MAIL_FROM', 'noreply@civicvoice.local');
define('MAIL_FROM_NAME', 'CivicVoice');

// File MUST end here without ?>