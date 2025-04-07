<?php
session_start();
require_once 'backend/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch current profile data
$stmt = $conn->prepare("SELECT * FROM profiles WHERE user_id = ?");
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
            $success = 'Profile updated successfully!';
            // Refresh profile data
            $profile['full_name'] = $full_name;
            $profile['headline'] = $headline;
            $profile['bio'] = $bio;
            $profile['industry'] = $industry;
            $profile['skills_or_needs'] = $skills_or_needs;
        } else {
            $error = 'Failed to update profile. Please try again.';
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
                    <a href="mentor_directory.php" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-users mr-3"></i>
                        Mentors
                    </a>
                    <a href="profile_edit.php" class="flex items-center px-4 py-2 text-gray-700 bg-orange-100 rounded-lg">
                        <i class="fas fa-user-edit mr-3"></i>
                        Edit Profile
                    </a>
                    <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
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
            <div class="max-w-2xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Profile</h1>

                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="profile_edit.php" class="space-y-6">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required
                               value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                    </div>

                    <div>
                        <label for="headline" class="block text-sm font-medium text-gray-700">Headline</label>
                        <input type="text" id="headline" name="headline"
                               value="<?php echo htmlspecialchars($profile['headline'] ?? ''); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                               placeholder="e.g., Marketing Expert or Tech Startup Founder">
                    </div>

                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                        <textarea id="bio" name="bio" rows="4"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                  placeholder="Tell us about yourself..."><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                    </div>

                    <div>
                        <label for="industry" class="block text-sm font-medium text-gray-700">Industry</label>
                        <input type="text" id="industry" name="industry"
                               value="<?php echo htmlspecialchars($profile['industry'] ?? ''); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                               placeholder="e.g., Technology, Healthcare, Finance">
                    </div>

                    <div>
                        <label for="skills_or_needs" class="block text-sm font-medium text-gray-700">
                            <?php echo $_SESSION['user_type'] === 'mentor' ? 'Skills' : 'Areas of Interest'; ?>
                        </label>
                        <textarea id="skills_or_needs" name="skills_or_needs" rows="4"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                  placeholder="<?php echo $_SESSION['user_type'] === 'mentor' ? 'List your expertise areas...' : 'What areas would you like mentorship in?'; ?>"><?php echo htmlspecialchars($profile['skills_or_needs'] ?? ''); ?></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 