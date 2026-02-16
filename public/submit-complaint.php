<?php
// --- PHP LOGIC MUST BE FIRST ---
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/public/login.php');
    exit();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';

$success = false;
$error = '';

// Capture POST values before execution for re-population later if error occurs
$posted_values = [
    'title' => $_POST['title'] ?? '',
    'description' => $_POST['description'] ?? '',
    'category_id' => $_POST['category_id'] ?? '',
    'state_id' => $_POST['state_id'] ?? '', 
    // District ID is no longer relevant for the form, but ward_id is new
    'ward_id' => $_POST['ward_id'] ?? '', 
    'latitude' => $_POST['latitude'] ?? '',
    'longitude' => $_POST['longitude'] ?? '',
    'address' => $_POST['address'] ?? '',
];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../src/Complaint.php';
    
    // Get posted values and sanitize
    $title = sanitizeInput($posted_values['title']);
    $description = sanitizeInput($posted_values['description']);
    $category_id = (int)($posted_values['category_id']);
    // Note: district_id is not used directly, but ward_id is
    $ward_id = (int)($posted_values['ward_id']); 
    $latitude = (float)($posted_values['latitude']);
    $longitude = (float)($posted_values['longitude']);
    $address = sanitizeInput($posted_values['address']);
    
    // Validation: Check for required fields (Title, Description, and the new Ward ID)
    // Check if ward_id is 0, which happens if the user selects the default option (value="")
    if (empty($title) || empty($description) || $ward_id === 0) { 
        $error = 'Please fill in all required fields (Title, Description, and Ward).';
    } else {
        $photo_url = null;
        
        // Handle photo upload (Logic retained)
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = UPLOADS_DIR;
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_ext, $allowed_ext)) {
                $error = 'Invalid file type. Only JPG, PNG, GIF allowed.';
            } else {
                $file_name = 'complaint_' . time() . '_' . uniqid() . '.' . $file_ext;
                $file_path = $upload_dir . '/' . $file_name;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_path)) {
                    $photo_url = UPLOADS_URL . '/' . $file_name;
                } else {
                    $error = 'Failed to upload photo';
                }
            }
        }
        
        // --- WARD ID COMES DIRECTLY FROM POSTED VALUE ($ward_id) ---
        // Validate that ward_id exists in the database
        if (!$error) {
            $stmt = $conn->prepare("SELECT id FROM wards WHERE id = ?");
            $stmt->bind_param("i", $ward_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $error = 'Invalid ward selected. Please choose a valid ward.';
            }
        }
        
        if (!$error) {
            $complaint = new Complaint($conn);
            $result = $complaint->create(
                $_SESSION['user_id'],
                $title,
                $description,
                $category_id > 0 ? $category_id : null,
                $ward_id, // Use the posted ward ID directly
                $latitude,
                $longitude,
                $address,
                $photo_url
            );
            
            if ($result['success']) {
                $success = true;
                $_SESSION['complaint_id'] = $result['complaint_id'];
            } else {
                // Return the detailed message from the Complaint class
                $error = $result['message']; 
            }
        }
    }
}

// --- PART 1: THE SUCCESS PAGE (Skipped for brevity) ---
if ($success) {
    $pageTitle = 'Complaint Submitted';
    require_once __DIR__ . '/header.php';
    ?>
    <div class="container my-5">
        <div style="max-width: 600px; margin: 3rem auto;">
            <div class="card shadow-3">
                <div class="card-body p-5 text-center">
                    <div style="font-size: 4rem; margin-bottom: 1rem;" class="text-success">
                        <i class="fas fa-check-circle fa-bounce"></i>
                    </div>
                    <h2 class="mb-2">Complaint Submitted!</h2>
                    <p class="text-muted mb-3">Thank you for reporting this issue. We'll analyze it and assign it to the appropriate department.</p>
                    
                    <div class="alert alert-success mb-3">
                        <strong>Complaint ID:</strong> #<?php echo str_pad($_SESSION['complaint_id'], 6, '0', STR_PAD_LEFT); ?>
                    </div>

                    <p class="mb-4 text-start">
                        <strong>Next Steps:</strong><br>
                        1. Our AI system will automatically categorize your complaint.<br>
                        2. It will be assigned to the relevant department.<br>
                        3. You'll receive updates as the status changes.<br>
                        4. Track progress on your dashboard.
                    </p>

                    <div class="d-flex justify-content-center" style="gap: 1rem;">
                        <a href="<?php echo APP_URL; ?>/public/dashboard.php" class="btn btn-primary" data-mdb-ripple-init>
                            <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                        </a>
                        <a href="<?php echo APP_URL; ?>/public/complaints.php" class="btn btn-outline-primary" data-mdb-ripple-init>
                            View Complaints
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/footer.php';
    exit();
}

