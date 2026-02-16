<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/Complaint.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$complaint_id = (int)($_POST['complaint_id'] ?? 0);
$comment_text = sanitizeInput($_POST['comment'] ?? '');

if (!$complaint_id || empty($comment_text)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$complaint = new Complaint($conn);
$result = $complaint->addComment($complaint_id, $_SESSION['user_id'], $comment_text);

echo json_encode($result);
?>
