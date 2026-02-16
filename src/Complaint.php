<?php
/**
 * Complaint class
 */

if (!class_exists('Complaint')) {
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
            // MySQLi bind_param requires specific type casting for non-null variables.
            // category_id and ward_id are ints. category_id can be 0 if not selected.
            // address and photo_url are strings.
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
    
    /**
     * Get complaint by ID
     */
    public function getById($complaint_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.*, u.name as user_name, u.email as user_email, cat.name as category_name, w.name as ward_name, d.name as department_name
                FROM complaints c
                JOIN users u ON c.user_id = u.id
                LEFT JOIN categories cat ON c.category_id = cat.id
                JOIN wards w ON c.ward_id = w.id
                LEFT JOIN departments d ON c.assigned_department_id = d.id
                WHERE c.id = ?
            ");
            
            $stmt->bind_param("i", $complaint_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Get all complaints with filters
     */
    public function getAllComplaints($filters = []) {
        try {
            $query = "
                SELECT c.*, u.name as user_name, cat.name as category_name, w.name as ward_name, 
                        COUNT(DISTINCT cu.id) as upvotes_count
                FROM complaints c
                JOIN users u ON c.user_id = u.id
                LEFT JOIN categories cat ON c.category_id = cat.id
                JOIN wards w ON c.ward_id = w.id
                LEFT JOIN complaint_upvotes cu ON c.id = cu.complaint_id
                WHERE 1=1
            ";
            
            $params = [];
            $types = "";
            
            // Apply filters
            if (isset($filters['status'])) {
                $query .= " AND c.status = ?";
                $params[] = $filters['status'];
                $types .= "s";
            }
            
            if (isset($filters['category_id'])) {
                $query .= " AND c.category_id = ?";
                $params[] = $filters['category_id'];
                $types .= "i";
            }
            
            if (isset($filters['ward_id'])) {
                $query .= " AND c.ward_id = ?";
                $params[] = $filters['ward_id'];
                $types .= "i";
            }
            
            if (isset($filters['search'])) {
                $search = '%' . $filters['search'] . '%';
                $query .= " AND (c.title LIKE ? OR c.description LIKE ? OR c.address LIKE ?)";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
                $types .= "sss";
            }
            
            $query .= " GROUP BY c.id ORDER BY c.created_at DESC";
            
            if (isset($filters['limit'])) {
                $query .= " LIMIT ?";
                $params[] = $filters['limit'];
                $types .= "i";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if (!empty($params)) {
                // Use call_user_func_array for dynamic bind_param if PHP version < 5.6
                if (version_compare(phpversion(), '5.6.0', '<')) {
                    call_user_func_array(array($stmt, 'bind_param'), array_merge(array($types), $this->refValues($params)));
                } else {
                    $stmt->bind_param($types, ...$params);
                }
            }
            
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            // In a real application, log this error
            return [];
        }
    }
    
    // Helper function for dynamic bind_param on older PHP versions
    private function refValues($arr){
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    
    /**
     * Get complaints by user ID
     */
    public function getByUserId($user_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.*, cat.name as category_name, w.name as ward_name, COUNT(DISTINCT cu.id) as upvotes_count
                FROM complaints c
                LEFT JOIN categories cat ON c.category_id = cat.id
                JOIN wards w ON c.ward_id = w.id
                LEFT JOIN complaint_upvotes cu ON c.id = cu.complaint_id
                WHERE c.user_id = ?
                GROUP BY c.id
                ORDER BY c.created_at DESC
            ");
            
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Update complaint status
     */
    public function updateStatus($complaint_id, $status, $admin_id = null, $notes = null) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE complaints 
                SET status = ?, resolution_notes = ?, resolved_at = ?
                WHERE id = ?
            ");
            
            $resolved_at = ($status === 'resolved') ? date('Y-m-d H:i:s') : null;
            
            $stmt->bind_param("sssi", $status, $notes, $resolved_at, $complaint_id);
            
            if ($stmt->execute()) {
                if ($admin_id) {
                    $this->logActivity($complaint_id, $admin_id, 'status_updated', "Status changed to $status");
                }
                return ['success' => true, 'message' => 'Status updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to update status'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Assign complaint to department
     */
    public function assign($complaint_id, $department_id, $assigned_to_user_id, $admin_id) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE complaints 
                SET assigned_department_id = ?, assigned_to_user_id = ?
                WHERE id = ?
            ");
            
            $stmt->bind_param("iii", $department_id, $assigned_to_user_id, $complaint_id);
            
            if ($stmt->execute()) {
                $this->logActivity($complaint_id, $admin_id, 'assigned', "Assigned to department $department_id");
                return ['success' => true, 'message' => 'Complaint assigned successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to assign complaint'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
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
    
    /**
     * Add upvote to complaint
     */
    public function addUpvote($complaint_id, $user_id) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO complaint_upvotes (complaint_id, user_id)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE id = id
            ");
            
            $stmt->bind_param("ii", $complaint_id, $user_id);
            $stmt->execute();
            
            // Update complaint upvotes count
            $stmt = $this->conn->prepare("
                UPDATE complaints 
                SET upvotes = (SELECT COUNT(*) FROM complaint_upvotes WHERE complaint_id = ?)
                WHERE id = ?
            ");
            
            $stmt->bind_param("ii", $complaint_id, $complaint_id);
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Upvote added'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Add comment to complaint
     */
    public function addComment($complaint_id, $user_id, $comment_text, $is_official = false, $is_public = true) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO complaint_comments (complaint_id, user_id, comment_text, is_official, is_public)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param("iisii", $complaint_id, $user_id, $comment_text, $is_official, $is_public);
            
            if ($stmt->execute()) {
                return ['success' => true, 'comment_id' => $stmt->insert_id, 'message' => 'Comment added'];
            } else {
                return ['success' => false, 'message' => 'Failed to add comment'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get comments for complaint
     */
    public function getComments($complaint_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT cc.*, u.name as user_name, u.profile_picture
                FROM complaint_comments cc
                JOIN users u ON cc.user_id = u.id
                WHERE cc.complaint_id = ? AND cc.is_public = TRUE
                ORDER BY cc.created_at DESC
            ");
            
            $stmt->bind_param("i", $complaint_id);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
}

?>