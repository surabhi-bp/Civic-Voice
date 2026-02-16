<?php
// We only need the database/constants/Complaint class for the API/data fetching
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/Complaint.php'; // Required for consistency

// Set the page title BEFORE including the header.
$pageTitle = 'Report Issues. Drive Change.';

// 1. Include the header. (Assumes header.php is in the same folder)
require_once __DIR__ . '/header.php';
?>

<section class="mt-3 text-center" style="background-color: var(--mdb-body-bg-rgb); padding: 4rem 1.5rem; border-radius: 0.5rem; border: 1px solid var(--mdb-border-color);">
    <h1 class="display-4 fw-bold">Report Issues. Drive Change.</h1>
    <p class="lead text-muted col-lg-8 mx-auto">Make your voice heard by reporting local government issues and tracking their resolution in real-time.</p>
    <?php if ($isLoggedIn): ?>
        <a href="<?php echo APP_URL; ?>/public/submit-complaint.php" class="btn btn-primary btn-lg" data-mdb-ripple-init>
            <i class="fas fa-plus me-2"></i> Report an Issue
        </a>
    <?php else: ?>
        <a href="<?php echo APP_URL; ?>/public/signup.php" class="btn btn-primary btn-lg" data-mdb-ripple-init>
            <i class="fas fa-user-plus me-2"></i> Get Started
        </a>
    <?php endif; ?>
</section>

<section class="mb-4 mt-5">
    <h2 class="mb-4 text-center fw-bold">Why Choose CivicVoice?</h2>
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 shadow-2-strong">
                <div class="card-body text-center">
                    <i class="fas fa-map-marker-alt fa-3x text-primary mb-3"></i>
                    <h5 class="card-title mt-3">Location-Based Tracking</h5>
                    <p class="card-text">Report issues with exact GPS coordinates or address mapping.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 shadow-2-strong">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                    <h5 class="card-title mt-3">Real-Time Updates</h5>
                    <p class="card-text">Track the status of your complaints from pending to resolved.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 shadow-2-strong">
                <div class="card-body text-center">
                    <i class="fas fa-brain fa-3x text-primary mb-3"></i>
                    <h5 class="card-title mt-3">AI-Powered Analysis</h5>
                    <p class="card-text">Automatic categorization and prioritization of issues.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 shadow-2-strong">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5 class="card-title mt-3">Community Support</h5>
                    <p class="card-text">Upvote and follow similar issues raised by other citizens.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mb-4">
    <h2 class="mb-3 text-center fw-bold">Recent Complaints</h2>
    <div id="recent-complaints" class="row">
        <div class="col-12 text-center" id="recent-complaints-loader">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Loading complaints...</p>
        </div>
    </div>
    <div class="text-center mt-3">
        <a href="<?php echo APP_URL; ?>/public/complaints.php" class="btn btn-outline-primary" data-mdb-ripple-init>View All Complaints</a>
    </div>
</section>

<section class="mb-4">
    <h2 class="mb-3 text-center fw-bold">Frequently Asked Questions</h2>
    <div class="accordion" id="faqAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button
                  data-mdb-collapse-init
                  class="accordion-button collapsed"
                  type="button"
                  data-mdb-target="#collapseOne"
                  aria-expanded="false"
                  aria-controls="collapseOne"
                >
                  How do I submit a complaint?
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-mdb-parent="#faqAccordion">
                <div class="accordion-body">
                    Simply sign up, click "Report an Issue", fill in the details including a photo, location, and description.
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button
                  data-mdb-collapse-init
                  class="accordion-button collapsed"
                  type="button"
                  data-mdb-target="#collapseTwo"
                  aria-expanded="false"
                  aria-controls="collapseTwo"
                >
                  How long does it take to resolve an issue?
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-mdb-parent="#faqAccordion">
                <div class="accordion-body">
                    Resolution time depends on the type and severity of the issue. You can track the status real-time on your dashboard.
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        fetchRecentComplaints();
    });

    async function fetchRecentComplaints() {
        try {
            const loader = document.getElementById('recent-complaints-loader');
            if (loader) loader.style.display = 'block';

            const response = await fetch('<?php echo APP_URL; ?>/api/complaints.php?limit=6');
            const data = await response.json();
            
            if (loader) loader.style.display = 'none';
            const container = document.getElementById('recent-complaints');
            
            if (data.success && data.complaints && data.complaints.length > 0) {
                container.innerHTML = data.complaints.map(complaint => `
                    <div class="col-md-6 col-lg-4 mb-4">
                        <a href="<?php echo APP_URL; ?>/public/complaint-detail.php?id=${complaint.id}" class="card h-100 shadow-2-strong text-dark hover-shadow" style="text-decoration: none;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start p-3">
                                    <h5 class="card-title">${escapeHtml(complaint.title)}</h5>
                                    <span class="badge badge-${getStatusColor(complaint.status)}">${complaint.status}</span>
                                </div>
                                <h6 class="card-subtitle mb-2 text-muted px-3">${complaint.ward_name}</h6>
                                <p class="card-text p-3">${escapeHtml(complaint.description.substring(0, 100))}...</p>
                                <div class="card-footer d-flex justify-content-between align-items-center">
                                    <small class="text-primary"><i class="fas fa-thumbs-up me-1"></i> ${complaint.upvotes_count || 0} upvotes</small>
                                    <small class="text-muted">${formatDistanceToNow(complaint.created_at)}</small>
                                </div>
                            </div>
                        </a>
                    </div>
                `).join('');
            } else {
                 container.innerHTML = '<div class="col-12"><p class="text-center text-muted">No recent complaints found.</p></div>';
            }
        } catch (error) {
            console.error('Error fetching complaints:', error);
            document.getElementById('recent-complaints').innerHTML = '<div class="col-12"><p class="text-center text-danger">Error loading complaints.</p></div>';
        }
    }

    // --- Assuming JS Helper functions are defined in app.js or globally ---
    // These functions must exist for the fetchRecentComplaints to work.

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getStatusColor(status) {
        const colors = {
            'pending': 'warning',
            'in_progress': 'info',
            'resolved': 'success'
        };
        return colors[status] || 'primary';
    }
    
    function formatDistanceToNow(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        let interval = seconds / 31536000;
        if (interval > 1) return Math.floor(interval) + " years ago";
        interval = seconds / 2592000;
        if (interval > 1) return Math.floor(interval) + " months ago";
        interval = seconds / 86400;
        if (interval > 1) return Math.floor(interval) + " days ago";
        interval = seconds / 3600;
        if (interval > 1) return Math.floor(interval) + " hours ago";
        interval = seconds / 60;
        if (interval > 1) return Math.floor(interval) + " minutes ago";
        return Math.floor(seconds) + " seconds ago";
    }
</script>

<?php
// 2. Include the footer. (Assumes footer.php is in the same folder)
require_once __DIR__ . '/footer.php';
?>