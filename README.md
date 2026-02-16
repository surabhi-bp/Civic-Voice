🏛️ CivicVoice: The Future of Urban Transparency
Empowering Communities through AI-Driven Governance
What is CivicVoice?
CivicVoice is a comprehensive, responsive platform designed to bridge the communication gap between citizens and local government.

In many cities, reporting a broken streetlight or a massive pothole feels like shouting into a void. CivicVoice turns that "shout" into a tracked, visible, and actionable digital ticket. It’s about accountability: citizens report, the system categorizes, and officials resolve.

Why do we need this?
End the "Paperwork Black Hole": Traditional complaints get lost. CivicVoice provides real-time status tracking from Pending ➡️ In Progress ➡️ Resolved.
Community Backing: Through the Upvote system, citizens can show officials which issues are the most urgent for the neighborhood.
Data-Driven Governance: Administrators get an analytics dashboard to see "hotspots," helping them allocate budget and staff where they are needed most.
⚙️ Technical Blueprint
🏗️ The Infrastructure
Server: Apache (XAMPP Environment).

Backend: PHP 7.4+ utilizing Object-Oriented Programming (OOP).

Database: MySQL 5.7+ with 15 relational tables, optimized with indexing for high-speed queries.

Frontend: Custom Mobile-First CSS with Dark/Light mode persistence.

📊 The Database Heart (database/schema.sql)
The system relies on a robust relational structure to handle users, wards, and complex complaint life cycles:

SQL
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
🌊 The App Flow: From Report to Resolution
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
