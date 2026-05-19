-- =========================================
-- DATABASE: INTERNSHIP MANAGEMENT SYSTEM
-- INS3064 - In-Class Backend Task
-- =========================================

DROP DATABASE IF EXISTS internship_system;
CREATE DATABASE internship_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE internship_system;

-- =========================================
-- MEMBER 1: users, companies, internship_positions
-- =========================================

CREATE TABLE departments (
    department_id   INT PRIMARY KEY AUTO_INCREMENT,
    department_name VARCHAR(100) NOT NULL,
    description     TEXT
);

CREATE TABLE users (
    user_id       INT PRIMARY KEY AUTO_INCREMENT,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('student','lecturer','company_rep','admin') NOT NULL,
    department_id INT,
    phone         VARCHAR(20),
    student_code  VARCHAR(20) UNIQUE,
    status        ENUM('active','inactive') DEFAULT 'active',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(department_id)
);

CREATE TABLE companies (
    company_id    INT PRIMARY KEY AUTO_INCREMENT,
    company_name  VARCHAR(150) NOT NULL,
    industry      VARCHAR(100),
    address       TEXT,
    description   TEXT,
    contact_email VARCHAR(100) UNIQUE,
    phone         VARCHAR(20),
    status        ENUM('active','inactive') DEFAULT 'active',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE internship_positions (
    position_id   INT PRIMARY KEY AUTO_INCREMENT,
    company_id    INT NOT NULL,
    title         VARCHAR(150) NOT NULL,
    description   TEXT,
    requirements  TEXT,
    industry      VARCHAR(100),
    quota         INT NOT NULL DEFAULT 1,
    filled        INT NOT NULL DEFAULT 0,
    start_date    DATE NOT NULL,
    end_date      DATE NOT NULL,
    status        ENUM('open','closed','full') DEFAULT 'open',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(company_id)
);

-- =========================================
-- MEMBER 2: internship_registrations, internship_assignments, weekly_journals
-- =========================================

CREATE TABLE internship_registrations (
    registration_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id      INT NOT NULL,
    position_id     INT NOT NULL,
    status          ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
    note            TEXT,
    registered_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id)  REFERENCES users(user_id),
    FOREIGN KEY (position_id) REFERENCES internship_positions(position_id),
    UNIQUE KEY uq_student_position (student_id, position_id)
);

CREATE TABLE internship_assignments (
    assignment_id   INT PRIMARY KEY AUTO_INCREMENT,
    registration_id INT NOT NULL UNIQUE,
    lecturer_id     INT NOT NULL,
    assigned_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    note            TEXT,
    FOREIGN KEY (registration_id) REFERENCES internship_registrations(registration_id),
    FOREIGN KEY (lecturer_id)     REFERENCES users(user_id)
);

CREATE TABLE weekly_journals (
    journal_id      INT PRIMARY KEY AUTO_INCREMENT,
    registration_id INT NOT NULL,
    week_number     INT NOT NULL,
    content         TEXT NOT NULL,
    tasks_done      TEXT,
    issues          TEXT,
    submitted_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registration_id) REFERENCES internship_registrations(registration_id),
    UNIQUE KEY uq_journal_week (registration_id, week_number)
);

-- =========================================
-- MEMBER 3: company_evaluations, lecturer_evaluations, final_grades
-- =========================================

CREATE TABLE company_evaluations (
    eval_id         INT PRIMARY KEY AUTO_INCREMENT,
    registration_id INT NOT NULL UNIQUE,
    attitude_score  DECIMAL(4,2),
    skill_score     DECIMAL(4,2),
    result_score    DECIMAL(4,2),
    total_score     DECIMAL(4,2),
    comment         TEXT,
    evaluated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registration_id) REFERENCES internship_registrations(registration_id)
);

CREATE TABLE lecturer_evaluations (
    eval_id         INT PRIMARY KEY AUTO_INCREMENT,
    registration_id INT NOT NULL UNIQUE,
    report_score    DECIMAL(4,2),
    journal_score   DECIMAL(4,2),
    presentation_score DECIMAL(4,2),
    total_score     DECIMAL(4,2),
    comment         TEXT,
    evaluated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registration_id) REFERENCES internship_registrations(registration_id)
);

CREATE TABLE final_grades (
    grade_id           INT PRIMARY KEY AUTO_INCREMENT,
    registration_id    INT NOT NULL UNIQUE,
    company_score      DECIMAL(4,2),
    lecturer_score     DECIMAL(4,2),
    company_weight     DECIMAL(3,2) DEFAULT 0.60,
    lecturer_weight    DECIMAL(3,2) DEFAULT 0.40,
    final_score        DECIMAL(4,2),
    letter_grade       VARCHAR(5),
    status             ENUM('pending','finalized') DEFAULT 'pending',
    calculated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registration_id) REFERENCES internship_registrations(registration_id)
);

-- =========================================
-- SAMPLE DATA
-- =========================================

INSERT INTO departments (department_name, description) VALUES
('Information Technology', 'IT Department'),
('Business Administration', 'Business Department'),
('Engineering', 'Engineering Department');

INSERT INTO companies (company_name, industry, address, description, contact_email, phone) VALUES
('FPT Software',    'Software Development', 'Ha Noi',          'Leading software outsourcing company',  'contact@fpt.com',  '024-1234567'),
('VNG Corporation', 'Technology',           'Ho Chi Minh City', 'Internet & technology company',         'hr@vng.com',       '028-9876543'),
('Viettel Group',   'Telecommunications',   'Ha Noi',          'State-owned telecom corporation',       'intern@viettel.vn','024-6660000');

INSERT INTO internship_positions (company_id, title, description, requirements, industry, quota, start_date, end_date) VALUES
(1, 'Backend Developer Intern',  'Work on Java Spring Boot projects',    'Java, SQL, OOP',          'Software Development', 3, '2025-06-01', '2025-08-31'),
(1, 'Frontend Developer Intern', 'Build React web applications',         'ReactJS, HTML, CSS',      'Software Development', 2, '2025-06-01', '2025-08-31'),
(2, 'Data Analyst Intern',       'Analyze user behavior data',           'Python, Excel, SQL',      'Technology',           2, '2025-07-01', '2025-09-30'),
(3, 'Network Engineer Intern',   'Support network infrastructure tasks', 'Networking, Linux basics', 'Telecommunications',   1, '2025-06-15', '2025-09-15');

INSERT INTO users (full_name, email, password, role, department_id, phone, student_code) VALUES
('Admin System',    'admin@ischool.edu.vn',    MD5('admin123'),    'admin',       1, '0100000000', NULL),
('Nguyen Van A',    'student1@ischool.edu.vn', MD5('pass123'),     'student',     1, '0123456789', 'SV001'),
('Tran Thi B',      'student2@ischool.edu.vn', MD5('pass123'),     'student',     1, '0123456780', 'SV002'),
('Le Van C',        'student3@ischool.edu.vn', MD5('pass123'),     'student',     2, '0123456781', 'SV003'),
('Pham Thi D',      'lecturer1@ischool.edu.vn',MD5('pass123'),     'lecturer',    1, '0987654321', NULL),
('Hoang Van E',     'lecturer2@ischool.edu.vn',MD5('pass123'),     'lecturer',    2, '0987654322', NULL);
