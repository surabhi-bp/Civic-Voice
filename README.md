# 🏛️ CivicVoice
### *Empowering Communities through AI-Driven Governance*

---

## 🧐 What is CivicVoice?
**CivicVoice** is a comprehensive, responsive platform designed to bridge the communication gap between citizens and local government. 

In many cities, reporting a broken streetlight or a massive pothole feels like shouting into a void. CivicVoice turns that "shout" into a **tracked, visible, and actionable digital ticket**. It’s about accountability: citizens report, the system categorizes, and officials resolve.

## 🚨 Why do we need this?
* **End the "Paperwork Black Hole":** Traditional complaints get lost. CivicVoice provides real-time status tracking from **Pending ➡️ In Progress ➡️ Resolved**.
* **Community Backing:** Through the **Upvote system**, citizens can show officials which issues are the most urgent for the neighborhood.
* **Data-Driven Governance:** Administrators get an analytics dashboard to see "hotspots," helping them allocate budget and staff where they are needed most.

---

## 📸 Project Visuals & App Flow

### 1. The Citizen Journey
From discovery to detailed reporting, the citizen interface is built for speed and clarity.

| **Landing Page** | **Reporting Interface** |
| :---: | :---: |
| ![Landing Page](https://github.com/user-attachments/assets/698291f3-84f6-4627-91be-cc3f01126dd1) | ![Report Issue](https://github.com/user-attachments/assets/d0b11c26-0328-4a82-b659-60ce3fda47ec) |
| *Modern hero section showcasing core platform value.* | *Detailed form with photo upload and GPS location mapping.* |

| **My Dashboard** | **Citizen Login** |
| :---: | :---: |
| ![Dashboard](https://github.com/user-attachments/assets/b6eb0761-0139-4572-b6b1-41b6573b5dba) | ![Login](https://github.com/user-attachments/assets/39762adc-3a11-4ece-9870-0a97f9ed7722) |
| *Real-time tracking of submitted complaints and personal stats.* | *Secure authentication for residents.* |

---

### 2. The Administrator Portal
A powerful backend for municipal officials to manage resources and track city performance.

| **Admin Dashboard** | **Complaint Triage** |
| :---: | :---: |
| ![Admin Stats](https://github.com/user-attachments/assets/de6fb018-2965-4290-a2ff-623b056d1aa6) | ![Management](https://github.com/user-attachments/assets/1641799a-d8eb-45bb-b1d8-5d4f3d2cc9ea) |
| *KPI cards for Resolution Rates and active management.* | *Detailed view for department assignment and resolution notes.* |

| **Analytics & Reports** | **Official Access** |
| :---: | :---: |
| ![Analytics](https://github.com/user-attachments/assets/f3e659f0-d51a-4b4e-9a99-3041a425571d) | ![Admin Login](https://github.com/user-attachments/assets/a61f2fcd-9005-4d49-a6e6-27987fade624) |
| *Deep dive into ward-wise hotspots and resolution times.* | *Restricted access portal for authorized government staff.* |

---

## ⚙️ Technical Blueprint

### 🏗️ The Infrastructure
* **Server:** Apache (XAMPP Environment).
* **Backend:** PHP 7.4+ utilizing Object-Oriented Programming (OOP).
* **Database:** MySQL 5.7+ with 15 relational tables, optimized with indexing.
* **Frontend:** Custom Mobile-First CSS with **Dark/Light mode** persistence.

### 📊 The Database Heart (`database/schema.sql`)
```sql
CREATE TABLE complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,               -- Links to Citizen
    title VARCHAR(200),
    status ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending',
    ward_id INT,               -- City District tracking
    category_id INT,           -- AI-ready categorization
    image_path VARCHAR(255),   -- Visual evidence
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
🌊 The Workflow Explained
Citizen Action: A user logs in, snaps a photo of an issue, and submits it with GPS coordinates.

AI/System Processing: The report is categorized (e.g., "Road Damage") and routed to the correct department.

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
