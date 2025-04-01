<?php
session_start();
// Simulate a logged-in user
$_SESSION['user_id'] = 1;
$_SESSION['username'] = "Entrepreneur";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MentorConnect Dashboard V2</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">MentorConnect</div>
        <ul>
            <li><a href="#" class="active">Home</a></li>
            <li><a href="#">Mentors</a></li>
            <li><a href="#">Progress</a></li>
            <li><a href="#">Resources</a></li>
            <li><a href="#">Feedback</a></li>
        </ul>
        <div class="sidebar-footer">
            <a href="#">Sign Out</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="search-bar">
                <input type="text" placeholder="Search for mentors or resources...">
            </div>
            <div class="theme-toggle">
                <button id="theme-toggle">Toggle Theme</button>
            </div>
            <div class="user-profile">
                <span>Welcome, <?php echo $_SESSION['username']; ?>!</span>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Welcome Banner -->
            <div class="banner">
                <h1>Find Your Perfect Mentor</h1>
                <p>Connect with experienced entrepreneurs to grow your business.</p>
            </div>

            <!-- Recommended Mentors -->
            <div class="section">
                <h2>Recommended Mentors</h2>
                <div class="mentor-grid" id="mentor-grid">
                    <!-- Mentors will be loaded dynamically via JavaScript -->
                </div>
            </div>

            <!-- Progress Tracking -->
            <div class="section">
                <h2>Your Progress</h2>
                <div class="progress-list" id="progress-list">
                    <!-- Progress will be loaded dynamically via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
</body>
</html>