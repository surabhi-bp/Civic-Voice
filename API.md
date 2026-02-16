# CivicVoice API Documentation

## Base URL
```
http://localhost/Civic-voice/api/
```

## Authentication
Most endpoints require user to be logged in via PHP session.

---

## Endpoints

### 1. Get All Complaints
**Endpoint:** `GET /api/complaints.php`

**Parameters:**
```
?page=1                    # Page number (default: 1)
&limit=12                  # Items per page (default: 12)
&status=pending            # Filter: pending, in_progress, resolved
&category_id=1             # Filter by category ID
&ward_id=1                 # Filter by ward ID
&search=pothole            # Search keyword
```

**Response:**
```json
{
    "success": true,
    "complaints": [
        {
            "id": 1,
            "title": "Large Pothole on Main St",
            "description": "There's a large pothole...",
            "status": "pending",
            "category_id": 1,
            "category_name": "Pothole",
            "ward_id": 1,
            "ward_name": "Ward 1 - Downtown",
            "upvotes": 5,
            "upvotes_count": 5,
            "created_at": "2025-11-16 10:30:00",
            "user_name": "John Doe"
        }
    ]
}
```

**Example:**
```
GET /api/complaints.php?status=pending&limit=6
```

---

### 2. Upvote a Complaint
**Endpoint:** `POST /api/upvote-complaint.php`

**Authentication:** Required (logged-in user)

**Request Body:**
```json
{
    "complaint_id": 1
}
```

**Response:**
```json
{
    "success": true,
    "message": "Upvote added"
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Not authenticated"
}
```

**JavaScript Example:**
```javascript
const response = await fetchAPI('api/upvote-complaint.php', {
    method: 'POST',
    body: JSON.stringify({ complaint_id: 1 })
});
```

---

### 3. Add Comment to Complaint
**Endpoint:** `POST /api/add-comment.php`

**Authentication:** Required (logged-in user)

**Request Body (Form Data):**
```
complaint_id: 1
comment: "This needs to be fixed urgently"
```

**Response:**
```json
{
    "success": true,
    "comment_id": 42,
    "message": "Comment added"
}
```

**JavaScript Example:**
```javascript
const formData = new FormData();
formData.append('complaint_id', 1);
formData.append('comment', 'This is urgent!');

const response = await fetchAPI('api/add-comment.php', {
    method: 'POST',
    body: formData
});
```

---

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 400 | Bad Request |
| 401 | Unauthorized |
| 404 | Not Found |
| 500 | Server Error |

---

## Error Handling

All API responses follow a consistent format:

```json
{
    "success": false,
    "message": "Error description"
}
```

**JavaScript Error Handling:**
```javascript
try {
    const data = await fetchAPI('api/complaints.php');
    if (data.success) {
        console.log(data.complaints);
    } else {
        showNotification(data.message, 'danger');
    }
} catch (error) {
    console.error('API Error:', error);
    showNotification('Failed to load complaints', 'danger');
}
```

---

## Rate Limiting

Currently not implemented. Future version will include:
- 100 requests per minute per user
- 1000 requests per hour per IP

---

## CORS

Not applicable (same-origin only)

---

## Pagination

All list endpoints support pagination:

```javascript
// Get page 2 with 20 items per page
GET /api/complaints.php?page=2&limit=20
```

**Response includes:**
- `complaints` array
- `success` boolean
- Items count (used to determine if more pages exist)

---

## Filtering Examples

### Get pending complaints only
```
GET /api/complaints.php?status=pending
```

### Get complaints in specific ward
```
GET /api/complaints.php?ward_id=1
```

### Search complaints
```
GET /api/complaints.php?search=pothole
```

### Combine filters
```
GET /api/complaints.php?status=pending&ward_id=1&category_id=1&search=road
```

---

## Data Types

### Complaint Object
```
{
    "id": Integer,
    "title": String,
    "description": String,
    "status": String ("pending", "in_progress", "resolved"),
    "category_id": Integer | null,
    "category_name": String | null,
    "ward_id": Integer,
    "ward_name": String,
    "latitude": Float | null,
    "longitude": Float | null,
    "address": String | null,
    "photo_url": String | null,
    "upvotes": Integer,
    "priority": String ("low", "medium", "high"),
    "assigned_department_id": Integer | null,
    "created_at": Timestamp,
    "user_name": String,
    "user_id": Integer
}
```

---

## Authentication Flow

### Login
1. POST to `/public/login.php`
2. System creates PHP session
3. Session ID stored in cookie
4. Subsequent API calls include session

### Logout
1. POST to `/public/logout.php`
2. System destroys session
3. API calls return "Not authenticated"

---

## Integration Example

### Complete Complaint Submission Flow

```javascript
// 1. Submit complaint form
async function submitComplaint() {
    const formData = new FormData(document.getElementById('submit-form'));
    
    const response = await fetch('/public/submit-complaint.php', {
        method: 'POST',
        body: formData
    });
    
    // Redirects to confirmation page
}

// 2. Load complaints list
async function loadComplaints() {
    const data = await fetchAPI('api/complaints.php?limit=12');
    renderComplaintsGrid(data.complaints);
}

// 3. Upvote a complaint
async function toggleUpvote(complaintId) {
    const data = await fetchAPI('api/upvote-complaint.php', {
        method: 'POST',
        body: JSON.stringify({ complaint_id: complaintId })
    });
    
    if (data.success) {
        location.reload(); // Refresh to show updated count
    }
}

// 4. Add comment
async function addComment(complaintId, comment) {
    const formData = new FormData();
    formData.append('complaint_id', complaintId);
    formData.append('comment', comment);
    
    const data = await fetchAPI('api/add-comment.php', {
        method: 'POST',
        body: formData
    });
    
    if (data.success) {
        location.reload(); // Refresh to show new comment
    }
}
```

---

## Future API Endpoints (Planned)

- `POST /api/create-complaint.php` - Create complaint via API
- `PUT /api/complaint/:id/status.php` - Update status (admin)
- `GET /api/user/complaints.php` - Get user's complaints
- `GET /api/analytics/summary.php` - Get analytics data
- `POST /api/contact.php` - Submit contact form
- `GET /api/faqs.php` - Get FAQ list
- `POST /api/alerts.php` - Create alert (admin)

---

## Testing Tools

### cURL Examples

**Get complaints:**
```bash
curl -b cookies.txt "http://localhost/Civic-voice/api/complaints.php"
```

**Upvote complaint:**
```bash
curl -b cookies.txt -X POST \
  -H "Content-Type: application/json" \
  -d '{"complaint_id": 1}' \
  "http://localhost/Civic-voice/api/upvote-complaint.php"
```

### Postman Collection

Import these endpoints into Postman:

1. **Base URL**: `http://localhost/Civic-voice`
2. **Cookies**: Enable cookie jar
3. **Endpoints**: Add above URLs

---

## Troubleshooting

### "Not authenticated" error
- Ensure user is logged in
- Check browser cookies are enabled
- Session may have expired - login again

### Empty response
- Check network tab for actual response
- Verify endpoint URL is correct
- Check server error logs

### CORS error
- Not applicable (same-origin only)
- Ensure requests are from same domain

---

## Performance Tips

1. **Pagination**: Always use limit parameter
2. **Caching**: Implement browser caching for lists
3. **Filtering**: Pre-filter on server, not client
4. **Search**: Use indexed fields (title, address)

---

## Version

**API Version**: 1.0  
**Last Updated**: November 2025

---

For more information, see `README.md` and `IMPLEMENTATION.md`
