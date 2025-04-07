-- Create the database
CREATE DATABASE IF NOT EXISTS mentorconnect;
USE mentorconnect;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('mentor', 'mentee') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create profiles table
CREATE TABLE IF NOT EXISTS profiles (
    user_id INT PRIMARY KEY,
    full_name VARCHAR(255),
    headline VARCHAR(255),
    bio TEXT,
    industry VARCHAR(255),
    skills_or_needs TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample mentor
INSERT INTO users (email, password, user_type) VALUES 
('mentor@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentor');

INSERT INTO profiles (user_id, full_name, headline, bio, industry, skills_or_needs) VALUES 
(LAST_INSERT_ID(), 'John Doe', 'Tech Startup Advisor', 'Experienced tech entrepreneur with 10+ years in the industry.', 'Technology', 'Startup Strategy, Product Development, Fundraising');

-- Insert sample mentee
INSERT INTO users (email, password, user_type) VALUES 
('mentee@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mentee');

INSERT INTO profiles (user_id, full_name, headline, bio, industry, skills_or_needs) VALUES 
(LAST_INSERT_ID(), 'Jane Smith', 'Aspiring Entrepreneur', 'Looking to start my own tech company.', 'Technology', 'Business Planning, Marketing, Product Development'); 