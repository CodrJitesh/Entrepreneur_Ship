<?php
require_once 'backend/db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Set current page for navbar highlighting
$current_page = 'contact_admin.php';

// Get user profile
$stmt = $conn->prepare("
    SELECT p.full_name, u.email
    FROM profiles p
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($subject) || empty($message)) {
        header('Location: dashboard.php?error=Please fill in all fields');
        exit();
    }

    // Prepare email content
    $to = "jiteshsingh4305@gmail.com"; // Your email address
    $email_subject = "Mentorship Platform Contact: " . $subject;
    $email_message = "From: " . $user['full_name'] . " (" . $user['email'] . ")\n\n";
    $email_message .= "Subject: " . $subject . "\n\n";
    $email_message .= "Message:\n" . $message;
    
    $from = "jiteshsingh4305@gmail.com"; // Your email address
    $headers = "From: $from\r\n";
    $headers .= "Reply-To: " . $user['email'] . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    if (mail($to, $email_subject, $email_message, $headers)) {
        header('Location: dashboard.php?success=Your message has been sent successfully');
    } else {
        header('Location: dashboard.php?error=Failed to send message. Please try again later.');
    }
    exit();
}

// If not POST request, redirect to dashboard
header('Location: dashboard.php');
exit();
?> 