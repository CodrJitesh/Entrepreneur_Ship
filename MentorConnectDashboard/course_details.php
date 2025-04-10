<?php
session_start();
require_once 'backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Set current page for navbar highlighting
$current_page = 'course_details.php';

// Get course ID from URL
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch course details with mentor information
$stmt = $conn->prepare("
    SELECT c.*, p.full_name as mentor_name, p.headline as mentor_headline, p.bio as mentor_bio
    FROM courses c
    JOIN profiles p ON c.mentor_id = p.user_id
    WHERE c.id = ?
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course) {
    header("Location: courses.php");
    exit();
}

// Check if user is already enrolled
$stmt = $conn->prepare("
    SELECT status 
    FROM course_enrollments 
    WHERE course_id = ? AND user_id = ?
");
$stmt->bind_param("ii", $course_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$enrollment = $result->fetch_assoc();
$stmt->close();

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$enrollment) {
    $stmt = $conn->prepare("
        INSERT INTO course_enrollments (course_id, user_id) 
        VALUES (?, ?)
    ");
    $stmt->bind_param("ii", $course_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        header("Location: course_details.php?id=" . $course_id . "&enrolled=1");
        exit();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - MentorConnect</title>
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
        <?php include 'components/navbar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <div class="p-8">
                <div class="max-w-4xl mx-auto">
                    <?php if (isset($_GET['enrolled'])): ?>
                        <div class="mb-6 p-4 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg">
                            <i class="fas fa-check-circle mr-2"></i>
                            Successfully enrolled in the course!
                        </div>
                    <?php endif; ?>

                    <div class="bg-white dark:bg-dark-200 rounded-lg shadow-md overflow-hidden mb-8">
                        <div class="p-6">
                            <div class="flex items-center mb-6">
                                <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                    <i class="fas fa-book text-2xl text-orange-600"></i>
                                </div>
                                <div class="ml-4">
                                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                                        <?php echo htmlspecialchars($course['title']); ?>
                                    </h1>
                                    <p class="text-orange-600">
                                        <?php echo htmlspecialchars($course['mentor_name']); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Course Description</h2>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        <?php echo htmlspecialchars($course['description']); ?>
                                    </p>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Course Details</h2>
                                    <div class="space-y-2">
                                        <div class="flex items-center text-gray-600 dark:text-gray-400">
                                            <i class="fas fa-clock mr-2"></i>
                                            <span><?php echo htmlspecialchars($course['duration']); ?></span>
                                        </div>
                                        <div class="flex items-center text-gray-600 dark:text-gray-400">
                                            <i class="fas fa-signal mr-2"></i>
                                            <span><?php echo htmlspecialchars($course['level']); ?> Level</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">About the Mentor</h2>
                                <div class="flex items-start">
                                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-xl font-bold text-orange-600">
                                            <?php echo strtoupper(substr($course['mentor_name'], 0, 1)); ?>
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                            <?php echo htmlspecialchars($course['mentor_name']); ?>
                                        </h3>
                                        <p class="text-orange-600 mb-2">
                                            <?php echo htmlspecialchars($course['mentor_headline']); ?>
                                        </p>
                                        <p class="text-gray-600 dark:text-gray-400">
                                            <?php echo htmlspecialchars($course['mentor_bio']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <?php if ($enrollment): ?>
                                <div class="flex justify-end">
                                    <a href="my_courses.php"
                                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <i class="fas fa-graduation-cap mr-2"></i>
                                        Go to My Courses
                                    </a>
                                </div>
                            <?php else: ?>
                                <form method="POST" class="flex justify-end">
                                    <button type="submit"
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                        <i class="fas fa-plus mr-2"></i>
                                        Enroll in Course
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle functionality
        const html = document.documentElement;
        
        // Check for saved theme preference or use system preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
    </script>
</body>
</html> 