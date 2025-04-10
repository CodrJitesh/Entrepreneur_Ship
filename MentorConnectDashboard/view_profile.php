<?php
session_start();
require_once 'backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get profile ID from URL
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($profile_id <= 0) {
    header("Location: mentor_directory.php");
    exit();
}

// Fetch profile data
$stmt = $conn->prepare("
    SELECT p.*, u.email, u.user_type 
    FROM profiles p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.user_id = ?
");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

if (!$profile) {
    header("Location: mentor_directory.php");
    exit();
}

// Set current page for navbar highlighting
$current_page = 'view_profile.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile['full_name']); ?> - MentorConnect</title>
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
        <?php include 'components/navbar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <div class="max-w-7xl mx-auto">
                    <!-- Theme Toggle -->
                    <div class="flex justify-end mb-4">
                        <button id="theme-toggle" class="p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-300 rounded-lg">
                            <i class="fas fa-moon"></i>
                        </button>
                    </div>

                    <!-- Profile Content -->
                    <div class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-8">
                        <!-- Profile Header -->
                        <div class="flex items-center mb-8">
                            <div class="w-24 h-24 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-4xl text-orange-600"></i>
                            </div>
                            <div class="ml-6">
                                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
                                    <?php echo htmlspecialchars($profile['full_name']); ?>
                                </h1>
                                <p class="text-xl text-orange-600">
                                    <?php echo htmlspecialchars($profile['headline']); ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-400">
                                    <?php echo htmlspecialchars($profile['industry']); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Profile Content -->
                        <div class="space-y-8">
                            <!-- Bio Section -->
                            <?php if ($profile['bio']): ?>
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">About</h2>
                                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                                        <?php echo nl2br(htmlspecialchars($profile['bio'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <!-- Expertise Section -->
                            <?php if ($profile['skills_or_needs']): ?>
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
                                        <?php echo $profile['user_type'] === 'mentor' ? 'Expertise' : 'Areas of Interest'; ?>
                                    </h2>
                                    <div class="flex flex-wrap gap-2">
                                        <?php 
                                        $skills = explode(',', $profile['skills_or_needs']);
                                        foreach ($skills as $skill):
                                            $skill = trim($skill);
                                            if ($skill):
                                        ?>
                                            <span class="px-4 py-2 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded-full">
                                                <?php echo htmlspecialchars($skill); ?>
                                            </span>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Contact Section -->
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Contact Information</h2>
                                <div class="space-y-2">
                                    <div class="flex items-center text-gray-600 dark:text-gray-400">
                                        <i class="fas fa-envelope mr-3"></i>
                                        <span><?php echo htmlspecialchars($profile['email']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Back Button -->
                        <div class="mt-8">
                            <a href="mentor_directory.php" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to Mentor Directory
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = themeToggle.querySelector('i');
        
        // Check for saved theme preference
        if (localStorage.getItem('theme') === 'dark' || 
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }
        
        themeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            const isDark = document.documentElement.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            themeIcon.classList.toggle('fa-moon');
            themeIcon.classList.toggle('fa-sun');
        });
    </script>
</body>
</html> 