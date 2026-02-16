<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/Complaint.php';

$complaint = new Complaint($conn);

$page = $_GET['page'] ?? 1;
$limit = $_GET['limit'] ?? ITEMS_PER_PAGE;
$offset = ($page - 1) * $limit;

$filters = [];
if (isset($_GET['status'])) $filters['status'] = sanitizeInput($_GET['status']);
if (isset($_GET['category_id'])) $filters['category_id'] = (int)$_GET['category_id'];
if (isset($_GET['ward_id'])) $filters['ward_id'] = (int)$_GET['ward_id'];
if (isset($_GET['search'])) $filters['search'] = sanitizeInput($_GET['search']);
$filters['limit'] = $limit;

$complaints = $complaint->getAllComplaints($filters);

echo json_encode([
    'success' => true,
    'complaints' => $complaints
]);
?>
