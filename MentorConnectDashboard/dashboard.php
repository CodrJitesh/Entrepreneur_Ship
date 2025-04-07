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
                    <a href="courses.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-300 rounded-lg">
                        <i class="fas fa-book mr-3"></i>
                        Courses
                    </a>
                    <a href="my_courses.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-300 rounded-lg">
                        <i class="fas fa-graduation-cap mr-3"></i>
                        My Courses
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
                                <span class="text-2xl font-bold text-orange-600">
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
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">My Courses</h2>
                            <a href="my_courses.php" class="text-orange-600 hover:text-orange-700">View All</a>
                        </div>
                        <?php if (empty($enrolledCourses)): ?>
                            <div class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-6 text-center">
                                <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-graduation-cap text-2xl text-orange-600"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No courses enrolled yet</h3>
                                <p class="text-gray-500 dark:text-gray-400 mb-4">Browse our available courses and start learning today!</p>
                                <a href="courses.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                    <i class="fas fa-book mr-2"></i>
                                    Browse Courses
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php foreach ($enrolledCourses as $course): ?>
                                    <div class="bg-white dark:bg-dark-200 rounded-lg shadow-md overflow-hidden">
                                        <div class="p-6">
                                            <div class="flex items-center mb-4">
                                                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-book text-orange-600"></i>
                                                </div>
                                                <div class="ml-4">
                                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                                        <?php echo htmlspecialchars($course['title']); ?>
                                                    </h3>
                                                    <p class="text-sm text-orange-600">
                                                        <?php echo htmlspecialchars($course['mentor_name']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex flex-wrap gap-2 mb-4">
                                                <span class="inline-block bg-gray-100 dark:bg-dark-300 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                    <?php echo htmlspecialchars($course['duration']); ?>
                                                </span>
                                                <span class="inline-block bg-gray-100 dark:bg-dark-300 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                    <?php echo htmlspecialchars($course['level']); ?>
                                                </span>
                                            </div>
                                            <div class="flex justify-end">
                                                <a href="course_details.php?id=<?php echo $course['id']; ?>"
                                                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                                    View Course
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <a href="mentor_directory.php" class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                    <i class="fas fa-users text-orange-600"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Find a Mentor</h3>
                                    <p class="text-gray-600 dark:text-gray-400">Browse our directory of experienced mentors</p>
                                </div>
                            </div>
                        </a>
                        <a href="courses.php" class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                    <i class="fas fa-book text-orange-600"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Browse Courses</h3>
                                    <p class="text-gray-600 dark:text-gray-400">Explore our available courses</p>
                                </div>
                            </div>
                        </a>
                        <a href="profile_edit.php" class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-edit text-orange-600"></i>
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