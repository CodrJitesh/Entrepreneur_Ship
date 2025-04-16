<?php
session_start();
require_once 'backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all courses with mentor information
$stmt = $conn->prepare("
    SELECT c.*, p.full_name as mentor_name, p.headline as mentor_headline
    FROM courses c
    JOIN profiles p ON c.mentor_id = p.user_id
    ORDER BY c.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
$courses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch user's enrolled courses
$stmt = $conn->prepare("
    SELECT course_id 
    FROM course_enrollments 
    WHERE user_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$enrolledCourseIds = array_column($result->fetch_all(MYSQLI_ASSOC), 'course_id');
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - MentorConnect</title>
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
                <div class="max-w-7xl mx-auto">
                    <!-- Header -->
                    <div class="mb-8 animate-slide-in">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Available Courses</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">Enroll in courses to enhance your skills</p>
                    </div>

                    <!-- Search and Filter -->
                    <div class="bg-white dark:bg-dark-200 rounded-xl shadow-md p-6 mb-8 card-hover animate-scale-in">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Courses</label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        id="search"
                                        placeholder="Search by title or description"
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-300 text-gray-900 dark:text-white shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-400 focus:outline-none transition duration-150 ease-in-out px-4 py-2 input-focus"
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filter by Level</label>
                                <select
                                    id="level"
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-300 text-gray-900 dark:text-white shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-400 focus:outline-none transition duration-150 ease-in-out px-4 py-2 input-focus"
                                >
                                    <option value="">All Levels</option>
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Courses Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($courses as $course): ?>
                            <div class="bg-white dark:bg-dark-200 rounded-xl shadow-md overflow-hidden card-hover animate-scale-in">
                                <div class="p-6">
                                    <div class="flex items-center mb-4">
                                        <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                            <i class="fas fa-book text-2xl text-orange-600 dark:text-white"></i>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($course['title']); ?></h3>
                                            <p class="text-orange-600 dark:text-orange-400"><?php echo htmlspecialchars($course['mentor_name']); ?></p>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-400 mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <span class="px-3 py-1 bg-gray-100 dark:bg-dark-300 text-gray-800 dark:text-gray-200 rounded-full text-sm">
                                            <?php echo htmlspecialchars($course['level']); ?>
                                        </span>
                                        <span class="px-3 py-1 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded-full text-sm">
                                            <?php echo htmlspecialchars($course['duration']); ?>
                                        </span>
                                    </div>
                                    <?php if (in_array($course['id'], $enrolledCourseIds)): ?>
                                        <a href="course_details.php?id=<?php echo $course['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 btn-hover">
                                            Continue Learning
                                        </a>
                                    <?php else: ?>
                                        <a href="course_details.php?id=<?php echo $course['id']; ?>" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 btn-hover">
                                            View Course
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
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