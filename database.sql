-- =====================================
-- 1. CREATE DATABASE
-- =====================================
CREATE DATABASE IF NOT EXISTS internship_systems;
USE internship_systems;

-- =====================================
-- 2. USERS
-- =====================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('student','lecturer','admin') NOT NULL,
    major VARCHAR(100)
);

-- =====================================
-- 3. COMPANIES
-- =====================================
CREATE TABLE companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    location VARCHAR(150),
    description TEXT
);

-- =====================================
-- 4. INTERNSHIP POSITIONS
-- =====================================
CREATE TABLE internship_positions (
    position_id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    required_major VARCHAR(100),
    quota INT DEFAULT 0,
    current_interns INT DEFAULT 0,

    FOREIGN KEY (company_id)
    REFERENCES companies(company_id)
    ON DELETE CASCADE
);

-- =====================================
-- 5. INTERNSHIP REGISTRATIONS
-- =====================================
CREATE TABLE internship_registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    position_id INT NOT NULL,
    status ENUM('Created','Pending','Approved','Rejected') DEFAULT 'Created',
    applied_date DATE,

    FOREIGN KEY (student_id)
    REFERENCES users(user_id)
    ON DELETE CASCADE,

    FOREIGN KEY (position_id)
    REFERENCES internship_positions(position_id)
    ON DELETE CASCADE
);

-- =====================================
-- 6. INTERNSHIP ASSIGNMENTS
-- =====================================
CREATE TABLE internship_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL UNIQUE,
    lecturer_id INT NOT NULL,
    start_date DATE,
    end_date DATE,

    FOREIGN KEY (registration_id)
    REFERENCES internship_registrations(registration_id)
    ON DELETE CASCADE,

    FOREIGN KEY (lecturer_id)
    REFERENCES users(user_id)
    ON DELETE CASCADE
);

-- =====================================
-- 7. WEEKLY JOURNALS
-- =====================================
CREATE TABLE weekly_journals (
    journal_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    week_number INT NOT NULL,
    content TEXT,
    submitted_at DATETIME,
    status ENUM('On-time','Late'),

    UNIQUE (assignment_id, week_number),

    FOREIGN KEY (assignment_id)
    REFERENCES internship_assignments(assignment_id)
    ON DELETE CASCADE
);

-- =====================================
-- 8. COMPANY EVALUATIONS
-- =====================================
CREATE TABLE company_evaluations (
    evaluation_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL UNIQUE,
    score DECIMAL(5,2),
    feedback TEXT,

    FOREIGN KEY (assignment_id)
    REFERENCES internship_assignments(assignment_id)
    ON DELETE CASCADE
);

-- =====================================
-- 9. LECTURER EVALUATIONS
-- =====================================
CREATE TABLE lecturer_evaluations (
    evaluation_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL UNIQUE,
    score DECIMAL(5,2),
    feedback TEXT,

    FOREIGN KEY (assignment_id)
    REFERENCES internship_assignments(assignment_id)
    ON DELETE CASCADE
);

-- =====================================
-- 10. FINAL GRADES
-- =====================================
CREATE TABLE final_grades (
    final_id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL UNIQUE,
    company_score DECIMAL(5,2),
    lecturer_score DECIMAL(5,2),
    final_score DECIMAL(5,2),

    FOREIGN KEY (assignment_id)
    REFERENCES internship_assignments(assignment_id)
    ON DELETE CASCADE
);