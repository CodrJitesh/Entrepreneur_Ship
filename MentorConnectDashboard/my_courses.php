<?php
session_start();
require_once 'backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's enrolled courses
$stmt = $conn->prepare("
    SELECT c.*, p.full_name as mentor_name, p.headline as mentor_headline, ce.status
    FROM course_enrollments ce
    JOIN courses c ON ce.course_id = c.id
    JOIN profiles p ON c.mentor_id = p.user_id
    WHERE ce.user_id = ?
    ORDER BY ce.enrolled_at DESC
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
    <title>My Courses - MentorConnect</title>
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

                    <!-- My Courses Section -->
                    <div class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-6 mb-8">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">My Courses</h1>
                        
                        <?php if (empty($enrolledCourses)): ?>
                            <div class="text-center py-12">
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
                                    <div class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-6">
                                        <div class="flex items-center mb-4">
                                            <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                                <i class="fas fa-book text-2xl text-orange-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                                    <?php echo htmlspecialchars($course['title']); ?>
                                                </h3>
                                                <p class="text-orange-600">
                                                    <?php echo htmlspecialchars($course['mentor_name']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                                            <?php echo htmlspecialchars($course['description']); ?>
                                        </p>
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            <span class="inline-block bg-gray-100 dark:bg-dark-300 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                <?php echo htmlspecialchars($course['duration']); ?>
                                            </span>
                                            <span class="inline-block bg-gray-100 dark:bg-dark-300 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                <?php echo htmlspecialchars($course['level']); ?>
                                            </span>
                                            <span class="inline-block <?php echo $course['status'] === 'completed' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200'; ?> rounded-full px-3 py-1 text-sm font-semibold">
                                                <?php echo ucfirst($course['status']); ?>
                                            </span>
                                        </div>
                                        <div class="flex justify-end">
                                            <a href="course_details.php?id=<?php echo $course['id']; ?>" 
                                               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                                View Course
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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