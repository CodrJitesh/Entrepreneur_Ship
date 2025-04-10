<?php
session_start();
require_once 'backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Set current page for navbar highlighting
$current_page = 'profile_edit.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch user profile data
$stmt = $conn->prepare("SELECT p.*, u.user_type FROM profiles p JOIN users u ON p.user_id = u.id WHERE p.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $headline = trim($_POST['headline'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $skills_or_needs = trim($_POST['skills_or_needs'] ?? '');

    if (empty($full_name)) {
        $error = 'Full name is required';
    } else {
        $stmt = $conn->prepare("UPDATE profiles SET full_name = ?, headline = ?, bio = ?, industry = ?, skills_or_needs = ? WHERE user_id = ?");
        $stmt->bind_param("sssssi", $full_name, $headline, $bio, $industry, $skills_or_needs, $user_id);
        
        if ($stmt->execute()) {
            $success = 'Profile updated successfully';
            // Refresh profile data
            $stmt = $conn->prepare("SELECT p.*, u.user_type FROM profiles p JOIN users u ON p.user_id = u.id WHERE p.user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $profile = $result->fetch_assoc();
        } else {
            $error = 'Error updating profile';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - MentorConnect</title>
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
                <div class="max-w-2xl mx-auto">
                    <div class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-8">
                        <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Edit Profile</h1>

                        <?php if ($error): ?>
                            <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded relative mb-4" role="alert">
                                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded relative mb-4" role="alert">
                                <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-6">
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                                <input type="text" id="full_name" name="full_name" required
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-orange-500 focus:ring-orange-500 dark:bg-dark-300 dark:text-white"
                                       value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>">
                            </div>

                            <div>
                                <label for="headline" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Headline</label>
                                <input type="text" id="headline" name="headline"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-orange-500 focus:ring-orange-500 dark:bg-dark-300 dark:text-white"
                                       value="<?php echo htmlspecialchars($profile['headline'] ?? ''); ?>"
                                       placeholder="e.g., Software Engineer, Entrepreneur">
                            </div>

                            <div>
                                <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bio</label>
                                <textarea id="bio" name="bio" rows="4"
                                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-orange-500 focus:ring-orange-500 dark:bg-dark-300 dark:text-white"
                                          placeholder="Tell us about yourself..."><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                            </div>

                            <div>
                                <label for="industry" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Industry</label>
                                <input type="text" id="industry" name="industry"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-orange-500 focus:ring-orange-500 dark:bg-dark-300 dark:text-white"
                                       value="<?php echo htmlspecialchars($profile['industry'] ?? ''); ?>"
                                       placeholder="e.g., Technology, Healthcare, Finance">
                            </div>

                            <div>
                                <label for="skills_or_needs" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <?php echo $profile['user_type'] === 'mentor' ? 'Skills' : 'Areas of Interest'; ?>
                                </label>
                                <textarea id="skills_or_needs" name="skills_or_needs" rows="4"
                                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-orange-500 focus:ring-orange-500 dark:bg-dark-300 dark:text-white"
                                          placeholder="<?php echo $profile['user_type'] === 'mentor' ? 'List your skills and expertise...' : 'What areas would you like to learn about?'; ?>"><?php echo htmlspecialchars($profile['skills_or_needs'] ?? ''); ?></textarea>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                        class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                    Save Changes
                                </button>
                            </div>
                        </form>
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