<?php
// --- PHP LOGIC MUST BE FIRST ---
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
// Include Complaint class for consistency, even if only DB queries are run directly
require_once __DIR__ . '/../src/Complaint.php'; 

// Fetch Wards for dropdown
$wards = [];
$ward_result = $conn->query("SELECT id, name FROM wards WHERE is_active = TRUE ORDER BY name");
if ($ward_result) $wards = $ward_result->fetch_all(MYSQLI_ASSOC);

// Fetch Categories for dropdown
$categories = [];
$category_result = $conn->query("SELECT id, name FROM categories WHERE is_active = TRUE ORDER BY name");
if ($category_result) $categories = $category_result->fetch_all(MYSQLI_ASSOC);

// Set page title and include template header
$pageTitle = 'View Complaints';
require_once __DIR__ . '/header.php';
?>

<style>
    .complaint-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: all 0.3s ease;
        border: none !important;
    }
    
    .complaint-card .card-body {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        padding: 1.5rem;
    }
    
    .complaint-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 0.75rem;
    }
    
    .complaint-title {
        flex: 1;
        word-break: break-word;
        margin: 0;
        line-height: 1.4;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .complaint-status {
        flex-shrink: 0;
        white-space: nowrap;
        font-size: 0.85rem;
    }
    
    .complaint-ward {
        font-size: 0.95rem;
        color: #6c757d;
        margin-bottom: 0.75rem;
        margin-top: 0;
    }
    
    .complaint-description {
        flex-grow: 1;
        margin-bottom: 1rem;
        line-height: 1.5;
        color: #495057;
    }
    
    .complaint-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 0.75rem;
        border-top: 1px solid #e0e0e0;
        font-size: 0.9rem;
    }
    
    .complaint-card:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
        transform: translateY(-2px);
    }
</style>

<div class="container my-5">
    <h1 class="mb-4">Public Complaints</h1>

    <div class="card shadow-3 mb-4">
        <div class="card-body p-4">
            <h5 class="card-title mb-3"><i class="fas fa-filter me-2"></i>Filters</h5>
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <div data-mdb-input-init class="form-outline">
                        <input type="text" id="search" placeholder="Search by location or keyword" class="form-control">
                        <label class="form-label" for="search">Search</label>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <select id="status-filter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <select id="category-filter" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <select id="ward-filter" class="form-select">
                        <option value="">All Wards</option>
                        <?php foreach ($wards as $ward): ?>
                            <option value="<?php echo $ward['id']; ?>"><?php echo htmlspecialchars($ward['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div id="complaints-container" class="row g-4">
        <div class="col-12 text-center" id="complaints-loader">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Loading complaints...</p>
        </div>
    </div>

    <nav aria-label="Page navigation" class="mt-4 d-flex justify-content-center">
        <ul class="pagination shadow-0">
            <li class="page-item" id="prev-btn-li">
                <a class="page-link" href="#" id="prev-btn" onclick="previousPage(event)">Previous</a>
            </li>
            <li class="page-item active">
                <a class="page-link" href="#" id="page-info">Page 1</a>
            </li>
            <li class="page-item" id="next-btn-li">
                <a class="page-link" href="#" id="next-btn" onclick="nextPage(event)">Next</a>
            </li>
        </ul>
    </nav>
</div>

<script>
    let currentPage = 1;
    const itemsPerPage = 12;

    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), delay);
        };
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadComplaints();
        
        const searchInput = document.getElementById('search');
        const debouncedSearch = debounce(() => {
            currentPage = 1;
            loadComplaints();
        }, 500);
        
        searchInput.addEventListener('input', debouncedSearch);
        
        ['status-filter', 'category-filter', 'ward-filter'].forEach(id => {
            document.getElementById(id).addEventListener('change', () => {
                currentPage = 1;
                loadComplaints();
            });
        });
    });

    async function loadComplaints() {
        try {
            const search = document.getElementById('search').value;
            const status = document.getElementById('status-filter').value;
            const category = document.getElementById('category-filter').value;
            const ward = document.getElementById('ward-filter').value;
            const loader = document.getElementById('complaints-loader');
            
            loader.style.display = 'block';

            let url = '<?php echo APP_URL; ?>/api/complaints.php?page=' + currentPage + '&limit=' + itemsPerPage;
            if (search) url += '&search=' + encodeURIComponent(search);
            if (status) url += '&status=' + status;
            if (category) url += '&category_id=' + category;
            if (ward) url += '&ward_id=' + ward;

            const response = await fetch(url);
            const data = await response.json();
            
            loader.style.display = 'none';
            const container = document.getElementById('complaints-container');

            if (data.success && data.complaints && data.complaints.length > 0) {
                container.innerHTML = data.complaints.map(complaint => `
                    <div class="col-lg-4 col-md-6">
                        <a href="<?php echo APP_URL; ?>/public/complaint-detail.php?id=${complaint.id}" class="card complaint-card shadow-2-strong text-dark" style="text-decoration: none;">
                            <div class="card-body">
                                <div class="complaint-header">
                                    <h5 class="complaint-title">${escapeHtml(complaint.title)}</h5>
                                    <span class="badge badge-${getStatusColor(complaint.status)} complaint-status">${complaint.status}</span>
                                </div>
                                <h6 class="complaint-ward">${complaint.ward_name}</h6>
                                <p class="complaint-description">${escapeHtml(complaint.description.substring(0, 100))}...</p>
                                <div class="complaint-footer">
                                    <small class="text-primary"><i class="fas fa-thumbs-up me-1"></i>${complaint.upvotes_count || 0}</small>
                                    <small class="text-muted">${formatDistanceToNow(complaint.created_at)}</small>
                                </div>
                            </div>
                        </a>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="col-12"><div class="card card-body text-center text-muted">No complaints found.</div></div>';
            }

            document.getElementById('page-info').textContent = `Page ${currentPage}`;
            document.getElementById('prev-btn-li').classList.toggle('disabled', currentPage === 1);
            document.getElementById('next-btn-li').classList.toggle('disabled', data.complaints.length < itemsPerPage);
            
        } catch (error) {
            console.error('Error loading complaints:', error);
            document.getElementById('complaints-loader').style.display = 'none';
        }
    }

    function applyFilters() {
        currentPage = 1;
        loadComplaints();
    }

    function nextPage(e) {
        if (e) e.preventDefault();
        if (!document.getElementById('next-btn-li').classList.contains('disabled')) {
            currentPage++;
            loadComplaints();
            window.scrollTo(0, 0);
        }
    }

    function previousPage(e) {
        if (e) e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            loadComplaints();
            window.scrollTo(0, 0);
        }
    }

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
require_once __DIR__ . '/footer.php';
?>
