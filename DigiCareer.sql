-- Database Setup for DigiCareer Niger

CREATE DATABASE IF NOT EXISTS digicareer;
USE digicareer;

-- Disable foreign key checks for clean teardown
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS documents;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS employers;
DROP TABLE IF EXISTS candidates;
SET FOREIGN_KEY_CHECKS = 1;

-- Candidates Table
CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    title VARCHAR(100) DEFAULT 'Job Seeker',
    bio TEXT,
    skills TEXT, -- JSON or comma-separated
    education_level VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Employers Table
CREATE TABLE employers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    website VARCHAR(100),
    description TEXT,
    location VARCHAR(100),
    industry VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Jobs Table
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(100),
    type ENUM('Full-time', 'Part-time', 'Internship', 'Freelance') DEFAULT 'Full-time',
    salary_range VARCHAR(50),
    status ENUM('active', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE
);

-- Applications Table
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    candidate_id INT NOT NULL,
    status ENUM('pending', 'reviewed', 'accepted', 'rejected') DEFAULT 'pending',
    cover_letter TEXT,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE
);

-- Documents Table (CVs, Diplomas)
-- Drop old table if exists
DROP TABLE IF EXISTS documents;

-- Create new table with BLOB storage
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidate_id INT NOT NULL,
    type ENUM('cv', 'diploma', 'certificate', 'cover_letter') NOT NULL,
    file_path VARCHAR(255) NOT NULL,  -- Original filename for reference
    original_name VARCHAR(255) NOT NULL,
    file_content LONGBLOB,  -- Stores actual file data
    file_size INT,
    mime_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE
);

-- Optional: Add index for faster lookups
CREATE INDEX idx_candidate_id ON documents(candidate_id);

-- Insert dummy data for testing
INSERT INTO employers (username, password, company_name, email, description) VALUES 
('techcorp', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'TechCorp Niger', 'hr@techcorp.ne', 'Leading tech company in Niamey.');
-- Password is 'password'

INSERT INTO candidates (username, password, full_name, email, title) VALUES 
('ali', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ali Mamane', 'ali@test.com', 'Junior Developer');
-- Password is 'password'

-- User Tokens Table (For Remember Me)


-- Password Resets Table
CREATE TABLE user_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    selector VARCHAR(255) NOT NULL,
    hashed_validator VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    user_type ENUM('candidate', 'employer') NOT NULL,
    expires DATETIME NOT NULL
);

-- Password Resets Table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    selector VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires BIGINT NOT NULL
);

-- Invitations Table
CREATE TABLE IF NOT EXISTS invitations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT NOT NULL,
    candidate_id INT NOT NULL,
    job_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
);


-- Change profile_picture from VARCHAR(255) to TEXT (can hold 65KB)
ALTER TABLE candidates MODIFY profile_picture TEXT DEFAULT NULL;

-- OR better: Store only filename and use documents table for BLOB
-- This is what the code does by default
