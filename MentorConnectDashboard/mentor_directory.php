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
                                <input type="text" id="search-input" placeholder="Search mentors..." 
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-orange-500 focus:border-orange-500 dark:bg-dark-300">
                            </div>
                            <div class="w-full md:w-64">
                                <select id="industry-filter" 
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-orange-500 focus:border-orange-500 dark:bg-dark-300">
                                    <option value="">All Industries</option>
                                    <?php
                                    $industries = array_unique(array_column($mentors, 'industry'));
                                    foreach ($industries as $industry):
                                    ?>
                                        <option value="<?php echo htmlspecialchars($industry); ?>">
                                            <?php echo htmlspecialchars($industry); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Mentors Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($mentors as $mentor): ?>
                            <div class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-6 mentor-card" 
                                 data-name="<?php echo htmlspecialchars($mentor['full_name']); ?>"
                                 data-industry="<?php echo htmlspecialchars($mentor['industry']); ?>">
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
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="inline-block bg-gray-100 dark:bg-dark-300 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 dark:text-gray-300">
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
        const industryFilter = document.getElementById('industry-filter');
        const mentorCards = document.querySelectorAll('.mentor-card');

        function filterMentors() {
            const searchTerm = searchInput.value.toLowerCase();
            const industryTerm = industryFilter.value.toLowerCase();

            mentorCards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const industry = card.dataset.industry.toLowerCase();
                const matchesSearch = name.includes(searchTerm);
                const matchesIndustry = !industryTerm || industry === industryTerm;

                card.style.display = matchesSearch && matchesIndustry ? 'block' : 'none';
            });
        }

        searchInput.addEventListener('input', filterMentors);
        industryFilter.addEventListener('change', filterMentors);
    </script>
</body>
</html> 