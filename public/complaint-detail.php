<?php
// --- PHP LOGIC MUST BE FIRST ---
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/Complaint.php';

$isLoggedIn = isset($_SESSION['user_id']);
$complaint_id = (int)($_GET['id'] ?? 0);

if (!$complaint_id) {
    header('Location: ' . APP_URL . '/public/complaints.php');
    exit();
}

$complaintObj = new Complaint($conn);
$complaint = $complaintObj->getById($complaint_id);

if (!$complaint) {
    header('Location: ' . APP_URL . '/public/complaints.php');
    exit();
}

// Check if user upvoted
$userUpvoted = false;
if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT id FROM complaint_upvotes WHERE complaint_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $complaint_id, $_SESSION['user_id']);
    $stmt->execute();
    $userUpvoted = $stmt->get_result()->num_rows > 0;
    $stmt->close(); // Close statement immediately after use
}

$comments = $complaintObj->getComments($complaint_id);

// --- HELPER FUNCTIONS ---
function getStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'in_progress' => 'info',
        'resolved' => 'success'
    ];
    return $colors[$status] ?? 'primary';
}

function getPriorityColor($priority) {
    $colors = [
        'low' => 'info',
        'medium' => 'warning',
        'high' => 'danger'
    ];
    return $colors[$priority] ?? 'primary';
}

// Set page title and include template header
$pageTitle = htmlspecialchars($complaint['title']);
require_once __DIR__ . '/header.php';
?>

<div class="container my-5">
    <button onclick="window.history.back()" class="btn btn-outline-primary btn-sm mb-3" data-mdb-ripple-init>
        <i class="fas fa-arrow-left me-2"></i>Back
    </button>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-3 mb-4">
                <div class="card-body p-4">
                    <h1 class="mb-3"><?php echo htmlspecialchars($complaint['title']); ?></h1>
                    
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge badge-<?php echo getStatusColor($complaint['status']); ?> me-2">
                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                        </span>
                        <span class="badge bg-primary me-2">
                            <?php echo htmlspecialchars($complaint['category_name']); ?>
                        </span>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> <?php echo htmlspecialchars($complaint['created_at']); ?>
                        </small>
                    </div>

                    <?php if ($complaint['photo_url']): ?>
                        <img src="<?php echo htmlspecialchars($complaint['photo_url']); ?>" alt="Complaint photo" class="img-fluid rounded mb-3" style="width: 100%;">
                    <?php endif; ?>

                    <p style="font-size: 1.1rem;"><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></p>

                    <hr class="my-4">
                    
                    <div>
                        <h5><i class="fas fa-map-marker-alt me-2"></i>Location</h5>
                        <p class="mb-1"><?php echo htmlspecialchars($complaint['address']); ?></p>
                        <small class="text-muted">Ward: <?php echo htmlspecialchars($complaint['ward_name']); ?></small>
                    </div>

                    <?php if ($complaint['assigned_department_id']): ?>
                        <div class="mt-3">
                            <h5><i class="fas fa-building me-2"></i>Assigned To</h5>
                            <p class="mb-0"><?php echo htmlspecialchars($complaint['department_name']); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($complaint['resolution_notes'] && $complaint['status'] === 'resolved'): ?>
                        <div class="alert alert-success mt-4">
                            <strong><i class="fas fa-check-circle me-1"></i> Resolution:</strong><br>
                            <?php echo nl2br(htmlspecialchars($complaint['resolution_notes'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-3">
                <div class="card-body p-4">
                    <h4 class="mb-3">Comments (<?php echo count($comments); ?>)</h4>

                    <?php if ($isLoggedIn): ?>
                        <form method="POST" action="<?php echo APP_URL; ?>/api/add-comment.php" class="mb-4" id="comment-form">
                            <input type="hidden" name="complaint_id" value="<?php echo $complaint_id; ?>">
                            <div data-mdb-input-init class="form-outline">
                                <textarea class="form-control" name="comment" id="comment" rows="4" required></textarea>
                                <label class="form-label" for="comment">Add a comment...</label>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3" data-mdb-ripple-init>Post Comment</button>
                        </form>
                    <?php else: ?>
                        <p class="text-center mb-3">
                            <a href="<?php echo APP_URL; ?>/public/login.php">Login</a> to add comments
                        </p>
                    <?php endif; ?>

                    <div id="comments-list">
                        <?php if (empty($comments)): ?>
                            <p class="text-center text-muted">No comments yet</p>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="d-flex mb-3 border-bottom pb-3">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="fas fa-user-circle fa-2x text-muted"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-0">
                                            <?php echo htmlspecialchars($comment['user_name']); ?>
                                            <?php if ($comment['is_official']): ?>
                                                <span class="badge bg-primary ms-1">Official</span>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted"><i class="fas fa-clock me-1"></i> <?php echo htmlspecialchars($comment['created_at']); ?></small>
                                        <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-3" style="position: sticky; top: 80px;">
                <div class="card-body">
                    <h4 class="mb-3">Complaint Stats</h4>

                    <div class="mb-3">
                        <strong><i class="fas fa-user me-1"></i> Reported by:</strong><br>
                        <?php echo htmlspecialchars($complaint['user_name']); ?>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-thumbs-up me-1"></i> Upvotes:</strong><br>
                        <span style="font-size: 2rem; font-weight: 700;" class="text-primary">
                            <?php echo $complaint['upvotes']; ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-shield-alt me-1"></i> Status:</strong><br>
                        <span class="badge badge-<?php echo getStatusColor($complaint['status']); ?>" style="font-size: var(--font-size-base); padding: 5px 10px;">
                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-tag me-1"></i> Priority:</strong><br>
                        <span class="badge badge-<?php echo getPriorityColor($complaint['priority']); ?>" style="font-size: var(--font-size-base); padding: 5px 10px;">
                            <?php echo ucfirst($complaint['priority']); ?>
                        </span>
                    </div>

                    <?php if ($isLoggedIn): ?>
                        <button class="btn btn-primary btn-block mb-2" id="upvote-btn" onclick="toggleUpvote(<?php echo $complaint_id; ?>)" data-mdb-ripple-init>
                            <i class="fas fa-thumbs-up me-1"></i> <?php echo $userUpvoted ? 'Remove Upvote' : 'Upvote'; ?>
                        </button>
                    <?php endif; ?>
                    
                    <a href="<?php echo APP_URL; ?>/public/complaints.php" class="btn btn-outline-secondary btn-block" data-mdb-ripple-init>
                        <i class="fas fa-arrow-left me-2"></i> View All Complaints
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // NOTE: fetchAPI and showNotification are assumed to be defined in app.js
    async function toggleUpvote(complaintId) {
        try {
            const response = await fetchAPI('<?php echo APP_URL; ?>/api/upvote-complaint.php', {
                method: 'POST',
                body: JSON.stringify({ complaint_id: complaintId })
            });
            if (response.success) {
                location.reload();
            }
        } catch (error) {
            showNotification('Failed to update upvote', 'danger');
        }
    }

    document.getElementById('comment-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const formData = new FormData(e.target);
            const complaint_id = formData.get('complaint_id');
            const comment = formData.get('comment');
            
            const response = await fetch('<?php echo APP_URL; ?>/api/add-comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    complaint_id: complaint_id,
                    comment: comment
                })
            });
            
            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                showNotification(data.message || 'Failed to post comment', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Failed to post comment', 'danger');
        }
    });
</script>

<?php
require_once __DIR__ . '/footer.php';
?>