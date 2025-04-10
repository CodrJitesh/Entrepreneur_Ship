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

                    <!-- Search and Filter Section -->
                    <div class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-6 mb-8">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <input type="text" id="search-input" placeholder="Search courses..." 
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-orange-500 focus:border-orange-500 dark:bg-dark-300">
                            </div>
                            <div class="w-full md:w-64">
                                <select id="level-filter" 
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-orange-500 focus:border-orange-500 dark:bg-dark-300">
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
                            <div class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-6 course-card" 
                                 data-title="<?php echo htmlspecialchars($course['title']); ?>"
                                 data-level="<?php echo htmlspecialchars($course['level']); ?>">
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
                                </div>
                                <div class="flex justify-end">
                                    <?php if (in_array($course['id'], $enrolledCourseIds)): ?>
                                        <span class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600">
                                            Enrolled
                                        </span>
                                    <?php else: ?>
                                        <a href="course_details.php?id=<?php echo $course['id']; ?>" 
                                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                            View Details
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

        // Search and filter functionality
        const searchInput = document.getElementById('search-input');
        const levelFilter = document.getElementById('level-filter');
        const courseCards = document.querySelectorAll('.course-card');

        function filterCourses() {
            const searchTerm = searchInput.value.toLowerCase();
            const levelTerm = levelFilter.value.toLowerCase();

            courseCards.forEach(card => {
                const title = card.dataset.title.toLowerCase();
                const level = card.dataset.level.toLowerCase();
                const matchesSearch = title.includes(searchTerm);
                const matchesLevel = !levelTerm || level === levelTerm;

                card.style.display = matchesSearch && matchesLevel ? 'block' : 'none';
            });
        }

        searchInput.addEventListener('input', filterCourses);
        levelFilter.addEventListener('change', filterCourses);
    </script>
</body>
</html> 