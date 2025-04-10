<?php
// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
// Get the current page from the URL if not provided
$current_page = isset($current_page) ? $current_page : basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<div class="w-64 bg-white dark:bg-dark-200 shadow-lg">
    <div class="p-4">
        <h1 class="text-2xl font-bold text-orange-600">MentorConnect</h1>
    </div>
    <nav class="mt-6">
        <div class="px-4 space-y-2">
            
            <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 <?php echo $current_page === 'dashboard.php' ? 'bg-orange-100 dark:bg-orange-900' : 'hover:bg-gray-100 dark:hover:bg-dark-300'; ?> rounded-lg">
                <i class="fas fa-home mr-3"></i>
                Home
            </a>
            <a href="mentor_directory.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 <?php echo $current_page === 'mentor_directory.php' || $current_page === 'view_profile.php' ? 'bg-orange-100 dark:bg-orange-900' : 'hover:bg-gray-100 dark:hover:bg-dark-300'; ?> rounded-lg">
                <i class="fas fa-users mr-3"></i>
                Mentors
            </a>
            <a href="courses.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 <?php echo $current_page === 'courses.php' || $current_page === 'course_details.php' ? 'bg-orange-100 dark:bg-orange-900' : 'hover:bg-gray-100 dark:hover:bg-dark-300'; ?> rounded-lg">
                <i class="fas fa-book mr-3"></i>
                Courses
            </a>
            <a href="my_courses.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 <?php echo $current_page === 'my_courses.php' ? 'bg-orange-100 dark:bg-orange-900' : 'hover:bg-gray-100 dark:hover:bg-dark-300'; ?> rounded-lg">
                <i class="fas fa-graduation-cap mr-3"></i>
                My Courses
            </a>
            <a href="profile_edit.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 <?php echo $current_page === 'profile_edit.php' ? 'bg-orange-100 dark:bg-orange-900' : 'hover:bg-gray-100 dark:hover:bg-dark-300'; ?> rounded-lg">
                <i class="fas fa-user-edit mr-3"></i>
                Edit Profile
            </a>
            <a href="contact_admin.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-300 <?php echo $current_page === 'contact_admin.php' ? 'bg-orange-100 dark:bg-orange-900' : 'hover:bg-gray-100 dark:hover:bg-dark-300'; ?> rounded-lg">
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