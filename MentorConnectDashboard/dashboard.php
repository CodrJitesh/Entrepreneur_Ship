<?php
session_start();
require_once 'backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user profile data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT p.*, u.user_type FROM profiles p JOIN users u ON p.user_id = u.id WHERE p.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MentorConnect Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            '100': '#1a1a1a',
                            '200': '#2d2d2d',
                            '300': '#404040',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-dark-100 dark:text-white">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white dark:bg-dark-200 shadow-lg">
            <div class="p-4">
                <h1 class="text-2xl font-bold text-orange-600">MentorConnect</h1>
            </div>
            <nav class="mt-6">
                <div class="px-4 space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 bg-orange-100 dark:bg-orange-900 rounded-lg">
                        <i class="fas fa-home mr-3"></i>
                        Home
                    </a>
                    <a href="mentor_directory.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-300 rounded-lg">
                        <i class="fas fa-users mr-3"></i>
                        Mentors
                    </a>
                    <a href="profile_edit.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-300 rounded-lg">
                        <i class="fas fa-user-edit mr-3"></i>
                        Edit Profile
                    </a>
                    <a href="contact_admin.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-300 rounded-lg">
                        <i class="fas fa-envelope mr-3"></i>
                        Contact Admin
                    </a>
                </div>
            </nav>
            <div class="absolute bottom-0 w-64 p-4">
                <a href="logout.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-300 rounded-lg">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    Sign Out
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Top Bar -->
            <div class="bg-white dark:bg-dark-200 shadow-sm">
                <div class="flex items-center justify-between p-4">
                    <div class="flex-1 max-w-xl">
                        <div class="relative">
                            <input type="text" placeholder="Search for mentors or resources..." 
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-dark-300 dark:text-white">
                            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button id="theme-toggle" class="p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-300 rounded-lg">
                            <i class="fas fa-moon"></i>
                        </button>
                        <div class="flex items-center">
                            <span class="text-gray-700 dark:text-gray-300">Welcome, <?php echo htmlspecialchars($profile['full_name'] ?? 'User'); ?>!</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="p-6">
                <!-- Welcome Banner -->
                <div class="bg-white dark:bg-dark-200 rounded-lg shadow-sm p-8 mb-6 text-center">
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">Find Your Perfect Mentor</h1>
                    <p class="text-gray-600 dark:text-gray-400">Connect with experienced entrepreneurs to grow your business.</p>
                </div>

                <!-- Recommended Mentors -->
                <div class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Recommended Mentors</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="mentor-grid">
                        <!-- Mentors will be loaded dynamically via JavaScript -->
                    </div>
                </div>

                <!-- Progress Tracking -->
                <div class="mb-8">
                    <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Your Progress</h2>
                    <div class="space-y-4" id="progress-list">
                        <!-- Progress will be loaded dynamically via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;
        
        // Check for saved theme preference or use system preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
            themeToggle.querySelector('i').classList.replace('fa-moon', 'fa-sun');
        } else {
            html.classList.remove('dark');
            themeToggle.querySelector('i').classList.replace('fa-sun', 'fa-moon');
        }
        
        themeToggle.addEventListener('click', () => {
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.theme = 'light';
                themeToggle.querySelector('i').classList.replace('fa-sun', 'fa-moon');
            } else {
                html.classList.add('dark');
                localStorage.theme = 'dark';
                themeToggle.querySelector('i').classList.replace('fa-moon', 'fa-sun');
            }
        });
    </script>
</body>
</html>