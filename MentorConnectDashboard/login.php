<?php
session_start();
require_once 'backend/db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
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
    <title>Login - MentorConnect</title>
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
<body class="bg-gray-100 dark:bg-dark-100 dark:text-white min-h-screen flex items-center justify-center relative overflow-hidden">

    <!-- 🔽 Video Background -->
    <video autoplay muted loop class="absolute top-0 left-0 w-full h-full object-cover z-0">
        <source src="includes/backdrop.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <!-- 🔽 Optional Dark Overlay -->
    <div class="absolute top-0 left-0 w-full h-full bg-black bg-opacity-50 z-0"></div>

    <!-- 🔼 Login Container -->
    <div class="max-w-md w-full mx-4 z-10 relative">
        <div class="bg-white dark:bg-dark-200 rounded-lg shadow-md p-8 card-hover animate-scale-in">
            <div class="text-center mb-8 animate-fade-in">
                <h1 class="text-3xl font-bold text-orange-600">MentorConnect</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Welcome back</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded relative mb-4 animate-slide-in-right" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="space-y-4">
                    <div class="animate-fade-in delay-100">
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            placeholder="Enter your email"
                            class="mt-2 w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-300 text-gray-900 dark:text-white shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-400 focus:outline-none transition duration-150 ease-in-out px-4 py-2 input-focus"
                        />
                    </div>
                    <div class="animate-fade-in delay-200">
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="Enter your password"
                            class="mt-2 w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-dark-300 text-gray-900 dark:text-white shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-400 focus:outline-none transition duration-150 ease-in-out px-4 py-2 input-focus"
                        />
                    </div>
                </div>

                <div class="animate-fade-in delay-300">
                    <button type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 btn-hover">
                        Sign in
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center animate-fade-in delay-400">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Don't have an account? 
                    <a href="register.php" class="font-medium text-orange-600 hover:text-orange-500 nav-link">
                        Sign up
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- 🌗 Theme toggle -->
    <script>
        const html = document.documentElement;
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
    </script>
</body>
</html>
