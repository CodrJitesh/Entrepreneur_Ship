<?php
session_start();
require_once 'backend/db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';

    // Validate inputs
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($user_type)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Start transaction
            $conn->begin_transaction();

            try {
                // Insert into users table
                $stmt = $conn->prepare("INSERT INTO users (email, password, user_type) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $email, $hashed_password, $user_type);
                $stmt->execute();
                $user_id = $conn->insert_id;

                // Insert into profiles table
                $stmt = $conn->prepare("INSERT INTO profiles (user_id, full_name) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $full_name);
                $stmt->execute();

                $conn->commit();
                $success = 'Registration successful! You can now login.';
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Registration failed. Please try again.';
            }
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
    <title>Register - MentorConnect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow-lg">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-orange-600">MentorConnect</h1>
            <h2 class="mt-2 text-xl text-gray-600">Create your account</h2>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST" action="register.php" onsubmit="return validateForm()">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="full_name" class="sr-only">Full Name</label>
                    <input id="full_name" name="full_name" type="text" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm" 
                           placeholder="Full Name">
                </div>
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm" 
                           placeholder="Email address">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm" 
                           placeholder="Password">
                </div>
                <div>
                    <label for="confirm_password" class="sr-only">Confirm Password</label>
                    <input id="confirm_password" name="confirm_password" type="password" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm" 
                           placeholder="Confirm Password">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">I want to be a:</label>
                <div class="flex space-x-4">
                    <div class="flex items-center">
                        <input id="mentor" name="user_type" type="radio" value="mentor" required
                               class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300">
                        <label for="mentor" class="ml-2 block text-sm text-gray-900">Mentor</label>
                    </div>
                    <div class="flex items-center">
                        <input id="mentee" name="user_type" type="radio" value="mentee"
                               class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300">
                        <label for="mentee" class="ml-2 block text-sm text-gray-900">Mentee</label>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    Register
                </button>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Already have an account? 
                    <a href="login.php" class="font-medium text-orange-600 hover:text-orange-500">
                        Sign in here
                    </a>
                </p>
            </div>
        </form>
    </div>

    <script>
        function validateForm() {
            const fullName = document.getElementById('full_name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const userType = document.querySelector('input[name="user_type"]:checked');

            if (!fullName || !email || !password || !confirmPassword || !userType) {
                alert('Please fill in all fields');
                return false;
            }

            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return false;
            }

            if (password.length < 8) {
                alert('Password must be at least 8 characters long');
                return false;
            }

            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address');
                return false;
            }

            return true;
        }
    </script>
</body>
</html> 