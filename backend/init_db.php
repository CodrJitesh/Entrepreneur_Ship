<?php
$servername = "localhost";
$username = "root";
$password = "";

// Create connection without database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS mentorship_db";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select the database
$conn->select_db("mentorship_db");

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('mentor', 'mentee') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create profiles table
$sql = "CREATE TABLE IF NOT EXISTS profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    headline VARCHAR(255),
    bio TEXT,
    industry VARCHAR(255),
    skills TEXT,
    needs TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Profiles table created successfully<br>";
} else {
    echo "Error creating profiles table: " . $conn->error . "<br>";
}

// Create courses table
$sql = "CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    mentor_id INT NOT NULL,
    duration VARCHAR(50),
    level VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentor_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Courses table created successfully<br>";
} else {
    echo "Error creating courses table: " . $conn->error . "<br>";
}

// Create course_enrollments table
$sql = "CREATE TABLE IF NOT EXISTS course_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('enrolled', 'completed') DEFAULT 'enrolled',
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_enrollment (course_id, user_id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Course enrollments table created successfully<br>";
} else {
    echo "Error creating course enrollments table: " . $conn->error . "<br>";
}

// First, clear existing data to start fresh
$conn->query("DELETE FROM course_enrollments");
$conn->query("DELETE FROM courses");
$conn->query("DELETE FROM profiles");
$conn->query("DELETE FROM users");
echo "Existing data cleared successfully<br>";

// Create sample mentors
$sampleMentors = [
    [
        "email" => "john.doe@example.com",
        "password" => password_hash("password123", PASSWORD_DEFAULT),
        "user_type" => "mentor",
        "full_name" => "John Doe",
        "headline" => "Senior Web Developer",
        "bio" => "Experienced web developer with 10+ years of experience in building modern web applications.",
        "industry" => "Technology",
        "skills" => "HTML, CSS, JavaScript, React, Node.js"
    ],
    [
        "email" => "jane.smith@example.com",
        "password" => password_hash("password123", PASSWORD_DEFAULT),
        "user_type" => "mentor",
        "full_name" => "Jane Smith",
        "headline" => "Data Science Expert",
        "bio" => "Data scientist specializing in machine learning and data analysis.",
        "industry" => "Data Science",
        "skills" => "Python, Machine Learning, Data Analysis, Statistics"
    ],
    [
        "email" => "mike.johnson@example.com",
        "password" => password_hash("password123", PASSWORD_DEFAULT),
        "user_type" => "mentor",
        "full_name" => "Mike Johnson",
        "headline" => "Digital Marketing Specialist",
        "bio" => "Digital marketing expert with a focus on social media and content strategy.",
        "industry" => "Marketing",
        "skills" => "Social Media Marketing, Content Strategy, SEO, Analytics"
    ]
];

// Insert mentors
$stmt = $conn->prepare("INSERT INTO users (email, password, user_type) VALUES (?, ?, ?)");
$profileStmt = $conn->prepare("INSERT INTO profiles (user_id, full_name, headline, bio, industry, skills) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($sampleMentors as $mentor) {
    // Insert user
    $stmt->bind_param("sss", $mentor['email'], $mentor['password'], $mentor['user_type']);
    $stmt->execute();
    $userId = $conn->insert_id;

    // Insert profile
    $profileStmt->bind_param("isssss", 
        $userId,
        $mentor['full_name'],
        $mentor['headline'],
        $mentor['bio'],
        $mentor['industry'],
        $mentor['skills']
    );
    $profileStmt->execute();
}

$stmt->close();
$profileStmt->close();
echo "Sample mentors created successfully<br>";

// Get mentor IDs
$getMentors = "SELECT id FROM users WHERE user_type = 'mentor' LIMIT 3";
$mentors = $conn->query($getMentors);
$mentorIds = [];
while ($mentor = $mentors->fetch_assoc()) {
    $mentorIds[] = $mentor['id'];
}

// Insert dummy courses
$dummyCourses = [
    [
        "title" => "Introduction to Web Development",
        "description" => "Learn the fundamentals of HTML, CSS, and JavaScript to build modern websites.",
        "mentor_id" => $mentorIds[0],
        "duration" => "8 weeks",
        "level" => "Beginner"
    ],
    [
        "title" => "Data Science Fundamentals",
        "description" => "Master the basics of data analysis, visualization, and machine learning.",
        "mentor_id" => $mentorIds[1],
        "duration" => "12 weeks",
        "level" => "Intermediate"
    ],
    [
        "title" => "Digital Marketing Strategy",
        "description" => "Learn how to create and implement effective digital marketing campaigns.",
        "mentor_id" => $mentorIds[2],
        "duration" => "6 weeks",
        "level" => "Beginner"
    ]
];

$stmt = $conn->prepare("INSERT INTO courses (title, description, mentor_id, duration, level) VALUES (?, ?, ?, ?, ?)");

foreach ($dummyCourses as $course) {
    $stmt->bind_param("ssiss", 
        $course['title'],
        $course['description'],
        $course['mentor_id'],
        $course['duration'],
        $course['level']
    );
    $stmt->execute();
}
$stmt->close();
echo "Dummy courses inserted successfully<br>";

$conn->close();
?> 