<?php
session_start();
require_once 'backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all mentors with their profile information
$stmt = $conn->prepare("
    SELECT p.*, u.email 
    FROM profiles p 
    JOIN users u ON p.user_id = u.id 
    WHERE u.user_type = 'mentor'
    ORDER BY p.full_name ASC
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg">
            <div class="p-4">
                <h1 class="text-2xl font-bold text-orange-600">MentorConnect</h1>
            </div>
            <nav class="mt-6">
                <div class="px-4 space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-home mr-3"></i>
                        Home
                    </a>
                    <a href="mentor_directory.php" class="flex items-center px-4 py-2 text-gray-700 bg-orange-100 rounded-lg">
                        <i class="fas fa-users mr-3"></i>
                        Mentors
                    </a>
                    <a href="profile_edit.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-user-edit mr-3"></i>
                        Edit Profile
                    </a>
                    <a href="contact_admin.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-envelope mr-3"></i>
                        Contact Admin
                    </a>
                </div>
            </nav>
            <div class="absolute bottom-0 w-64 p-4">
                <a href="logout.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    Sign Out
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Mentor Directory</h1>

                <!-- Search and Filter -->
                <div class="mb-6">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search mentors by name, industry, or skills..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <!-- Mentors Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="mentorsGrid">
                    <?php foreach ($mentors as $mentor): ?>
                        <div class="bg-white rounded-lg shadow-md p-6 mentor-card" 
                             data-name="<?php echo htmlspecialchars($mentor['full_name']); ?>"
                             data-industry="<?php echo htmlspecialchars($mentor['industry']); ?>"
                             data-skills="<?php echo htmlspecialchars($mentor['skills_or_needs']); ?>">
                            <div class="flex items-center mb-4">
                                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-2xl text-orange-600"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-xl font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($mentor['full_name']); ?>
                                    </h3>
                                    <p class="text-orange-600">
                                        <?php echo htmlspecialchars($mentor['headline']); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <?php if ($mentor['industry']): ?>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-briefcase mr-2"></i>
                                        <span><?php echo htmlspecialchars($mentor['industry']); ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($mentor['bio']): ?>
                                    <p class="text-gray-600 line-clamp-3">
                                        <?php echo htmlspecialchars($mentor['bio']); ?>
                                    </p>
                                <?php endif; ?>

                                <?php if ($mentor['skills_or_needs']): ?>
                                    <div class="mt-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Expertise</h4>
                                        <div class="flex flex-wrap gap-2">
                                            <?php 
                                            $skills = explode(',', $mentor['skills_or_needs']);
                                            foreach ($skills as $skill):
                                                $skill = trim($skill);
                                                if ($skill):
                                            ?>
                                                <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm">
                                                    <?php echo htmlspecialchars($skill); ?>
                                                </span>
                                            <?php 
                                                endif;
                                            endforeach; 
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-6">
                                <a href="view_profile.php?id=<?php echo $mentor['user_id']; ?>" 
                                   class="block w-full text-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($mentors)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-medium text-gray-900">No mentors found</h3>
                        <p class="mt-1 text-gray-500">There are currently no mentors available.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const mentorCards = document.querySelectorAll('.mentor-card');

            mentorCards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const industry = card.dataset.industry.toLowerCase();
                const skills = card.dataset.skills.toLowerCase();

                if (name.includes(searchTerm) || 
                    industry.includes(searchTerm) || 
                    skills.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html> 