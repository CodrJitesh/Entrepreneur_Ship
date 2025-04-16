<?php
session_start();
require_once 'backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get mentor ID from URL
$mentor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch mentor details
$stmt = $conn->prepare("
    SELECT p.*, u.email
    FROM profiles p
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = ? AND u.user_type = 'mentor'
");
$stmt->bind_param("i", $mentor_id);
$stmt->execute();
$result = $stmt->get_result();
$mentor = $result->fetch_assoc();
$stmt->close();

if (!$mentor) {
    header("Location: mentor_directory.php");
    exit();
}

// Fetch mentor's courses
$stmt = $conn->prepare("
    SELECT c.* 
    FROM courses c
    WHERE c.mentor_id = ?
");
$stmt->bind_param("i", $mentor_id);
$stmt->execute();
$result = $stmt->get_result();
$courses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($mentor['full_name']); ?> - MentorConnect</title>
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
            <div class="absolute top-4 right-4">
                <button id="theme-toggle" class="p-3 rounded-lg bg-white dark:bg-dark-200 shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-110">
                    <i class="fas fa-moon text-gray-600 dark:text-gray-300"></i>
                </button>
            </div>

            <div class="p-8 animate-fade-in">
                <div class="max-w-4xl mx-auto">
                    <!-- Mentor Profile Header -->
                    <div class="bg-white dark:bg-dark-200 rounded-xl shadow-md p-8 mb-8 card-hover animate-scale-in">
                        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                            <div class="w-32 h-32 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                <span class="text-4xl font-bold text-orange-600 dark:text-white">
                                    <?php echo strtoupper(substr($mentor['full_name'], 0, 1)); ?>
                                </span>
                            </div>
                            <div class="flex-1 text-center md:text-left">
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                    <?php echo htmlspecialchars($mentor['full_name']); ?>
                                </h1>
                                <p class="text-orange-600 dark:text-orange-400 text-xl mb-4">
                                    <?php echo htmlspecialchars($mentor['headline']); ?>
                                </p>
                                <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                                    <span class="px-3 py-1 bg-gray-100 dark:bg-dark-300 text-gray-800 dark:text-gray-200 rounded-full text-sm">
                                        <?php echo htmlspecialchars($mentor['industry']); ?>
                                    </span>
                                    <?php 
                                    if (isset($mentor['skills_or_needs']) && !empty($mentor['skills_or_needs'])) {
                                        $skills = explode(',', $mentor['skills_or_needs']);
                                        foreach ($skills as $skill): 
                                    ?>
                                        <span class="px-3 py-1 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded-full text-sm">
                                            <?php echo htmlspecialchars(trim($skill)); ?>
                                        </span>
                                    <?php 
                                        endforeach;
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- About Section -->
                    <div class="bg-white dark:bg-dark-200 rounded-xl shadow-md p-8 mb-8 card-hover animate-scale-in">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">About</h2>
                        <p class="text-gray-600 dark:text-gray-400">
                            <?php echo htmlspecialchars($mentor['bio']); ?>
                        </p>
                    </div>

                    <!-- Courses Section -->
                    <div class="bg-white dark:bg-dark-200 rounded-xl shadow-md p-8 card-hover animate-scale-in">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Available Courses</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php foreach ($courses as $course): ?>
                                <div class="bg-gray-50 dark:bg-dark-300 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        <?php echo htmlspecialchars($course['title']); ?>
                                    </h3>
                                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                                        <?php echo htmlspecialchars($course['description']); ?>
                                    </p>
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <span class="px-3 py-1 bg-gray-100 dark:bg-dark-200 text-gray-800 dark:text-gray-200 rounded-full text-sm">
                                            <?php echo htmlspecialchars($course['level']); ?>
                                        </span>
                                        <span class="px-3 py-1 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded-full text-sm">
                                            <?php echo htmlspecialchars($course['duration']); ?>
                                        </span>
                                    </div>
                                    <a href="course_details.php?id=<?php echo $course['id']; ?>" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 btn-hover">
                                        View Course
                                    </a>
                                </div>
                            <?php endforeach; ?>
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
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        }
        
        themeToggle.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
                localStorage.setItem('theme', 'dark');
            }
        });

        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';
    </script>
</body>
</html> 