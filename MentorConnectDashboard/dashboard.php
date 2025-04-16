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
    SELECT c.*, p.full_name as mentor_name, ce.status
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

// Calculate progress based on completed courses
$completedCourses = 0;
$courseCount = count($enrolledCourses);
if ($courseCount > 0) {
    foreach ($enrolledCourses as $course) {
        if ($course['status'] === 'completed') {
            $completedCourses++;
        }
    }
    $progressPercentage = round(($completedCourses / $courseCount) * 100);
} else {
    $progressPercentage = 0;
}

// Calculate achievement count based on completed courses
$achievementCount = $completedCourses;
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
                <!-- Welcome Section -->
                <div class="mb-8 animate-slide-in">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome back, <?php echo htmlspecialchars($profile['full_name']); ?>!</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">Here's what's happening with your mentorship journey</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white dark:bg-dark-200 rounded-xl shadow-md p-6 card-hover animate-scale-in delay-100">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-orange-100 dark:bg-orange-900">
                                <i class="fas fa-graduation-cap text-orange-600 dark:text-orange-400 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Enrolled Courses</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo count($enrolledCourses); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-dark-200 rounded-xl shadow-md p-6 card-hover animate-scale-in delay-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                                <i class="fas fa-chart-line text-blue-600 dark:text-blue-400 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Progress</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $progressPercentage; ?>%</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-dark-200 rounded-xl shadow-md p-6 card-hover animate-scale-in delay-300">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                                <i class="fas fa-trophy text-green-600 dark:text-green-400 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Achievements</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white"><?php echo $achievementCount; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Courses Section -->
                <div class="mb-8 animate-slide-in">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">My Courses</h2>
                        <a href="courses.php" class="text-orange-600 hover:text-orange-500 nav-link">
                            Browse All Courses
                        </a>
                    </div>
                    <?php if (empty($enrolledCourses)): ?>
                        <div class="bg-white dark:bg-dark-200 rounded-xl shadow-md p-8 text-center card-hover animate-scale-in">
                            <div class="text-gray-500 dark:text-gray-400 mb-4">
                                <i class="fas fa-book-open text-4xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Courses Yet</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">Start your learning journey by enrolling in a course</p>
                            <a href="courses.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 btn-hover">
                                Browse Courses
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($enrolledCourses as $course): ?>
                                <div class="bg-white dark:bg-dark-200 rounded-xl shadow-md overflow-hidden card-hover animate-scale-in">
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($course['title']); ?></h3>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $course['level'] === 'Beginner' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($course['level'] === 'Intermediate' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'); ?>">
                                                <?php echo htmlspecialchars($course['level']); ?>
                                            </span>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-400 mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                <i class="fas fa-user mr-2"></i>
                                                <?php echo htmlspecialchars($course['mentor_name']); ?>
                                            </div>
                                            <a href="course_details.php?id=<?php echo $course['id']; ?>" class="text-orange-600 hover:text-orange-500 nav-link">
                                                Continue Learning
                                            </a>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-dark-300 px-6 py-4">
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-orange-600 h-2 rounded-full" style="width: <?php echo $course['status'] === 'completed' ? '100' : '50'; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="animate-slide-in">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <a href="mentor_directory.php" class="bg-white dark:bg-dark-200 rounded-xl shadow-md p-6 text-center card-hover animate-scale-in">
                            <div class="text-orange-600 dark:text-orange-400 mb-4">
                                <i class="fas fa-user-tie text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Find a Mentor</h3>
                            <p class="text-gray-600 dark:text-gray-400">Connect with experienced mentors</p>
                        </a>
                        <a href="courses.php" class="bg-white dark:bg-dark-200 rounded-xl shadow-md p-6 text-center card-hover animate-scale-in">
                            <div class="text-blue-600 dark:text-blue-400 mb-4">
                                <i class="fas fa-book text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Browse Courses</h3>
                            <p class="text-gray-600 dark:text-gray-400">Explore available courses</p>
                        </a>
                        <a href="profile_edit.php" class="bg-white dark:bg-dark-200 rounded-xl shadow-md p-6 text-center card-hover animate-scale-in">
                            <div class="text-green-600 dark:text-green-400 mb-4">
                                <i class="fas fa-user-edit text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Edit Profile</h3>
                            <p class="text-gray-600 dark:text-gray-400">Update your information</p>
                        </a>
                        <a href="contact_admin.php" class="bg-white dark:bg-dark-200 rounded-xl shadow-md p-6 text-center card-hover animate-scale-in">
                            <div class="text-purple-600 dark:text-purple-400 mb-4">
                                <i class="fas fa-envelope text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Contact Admin</h3>
                            <p class="text-gray-600 dark:text-gray-400">Get help and support</p>
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

        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';
    </script>
</body>
</html>