<?php
session_start();
require_once 'backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Set current page for navbar highlighting
$current_page = 'profile_edit.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch user profile data
$stmt = $conn->prepare("SELECT p.*, u.user_type FROM profiles p JOIN users u ON p.user_id = u.id WHERE p.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $headline = trim($_POST['headline'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $skills_or_needs = trim($_POST['skills_or_needs'] ?? '');

    if (empty($full_name)) {
        $error = 'Full name is required';
    } else {
        $stmt = $conn->prepare("UPDATE profiles SET full_name = ?, headline = ?, bio = ?, industry = ?, skills_or_needs = ? WHERE user_id = ?");
        $stmt->bind_param("sssssi", $full_name, $headline, $bio, $industry, $skills_or_needs, $user_id);
        
        if ($stmt->execute()) {
            $success = 'Profile updated successfully';
            // Refresh profile data
            $stmt = $conn->prepare("SELECT p.*, u.user_type FROM profiles p JOIN users u ON p.user_id = u.id WHERE p.user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $profile = $result->fetch_assoc();
        } else {
            $error = 'Error updating profile';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - MentorConnect</title>
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
    <link href="includes/animations.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-dark-100 dark:text-white min-h-screen">
    <div class="flex h-screen">
        <?php include 'components/navbar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Theme Toggle -->
            <div class="flex justify-end mb-4">
                <button id="theme-toggle" class="p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-300 rounded-lg">
                    <i class="fas fa-moon"></i>
                </button>
            </div>

            <div class="p-8 animate-fade-in">
                <div class="max-w-4xl mx-auto">
                    <!-- Header -->
                    <div class="mb-8 animate-slide-in">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Profile</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Update your profile information</p>
                    </div>

                    <!-- Profile Form -->
                    <div class="bg-white dark:bg-dark-200 rounded-xl shadow-md p-8 card-hover animate-scale-in">
                        <form method="POST" class="space-y-6">
                            <div class="space-y-4">
                                <div>
                                    <label for="full_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name</label>
                                    <input
                                        type="text"
                                        id="full_name"
                                        name="full_name"
                                        required
                                        placeholder="Enter your full name"
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-300 text-gray-900 dark:text-white shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-400 focus:outline-none transition duration-150 ease-in-out px-4 py-2 input-focus"
                                    />
                                </div>
                                <div>
                                    <label for="headline" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Headline</label>
                                    <input
                                        type="text"
                                        id="headline"
                                        name="headline"
                                        placeholder="Enter your headline"
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-300 text-gray-900 dark:text-white shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-400 focus:outline-none transition duration-150 ease-in-out px-4 py-2 input-focus"
                                    />
                                </div>
                                <div>
                                    <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bio</label>
                                    <textarea
                                        id="bio"
                                        name="bio"
                                        rows="4"
                                        placeholder="Tell us about yourself..."
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-300 text-gray-900 dark:text-white shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-400 focus:outline-none transition duration-150 ease-in-out px-4 py-2 input-focus"
                                    ></textarea>
                                </div>
                                <div>
                                    <label for="industry" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Industry</label>
                                    <input
                                        type="text"
                                        id="industry"
                                        name="industry"
                                        placeholder="Enter your industry"
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-300 text-gray-900 dark:text-white shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-400 focus:outline-none transition duration-150 ease-in-out px-4 py-2 input-focus"
                                    />
                                </div>
                                <div>
                                    <label for="skills_or_needs" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Skills or Needs</label>
                                    <textarea
                                        id="skills_or_needs"
                                        name="skills_or_needs"
                                        rows="4"
                                        placeholder="List your skills or areas where you need help..."
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-300 text-gray-900 dark:text-white shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-400 focus:outline-none transition duration-150 ease-in-out px-4 py-2 input-focus"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 btn-hover"
                                >
                                    Save Changes
                                </button>
                            </div>
                        </form>
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