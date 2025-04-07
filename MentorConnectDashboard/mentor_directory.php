<?php
session_start();
require_once 'backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch mentors
$stmt = $conn->prepare("
    SELECT p.*, u.email
    FROM profiles p
    JOIN users u ON p.user_id = u.id
    WHERE u.user_type = 'mentor'
    ORDER BY p.full_name
");
$stmt->execute();
$result = $stmt->get_result();
$mentors = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Directory - MentorConnect</title>
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
                    <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-dark-300 rounded-lg">
                        <i class="fas fa-home mr-3"></i>
                        Home
                    </a>
                    <a href="mentor_directory.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 bg-orange-100 dark:bg-orange-900 rounded-lg">
                        <i class="fas fa-users mr-3"></i>
                        Mentors
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
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-8">Mentor Directory</h1>

                    <!-- Search and Filter -->
                    <div class="mb-8">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <input type="text" id="search" placeholder="Search mentors..."
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-dark-300 dark:text-white">
                            </div>
                            <div class="w-full md:w-64">
                                <select id="industry" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-dark-300 dark:text-white">
                                    <option value="">All Industries</option>
                                    <option value="Technology">Technology</option>
                                    <option value="Healthcare">Healthcare</option>
                                    <option value="Finance">Finance</option>
                                    <option value="Education">Education</option>
                                    <option value="Marketing">Marketing</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Mentors Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="mentor-grid">
                        <?php foreach ($mentors as $mentor): ?>
                            <div class="bg-white dark:bg-dark-200 rounded-lg shadow-md overflow-hidden mentor-card"
                                 data-name="<?php echo htmlspecialchars($mentor['full_name']); ?>"
                                 data-industry="<?php echo htmlspecialchars($mentor['industry']); ?>">
                                <div class="p-6">
                                    <div class="flex items-center mb-4">
                                        <div class="w-16 h-16 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center">
                                            <span class="text-2xl font-bold text-orange-600">
                                                <?php echo strtoupper(substr($mentor['full_name'], 0, 1)); ?>
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                                <?php echo htmlspecialchars($mentor['full_name']); ?>
                                            </h3>
                                            <p class="text-orange-600">
                                                <?php echo htmlspecialchars($mentor['headline']); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                                        <?php echo htmlspecialchars($mentor['bio']); ?>
                                    </p>
                                    <div class="mb-4">
                                        <span class="inline-block bg-gray-100 dark:bg-dark-300 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 dark:text-gray-300 mr-2">
                                            <?php echo htmlspecialchars($mentor['industry']); ?>
                                        </span>
                                    </div>
                                    <div class="flex justify-end">
                                        <a href="view_profile.php?id=<?php echo $mentor['user_id']; ?>"
                                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                            View Profile
                                        </a>
                                    </div>
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
        const html = document.documentElement;
        
        // Check for saved theme preference or use system preference
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        // Search and filter functionality
        const searchInput = document.getElementById('search');
        const industrySelect = document.getElementById('industry');
        const mentorCards = document.querySelectorAll('.mentor-card');

        function filterMentors() {
            const searchTerm = searchInput.value.toLowerCase();
            const industryFilter = industrySelect.value;

            mentorCards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const industry = card.dataset.industry;

                const matchesSearch = name.includes(searchTerm);
                const matchesIndustry = !industryFilter || industry === industryFilter;

                if (matchesSearch && matchesIndustry) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterMentors);
        industrySelect.addEventListener('change', filterMentors);
    </script>
</body>
</html> 