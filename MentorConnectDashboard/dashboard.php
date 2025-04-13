<?php
session_start();
require_once 'backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user profile data
$stmt = $conn->prepare("
    SELECT p.*, u.email, u.user_type
    FROM profiles p
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

// Fetch user's enrolled courses
$stmt = $conn->prepare("
    SELECT c.*, p.full_name as mentor_name
    FROM course_enrollments ce
    JOIN courses c ON ce.course_id = c.id
    JOIN profiles p ON c.mentor_id = p.user_id
    WHERE ce.user_id = ?
    ORDER BY ce.enrolled_at DESC
    LIMIT 3
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$enrolledCourses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MentorConnect</title>
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

                    <!-- Welcome Section -->
                    <div class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-6 mb-8">
                        <div class="flex items-center">
                            <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                <span class="text-2xl font-bold text-orange-600 dark:text-white">
                                    <?php echo strtoupper(substr($profile['full_name'], 0, 1)); ?>
                                </span>
                            </div>
                            <div class="ml-4">
                                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Welcome, <?php echo htmlspecialchars($profile['full_name']); ?>!</h1>
                                <p class="text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($profile['headline']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- My Courses Section -->
                    <div class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-6 mb-8">
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">My Courses</h2>
                        <?php if (empty($enrolledCourses)): ?>
                            <p class="text-gray-600 dark:text-gray-400">You haven't enrolled in any courses yet.</p>
                            <a href="courses.php" class="mt-4 inline-block bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700">
                                Browse Courses
                            </a>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php foreach ($enrolledCourses as $course): ?>
                                    <div class="bg-white dark:bg-dark-300 rounded-lg shadow-md p-6">
                                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">
                                            <?php echo htmlspecialchars($course['title']); ?>
                                        </h3>
                                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                                            <?php echo htmlspecialchars($course['description']); ?>
                                        </p>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                Mentor: <?php echo htmlspecialchars($course['mentor_name']); ?>
                                            </span>
                                            <a href="course_details.php?id=<?php echo $course['id']; ?>" class="text-orange-600 hover:text-orange-700">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <a href="mentor_directory.php" class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                    <i class="fas fa-users text-orange-600 dark:text-white"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Find a Mentor</h3>
                                    <p class="text-gray-600 dark:text-gray-400">Connect with experienced mentors</p>
                                </div>
                            </div>
                        </a>
                        <a href="courses.php" class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                    <i class="fas fa-book text-orange-600 dark:text-white"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Browse Courses</h3>
                                    <p class="text-gray-600 dark:text-gray-400">Explore available courses</p>
                                </div>
                            </div>
                        </a>
                        <a href="profile_edit.php" class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-edit text-orange-600 dark:text-white"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Update Profile</h3>
                                    <p class="text-gray-600 dark:text-gray-400">Keep your profile information up to date</p>
                                </div>
                            </div>
                        </a>
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