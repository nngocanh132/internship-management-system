-- ============================================
-- INTERNSHIP MANAGEMENT SYSTEM v2
-- 12 Tables - Clean Schema
-- ============================================
DROP DATABASE IF EXISTS internship_system;
CREATE DATABASE internship_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE internship_system;

-- 1. USERS
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','company','lecturer','admin') NOT NULL,
    is_profile_completed TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. STUDENT_PROFILES
CREATE TABLE student_profiles (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    student_code VARCHAR(30) UNIQUE,
    phone VARCHAR(20),
    gpa DECIMAL(3,2),
    major VARCHAR(100),
    avatar VARCHAR(255),
    about_me TEXT,
    linkedin_url VARCHAR(255),
    CONSTRAINT fk_sp_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 3. COMPANY_PROFILES
CREATE TABLE company_profiles (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    company_name VARCHAR(150) NOT NULL,
    tax_code VARCHAR(50),
    address TEXT,
    phone VARCHAR(20),
    website VARCHAR(255),
    logo VARCHAR(255),
    business_license_file VARCHAR(255),
    description TEXT,
    industry VARCHAR(100),
    company_size VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cp_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 4. LECTURER_PROFILES
CREATE TABLE lecturer_profiles (
    lecturer_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    department VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(150),
    CONSTRAINT fk_lp_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 5. INTERNSHIPS (Job posts by company)
CREATE TABLE internships (
    internship_id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    requirements TEXT,
    quantity INT DEFAULT 1,
    location VARCHAR(200),
    start_date DATE,
    end_date DATE,
    status ENUM('open','closed') DEFAULT 'open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_int_company FOREIGN KEY (company_id) REFERENCES company_profiles(company_id) ON DELETE CASCADE
);

-- 6. APPLICATIONS
CREATE TABLE applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    internship_id INT NOT NULL,
    cv_file VARCHAR(255),
    status ENUM(
        'pending_admin',
        'approved_admin',
        'rejected_admin',
        'approved_company',
        'rejected_company',
        'interview_passed',
        'interview_failed',
        'internship_active',
        'internship_completed'
    ) DEFAULT 'pending_admin',
    admin_note TEXT,
    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_app (student_id, internship_id),
    CONSTRAINT fk_app_student FOREIGN KEY (student_id) REFERENCES student_profiles(student_id) ON DELETE CASCADE,
    CONSTRAINT fk_app_internship FOREIGN KEY (internship_id) REFERENCES internships(internship_id) ON DELETE CASCADE
);

-- 7. CONVERSATIONS
CREATE TABLE conversations (
    conversation_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    company_id INT NOT NULL,
    application_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_conv (student_id, company_id, application_id),
    CONSTRAINT fk_conv_student FOREIGN KEY (student_id) REFERENCES student_profiles(student_id) ON DELETE CASCADE,
    CONSTRAINT fk_conv_company FOREIGN KEY (company_id) REFERENCES company_profiles(company_id) ON DELETE CASCADE,
    CONSTRAINT fk_conv_app FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE SET NULL
);

-- 8. MESSAGES
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    message_content TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_msg_conv FOREIGN KEY (conversation_id) REFERENCES conversations(conversation_id) ON DELETE CASCADE,
    CONSTRAINT fk_msg_sender FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 9. INTERVIEWS
CREATE TABLE interviews (
    interview_id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL UNIQUE,
    interview_date DATETIME,
    meeting_link VARCHAR(255),
    address VARCHAR(255),
    result ENUM('pending','passed','failed') DEFAULT 'pending',
    note TEXT,
    CONSTRAINT fk_iv_app FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE
);

-- 10. INTERNSHIP_REGISTRATIONS
CREATE TABLE internship_registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    company_id INT NOT NULL,
    internship_id INT NOT NULL,
    lecturer_id INT,
    start_date DATE,
    end_date DATE,
    status ENUM('active','completed') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reg_student FOREIGN KEY (student_id) REFERENCES student_profiles(student_id),
    CONSTRAINT fk_reg_company FOREIGN KEY (company_id) REFERENCES company_profiles(company_id),
    CONSTRAINT fk_reg_internship FOREIGN KEY (internship_id) REFERENCES internships(internship_id),
    CONSTRAINT fk_reg_lecturer FOREIGN KEY (lecturer_id) REFERENCES lecturer_profiles(lecturer_id)
);

-- 11. EVALUATIONS
CREATE TABLE evaluations (
    evaluation_id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL UNIQUE,
    technical_skill TINYINT,
    teamwork TINYINT,
    communication TINYINT,
    attitude TINYINT,
    overall_score DECIMAL(4,2),
    comment TEXT,
    evaluated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_eval_reg FOREIGN KEY (registration_id) REFERENCES internship_registrations(registration_id)
);

-- 12. INTERNSHIP_REPORTS
CREATE TABLE internship_reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL UNIQUE,
    report_file VARCHAR(255),
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    lecturer_comment TEXT,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME,
    CONSTRAINT fk_report_reg FOREIGN KEY (registration_id) REFERENCES internship_registrations(registration_id)
);

-- ============================================
-- SEED DATA
-- ============================================

-- Admin account (password: admin123)
INSERT INTO users (email, password, role, is_profile_completed) VALUES
('admin@ischool.edu.vn', MD5('admin123'), 'admin', 1);

-- Demo lecturer (password: pass123) - Admin creates lecturer
INSERT INTO users (email, password, role, is_profile_completed) VALUES
('lecturer1@ischool.edu.vn', MD5('pass123'), 'lecturer', 1);

INSERT INTO lecturer_profiles (user_id, full_name, department, phone, email) VALUES
(2, 'TS. Nguyễn Văn Minh', 'Khoa CNTT', '0901000005', 'lecturer1@ischool.edu.vn');

-- Demo student (password: pass123)
INSERT INTO users (email, password, role, is_profile_completed) VALUES
('student1@ischool.edu.vn', MD5('pass123'), 'student', 1);

INSERT INTO student_profiles (user_id, full_name, student_code, phone, gpa, major) VALUES
(3, 'Nguyễn Văn An', 'SV2024001', '0901000002', 3.50, 'Công nghệ thông tin');

-- Demo company (password: pass123)
INSERT INTO users (email, password, role, is_profile_completed) VALUES
('hr@fpt.com', MD5('pass123'), 'company', 1);

INSERT INTO company_profiles (user_id, company_name, tax_code, address, phone, website, industry) VALUES
(4, 'FPT Software', '0101248141', '17 Duy Tân, Cầu Giấy, Hà Nội', '024 7300 7300', 'https://fpt-software.com', 'Công nghệ thông tin');

-- Demo internship
INSERT INTO internships (company_id, title, description, requirements, quantity, location, status) VALUES
(1, 'Backend Developer Intern', 'Phát triển API và backend với PHP/Laravel', 'GPA >= 3.0, biết PHP/MySQL', 3, 'Hà Nội', 'open'),
(1, 'Frontend Developer Intern', 'Xây dựng giao diện với ReactJS', 'GPA >= 2.8, biết HTML/CSS/JS', 2, 'Hà Nội', 'open');
