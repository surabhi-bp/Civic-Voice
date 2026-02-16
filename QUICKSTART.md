# CivicVoice - Quick Start Guide

## ⚡ 5-Minute Setup

### 1. Extract Files
```
C:\xampp\htdocs\Civic-voice\
```

### 2. Create Database
1. Open http://localhost/phpmyadmin
2. Create database: `civic_voice`
3. Import: `database/schema.sql`

### 3. Create Uploads Folder
```powershell
mkdir "C:\xampp\htdocs\Civic-voice\public\uploads"
```

### 4. Start Services
- Open XAMPP Control Panel
- Start Apache & MySQL

### 5. Access Application
- **Citizen**: http://localhost/Civic-voice/public/
- **Admin**: http://localhost/Civic-voice/admin/login.php

---

## 🔐 Create Test Admin Account

1. Open http://localhost/phpmyadmin
2. Select database `civic_voice`
3. Go to "SQL" tab
4. Execute:

```sql
-- Create admin user
INSERT INTO users (id, name, email, password_hash, user_type, created_at) 
VALUES (999, 'Admin User', 'admin@civicvoice.local', '$2y$10$ixl0XKjTcRhPGDXfX.8cIum9YJ.yqVcVlAVxKZMYuQiQ9qYRTKRAm', 'admin', NOW());

-- Assign super admin role
INSERT INTO admin_roles (user_id, role) VALUES (999, 'super_admin');
```

**Admin Login:**
- Email: `admin@civicvoice.local`
- Password: `admin@123`

---

## 📋 First-Time Setup Checklist

- [ ] Database created and schema imported
- [ ] Uploads folder created with permissions 755
- [ ] Apache & MySQL running
- [ ] Admin account created
- [ ] Application accessible at localhost
- [ ] Test citizen signup works
- [ ] Test admin login works

---

## 🎯 Key Features to Test

### As a Citizen
1. Sign up with name, email, password
2. Submit a complaint with photo
3. View complaints list
4. Upvote and comment on complaints
5. Check dashboard for your complaints

### As an Admin
1. Log in to admin panel
2. View all complaints
3. Update complaint status
4. Assign to departments
5. View analytics dashboard

---

## 🌐 Application URLs

| Page | Citizen | Admin |
|------|---------|-------|
| Home | `/public/` | - |
| Login | `/public/login.php` | `/admin/login.php` |
| Dashboard | `/public/dashboard.php` | `/admin/dashboard.php` |
| Submit Complaint | `/public/submit-complaint.php` | - |
| View Complaints | `/public/complaints.php` | `/admin/complaints.php` |
| Analytics | - | `/admin/analytics.php` |

---

## 🛠️ Troubleshooting

### Page shows blank
- Check PHP error log: `C:\xampp\php\logs\`
- Verify APP_URL in `config/constants.php`

### Can't upload files
- Ensure `public/uploads/` exists
- Check folder permissions (755)

### Database connection failed
- Verify MySQL is running
- Check credentials in `config/database.php`

### Images not displaying
- Check image file paths
- Verify uploads folder has read permissions

---

## 📞 Support

For detailed documentation, see `README.md`

---

**Happy Reporting!** 🎉
