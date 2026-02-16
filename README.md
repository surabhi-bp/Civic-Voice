# 🏛️ CivicVoice: The Future of Urban Transparency
### *Empowering Communities through AI-Driven Governance*

[![Project Status: 100% Complete](https://img.shields.io/badge/Status-100%25%20Complete-green?style=for-the-badge)](https://github.com/surabhi-bp/Civic_voice)
[![Tech Stack: PHP/MySQL](https://img.shields.io/badge/Stack-PHP%20%7C%20MySQL%20%7C%20JS-blue?style=for-the-badge)](https://github.com/surabhi-bp/Civic_voice)

---

## 🧐 What is CivicVoice?
**CivicVoice** is a comprehensive, responsive web ecosystem designed to bridge the communication gap between citizens and local government. 

In many cities, reporting a broken streetlight or a massive pothole feels like shouting into a void. CivicVoice turns that "shout" into a **tracked, visible, and actionable digital ticket**. It’s about accountability: citizens report, the system categorizes, and officials resolve.

## 🚨 Why do we need this?
1. **End the "Paperwork Black Hole":** Traditional complaints get lost. CivicVoice provides real-time status tracking from **Pending ➡️ In Progress ➡️ Resolved**.
2. **Community Backing:** Through the **Upvote system**, citizens can show officials which issues are the most urgent for the neighborhood.
3. **Data-Driven Governance:** Administrators get an analytics dashboard to see "hotspots," helping them allocate budget and staff where they are needed most.

---

## ⚙️ Technical Blueprint

### 🏗️ The Infrastructure
* **Server:** Apache (XAMPP Environment).
* **Backend:** PHP 7.4+ utilizing Object-Oriented Programming (OOP).
* **Database:** MySQL 5.7+ with 15 relational tables, optimized with indexing for high-speed queries.
* **Frontend:** Custom Mobile-First CSS with **Dark/Light mode** persistence.



### 📊 Database Schema (`database/schema.sql`)
The system relies on a robust relational structure to handle users, wards, and complex complaint life cycles. Below is the core schema used:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('citizen', 'admin') DEFAULT 'citizen',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,               
    title VARCHAR(200),
    description TEXT,
    status ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending',
    ward_id INT,               
    category_id INT,           
    image_path VARCHAR(255),   
    latitude DECIMAL(10, 8),   
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE complaint_upvotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    complaint_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (complaint_id) REFERENCES complaints(id)
);
🔑 Configuration (config/database.php)
This file handles the secure connection to the MySQL server:

PHP
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); 
define('DB_NAME', 'civic_voice');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
🌊 The App Flow: From Report to Resolution
Citizen Action: A user logs in, snaps a photo of an issue, and submits it with GPS coordinates.

Processing: The report is categorized (e.g., "Road Damage") and routed to the correct department.

Community Validation: Other local residents can upvote the issue or leave comments to provide more context.

Admin Resolution: Officials view the queue, assign staff, and update the status. The citizen receives a real-time update on their dashboard.

🚀 How to Use It
👤 For Citizens
Sign Up: Create an account and select your local Ward.

Report: Hit the "+" button, upload a photo, and describe the problem.

Track: Check your Dashboard to see the progress of your ticket.

🛡️ For Admins
Login: Access the /admin portal.

Manage: View the complaints queue and assign tasks to departments like "Waste Management" or "Traffic".

Analyze: Use the Analytics tab to view monthly resolution rates and ward performance.

Developed with ❤️ by Surabhi BP
