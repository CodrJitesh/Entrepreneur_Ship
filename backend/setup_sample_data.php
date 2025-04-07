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

// First, clear existing data to start fresh
$conn->query("DELETE FROM course_enrollments");
$conn->query("DELETE FROM courses");
$conn->query("DELETE FROM profiles");
$conn->query("DELETE FROM users");
echo "Existing data cleared successfully<br>";

// Create sample mentors
$sampleMentors = [
    [
        "email" => "rajesh.sharma@example.com",
        "password" => password_hash("password123", PASSWORD_DEFAULT),
        "user_type" => "mentor",
        "full_name" => "Rajesh Sharma",
        "headline" => "Serial Entrepreneur & Startup Advisor",
        "bio" => "Founder of multiple successful startups in the e-commerce and fintech space. Passionate about mentoring young entrepreneurs.",
        "industry" => "E-commerce & Fintech",
        "skills" => "Startup Strategy, Fundraising, E-commerce"
    ],
    [
        "email" => "priya.patel@example.com",
        "password" => password_hash("password123", PASSWORD_DEFAULT),
        "user_type" => "mentor",
        "full_name" => "Priya Patel",
        "headline" => "Social Entrepreneurship Expert",
        "bio" => "Award-winning social entrepreneur focused on sustainable business models and impact investing.",
        "industry" => "Social Enterprise",
        "skills" => "Social Entrepreneurship, Impact Investing, Sustainability"
    ],
    [
        "email" => "arun.kumar@example.com",
        "password" => password_hash("password123", PASSWORD_DEFAULT),
        "user_type" => "mentor",
        "full_name" => "Arun Kumar",
        "headline" => "Tech Startup Mentor",
        "bio" => "Former CTO turned startup mentor, specializing in helping tech entrepreneurs build scalable products.",
        "industry" => "Technology",
        "skills" => "Product Development, Tech Strategy, Scaling"
    ],
    [
        "email" => "meera.desai@example.com",
        "password" => password_hash("password123", PASSWORD_DEFAULT),
        "user_type" => "mentor",
        "full_name" => "Meera Desai",
        "headline" => "Business Strategy Consultant",
        "bio" => "Helping entrepreneurs develop winning business strategies and sustainable growth plans.",
        "industry" => "Business Strategy",
        "skills" => "Business Planning, Market Analysis, Growth Strategy"
    ]
];

// Insert mentors
$stmt = $conn->prepare("INSERT INTO users (email, password, user_type) VALUES (?, ?, ?)");
$profileStmt = $conn->prepare("INSERT INTO profiles (user_id, full_name, headline, bio, industry, skills_or_needs) VALUES (?, ?, ?, ?, ?, ?)");

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
$getMentors = "SELECT id FROM users WHERE user_type = 'mentor'";
$mentors = $conn->query($getMentors);
$mentorIds = [];
while ($mentor = $mentors->fetch_assoc()) {
    $mentorIds[] = $mentor['id'];
}

// Insert entrepreneurship courses
$dummyCourses = [
    [
        "title" => "Startup Fundamentals",
        "description" => "Learn the essential steps to launch your startup, from ideation to market validation and initial funding.",
        "mentor_id" => $mentorIds[0],
        "duration" => "8 weeks",
        "level" => "Beginner"
    ],
    [
        "title" => "Social Entrepreneurship",
        "description" => "Discover how to build a business that creates both financial and social impact.",
        "mentor_id" => $mentorIds[1],
        "duration" => "10 weeks",
        "level" => "Intermediate"
    ],
    [
        "title" => "Tech Startup Development",
        "description" => "Master the process of building and scaling a technology-based startup.",
        "mentor_id" => $mentorIds[2],
        "duration" => "12 weeks",
        "level" => "Intermediate"
    ],
    [
        "title" => "Business Strategy & Growth",
        "description" => "Develop effective business strategies and learn how to scale your venture sustainably.",
        "mentor_id" => $mentorIds[3],
        "duration" => "8 weeks",
        "level" => "Advanced"
    ],
    [
        "title" => "Fundraising for Startups",
        "description" => "Learn how to prepare for and secure funding for your startup venture.",
        "mentor_id" => $mentorIds[0],
        "duration" => "6 weeks",
        "level" => "Intermediate"
    ],
    [
        "title" => "Sustainable Business Models",
        "description" => "Explore how to create business models that are both profitable and environmentally sustainable.",
        "mentor_id" => $mentorIds[1],
        "duration" => "8 weeks",
        "level" => "Intermediate"
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
echo "Sample courses created successfully<br>";

$conn->close();
echo "Setup completed successfully!<br>";
?> 