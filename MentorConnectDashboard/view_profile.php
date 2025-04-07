<?php
session_start();
require_once 'backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get profile ID from URL
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($profile_id <= 0) {
    header("Location: mentor_directory.php");
    exit();
}

// Fetch profile data
$stmt = $conn->prepare("
    SELECT p.*, u.email, u.user_type 
    FROM profiles p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.user_id = ?
");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

if (!$profile) {
    header("Location: mentor_directory.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile['full_name']); ?> - MentorConnect</title>
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
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-md p-8">
                    <!-- Profile Header -->
                    <div class="flex items-center mb-8">
                        <div class="w-24 h-24 bg-orange-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-4xl text-orange-600"></i>
                        </div>
                        <div class="ml-6">
                            <h1 class="text-3xl font-bold text-gray-800">
                                <?php echo htmlspecialchars($profile['full_name']); ?>
                            </h1>
                            <p class="text-xl text-orange-600">
                                <?php echo htmlspecialchars($profile['headline']); ?>
                            </p>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($profile['industry']); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Profile Content -->
                    <div class="space-y-8">
                        <!-- Bio Section -->
                        <?php if ($profile['bio']): ?>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 mb-4">About</h2>
                                <p class="text-gray-600 leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($profile['bio'])); ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Expertise Section -->
                        <?php if ($profile['skills_or_needs']): ?>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                                    <?php echo $profile['user_type'] === 'mentor' ? 'Expertise' : 'Areas of Interest'; ?>
                                </h2>
                                <div class="flex flex-wrap gap-2">
                                    <?php 
                                    $skills = explode(',', $profile['skills_or_needs']);
                                    foreach ($skills as $skill):
                                        $skill = trim($skill);
                                        if ($skill):
                                    ?>
                                        <span class="px-4 py-2 bg-orange-100 text-orange-800 rounded-full">
                                            <?php echo htmlspecialchars($skill); ?>
                                        </span>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Contact Section -->
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 mb-4">Contact Information</h2>
                            <div class="space-y-2">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-envelope mr-3"></i>
                                    <span><?php echo htmlspecialchars($profile['email']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="mt-8">
                        <a href="mentor_directory.php" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Mentor Directory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 