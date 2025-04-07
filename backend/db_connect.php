<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mentorship_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create courses table if it doesn't exist
$createCoursesTable = "CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    mentor_id INT NOT NULL,
    duration VARCHAR(50),
    level VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mentor_id) REFERENCES users(id)
)";

// Create course_enrollments table if it doesn't exist
$createEnrollmentsTable = "CREATE TABLE IF NOT EXISTS course_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('enrolled', 'completed') DEFAULT 'enrolled',
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_enrollment (course_id, user_id)
)";

$conn->query($createCoursesTable);
$conn->query($createEnrollmentsTable);

// Insert dummy courses if none exist
$checkCourses = "SELECT COUNT(*) as count FROM courses";
$result = $conn->query($checkCourses);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Get some mentor IDs
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
            "mentor_id" => $mentorIds[0] ?? 1,
            "duration" => "8 weeks",
            "level" => "Beginner"
        ],
        [
            "title" => "Data Science Fundamentals",
            "description" => "Master the basics of data analysis, visualization, and machine learning.",
            "mentor_id" => $mentorIds[1] ?? 1,
            "duration" => "12 weeks",
            "level" => "Intermediate"
        ],
        [
            "title" => "Digital Marketing Strategy",
            "description" => "Learn how to create and implement effective digital marketing campaigns.",
            "mentor_id" => $mentorIds[2] ?? 1,
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
}
?> 