// --- PART 2: THE SUBMISSION FORM (Logic for fetching data) ---

// *******************************************************************
// ************ SIMULATED DATA REPLACEMENT STARTS HERE ***************
// *******************************************************************

// Fetch States (Simulated data)
$states = [
    ['id' => 1, 'name' => 'Karnataka'], // State 1: Bengaluru (Urban)
    ['id' => 2, 'name' => 'Tamil Nadu'], // State 2: Chennai
];

// Fetch Wards (CORRECTED to use actual IDs 1-5 from the database image)
$all_wards = [
    // Use IDs 1, 2, 3, 4, 5
    ['id' => 1, 'state_id' => 1, 'district_id' => 105, 'name' => 'Ward 1 - Downtown'], 
    ['id' => 2, 'state_id' => 1, 'district_id' => 105, 'name' => 'Ward 2 - North'],
    ['id' => 3, 'state_id' => 1, 'district_id' => 105, 'name' => 'Ward 3 - South'],
    ['id' => 4, 'state_id' => 1, 'district_id' => 105, 'name' => 'Ward 4 - East'],
    ['id' => 5, 'state_id' => 1, 'district_id' => 105, 'name' => 'Ward 5 - West'],
    // Example ward for State 2 (Tamil Nadu)
    ['id' => 2001, 'state_id' => 2, 'district_id' => 201, 'name' => 'Ward 1 - Chennai Central'], 
];

// Fetch Districts (Retained as contextual data)
$all_districts = [
    // Karnataka Districts (state_id = 1)
    ['id' => 105, 'state_id' => 1, 'name' => 'Bengaluru Urban'], 
    ['id' => 101, 'state_id' => 1, 'name' => 'Bagalkot'],
    // Example District for State 2
    ['id' => 201, 'state_id' => 2, 'name' => 'Chennai'],
];
// Note: In a real app, these would be fetched from DB:
// $states = $conn->query("SELECT id, name FROM states ORDER BY name")->fetch_all(MYSQLI_ASSOC);
// $all_wards = $conn->query("SELECT id, state_id, district_id, name FROM wards ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$categories = $conn->query("SELECT id, name FROM categories WHERE is_active = TRUE ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// *******************************************************************
// ************ SIMULATED DATA REPLACEMENT ENDS HERE *****************
// *******************************************************************

// Set page title and include template header
$pageTitle = 'Report an Issue';
require_once __DIR__ . '/header.php';
?>

<style>
    /* Clean and simple form styling */
    .coordinate-field {
        display: flex;
        flex-direction: column;
        margin-bottom: 1.5rem;
    }
    
    .coordinate-field label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .coordinate-field input[type="number"] {
        padding: 0.75rem;
        font-size: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        background-color: #f8f9fa;
        transition: all 0.2s ease;
    }
    
    .coordinate-field input[type="number"]:focus {
        background-color: white;
        border-color: #0d6efd;
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .coordinates-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    @media (max-width: 576px) {
        .coordinates-row {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }
</style>

<div class="container my-5">
    <div style="max-width: 700px; margin: 1rem auto;">
        <div class="card shadow-3">
            <div class="card-body p-5">
                <h1 class="mb-3"><i class="fas fa-exclamation-circle me-2 text-danger"></i>Report an Issue</h1>
                <p class="text-muted">Help us improve your community by reporting municipal issues.</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger mb-3">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" id="submit-form">
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($posted_values['title']); ?>" required />
                        <label class="form-label" for="title">Issue Title *</label>
                    </div>

                    <div data-mdb-input-init class="form-outline mb-4">
                        <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($posted_values['description']); ?></textarea>
                        <label class="form-label" for="description">Detailed Description *</label>
                    </div>

                    <select class="form-select mb-4" name="category_id">
                        <option value="">Select a category (optional)</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($posted_values['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <hr class="my-4">
                    <h5 class="mb-3 text-primary"><i class="fas fa-map-marker-alt me-2"></i>Location Details (Required)</h5>

                    <select class="form-select mb-4" id="state-select" name="state_id" required>
                        <option value="">Select State *</option>
                        <?php foreach ($states as $state): ?>
                            <option value="<?php echo $state['id']; ?>" <?php echo ($posted_values['state_id'] == $state['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($state['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select class="form-select mb-4" id="ward-select" name="ward_id" required>
                        <option value="">Select Ward *</option>
                    </select>
                    <hr class="my-4">
                    <h5 class="mb-3 text-primary"><i class="fas fa-location-arrow me-2"></i>Location Coordinates</h5>

                    <button type="button" class="btn btn-outline-info btn-block mb-3" id="get-location-btn">
                        <i class="fas fa-crosshairs me-2"></i> Get Current Location via GPS
                    </button>
                    <small id="geo-status" class="form-text text-muted d-block text-center mb-3">Click above or enter manually below.</small>

                    <div class="coordinates-row">
                        <div class="coordinate-field">
                            <label for="latitude">Latitude (Optional)</label>
                            <input type="number" id="latitude" name="latitude" step="0.000001" value="<?php echo htmlspecialchars($posted_values['latitude']); ?>" />
                        </div>
                        <div class="coordinate-field">
                            <label for="longitude">Longitude (Optional)</label>
                            <input type="number" id="longitude" name="longitude" step="0.000001" value="<?php echo htmlspecialchars($posted_values['longitude']); ?>" />
                        </div>
                    </div>

                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($posted_values['address']); ?>" />
                        <label class="form-label" for="address">Location Address (optional)</label>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="photo-input">Photo of Issue</label>
                        <input type="file" class="form-control" id="photo-input" name="photo" accept="image/*" />
                        <div class="form-text">JPG, PNG, GIF - Max 5MB</div>
                        <img id="photo-preview" src="" alt="Photo preview" style="width: 100%; max-width: 200px; margin-top: 15px; display: none; border-radius: 0.5rem;" />
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" data-mdb-ripple-init>
                        <i class="fas fa-paper-plane me-2"></i> Submit Complaint
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // PHP encoded JSON data for quick JS filtering and restoring state
    const ALL_WARDS = <?php echo json_encode($all_wards); ?>;
    
    // Store POSTed values for restoring dropdown selections after form error
    const POSTED_WARD_ID = '<?php echo $posted_values['ward_id']; ?>';

    const stateSelect = document.getElementById('state-select');
    // Renamed from districtSelect to wardSelect
    const wardSelect = document.getElementById('ward-select'); 
    const getLocationBtn = document.getElementById('get-location-btn');
    const geoStatus = document.getElementById('geo-status');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const photoInput = document.getElementById('photo-input');
    const photoPreview = document.getElementById('photo-preview');
    
    // --- LOCATION HIERARCHY FUNCTIONS (Modified for Wards) ---

    function populateWards() {
        const stateId = stateSelect.value;
        
        let wardOptions = '<option value="">Select Ward *</option>';
        // Filter wards based on the selected state ID
        const filteredWards = ALL_WARDS.filter(w => w.state_id == stateId);

        filteredWards.forEach(ward => {
            // Restore selection if POSTed
            const isSelected = (POSTED_WARD_ID == ward.id) ? 'selected' : '';
            wardOptions += `<option value="${ward.id}" ${isSelected}>${ward.name}</option>`;
        });
        wardSelect.innerHTML = wardOptions;
        
        // Re-initialize MDB inputs for styling
        if (typeof mdb !== 'undefined' && mdb.Input) {
            mdb.Input.init();
        }
    }
    
    // --- GEOLOCATION FUNCTIONS (FIXED) ---
    
    function setCoordinates(lat, lng) {
        // Set values
        latitudeInput.value = lat ? lat.toFixed(6) : '';
        longitudeInput.value = lng ? lng.toFixed(6) : '';
        
        // Use MDB's internal Input.init to handle labels on value change
        if (typeof mdb !== 'undefined' && mdb.Input) {
            mdb.Input.init(latitudeInput);
            mdb.Input.init(longitudeInput);
        }
        
        // Only update status if successful coordinates were received
        if (lat && lng) {
            geoStatus.textContent = 'Location set successfully!';
            geoStatus.classList.remove('text-muted', 'text-danger');
            geoStatus.classList.add('text-success');
        }
    }

    function geoSuccess(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        setCoordinates(lat, lng);
        getLocationBtn.disabled = false;
        getLocationBtn.innerHTML = '<i class="fas fa-crosshairs me-2"></i> Update Current Location';
    }

    function geoError(error) {
        // Reset inputs to blank on error and notify user
        setCoordinates(null, null); 
        
        geoStatus.textContent = `Error: ${error.message}. Please enter manually.`;
        geoStatus.classList.remove('text-muted', 'text-success');
        geoStatus.classList.add('text-danger');
        getLocationBtn.disabled = false;
        getLocationBtn.innerHTML = '<i class="fas fa-crosshairs me-2"></i> Get Current Location via GPS';
    }
    
    function getLocation() {
        if (!navigator.geolocation) {
            geoStatus.textContent = 'Geolocation is not supported by your browser.';
            geoStatus.classList.remove('text-muted');
            geoStatus.classList.add('text-danger');
            return;
        }

        geoStatus.textContent = 'Fetching location...';
        getLocationBtn.disabled = true;
        
        // Use a higher timeout for better reliability
        navigator.geolocation.getCurrentPosition(geoSuccess, geoError, {
            enableHighAccuracy: true,
            timeout: 10000, 
            maximumAge: 0
        });
    }

    // --- EVENT LISTENERS ---
    
    document.addEventListener('DOMContentLoaded', () => {
        // Initial population based on the default or posted state ID
        populateWards();
        
        // Ensure MDB inputs are initialized for any POSTed values
        if (typeof mdb !== 'undefined' && mdb.Input) {
            mdb.Input.init();
        }
    });

    // Changed listener to call the new function
    stateSelect.addEventListener('change', populateWards); 
    getLocationBtn.addEventListener('click', getLocation);
    
    // Photo preview script (retained)
    if (photoInput) {
        photoInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.src = e.target.result;
                    photoPreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                photoPreview.src = '';
                photoPreview.style.display = 'none';
            }
        });
    }

</script>

<?php
require_once __DIR__ . '/footer.php';
?>                                                                                                                                                                            <?php
/**
 * Complaint class
 */

class Complaint {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Create a new complaint
     */
    public function create($user_id, $title, $description, $category_id, $ward_id, $latitude, $longitude, $address, $photo_url = null) {
        try {
            // 11 columns, 11 placeholders
            $stmt = $this->conn->prepare("
                INSERT INTO complaints (user_id, title, description, category_id, ward_id, latitude, longitude, address, photo_url, status, priority)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $status = 'pending';
            $priority = 'medium';
            
            // Prepare variables for binding (handle expected nulls/optional fields)
            $category_id = $category_id ?: 0;
            $address = $address ?: '';
            $photo_url = $photo_url ?: '';
            
            // CORRECTED BIND_PARAM: 11 characters (issiiidssss) for 11 variables
            // i: user_id, s: title, s: description, i: category_id, i: ward_id, 
            // d: latitude, d: longitude, s: address, s: photo_url, s: status, s: priority
            // NOTE: Using 'd' for latitude/longitude as they are floats/doubles.
            $stmt->bind_param("isiiidsssss", 
                $user_id,       // i
                $title,         // s
                $description,   // s
                $category_id,   // i
                $ward_id,       // i
                $latitude,      // d
                $longitude,     // d
                $address,       // s
                $photo_url,     // s
                $status,        // s
                $priority       // s
            );
            
            if ($stmt->execute()) {
                $complaint_id = $stmt->insert_id;
                
                // Log activity
                $this->logActivity($complaint_id, $user_id, 'created', 'Complaint created');
                
                return ['success' => true, 'complaint_id' => $complaint_id, 'message' => 'Complaint created successfully'];
            } else {
                // Return detailed error for debugging purposes (optional)
                return ['success' => false, 'message' => 'Failed to create complaint: ' . $stmt->error];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // ... rest of the Complaint class methods ...

    /**
     * Log activity
     */
    private function logActivity($complaint_id, $user_id, $action, $description = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO activity_logs (complaint_id, user_id, action, description)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->bind_param("iiss", $complaint_id, $user_id, $action, $description);
            $stmt->execute();
        } catch (Exception $e) {
            // Log activity failed, but don't break main operation
        }
    }
    // ... 
}

?>