-- =========================================
-- DROP & CREATE DATABASE
-- =========================================
DROP DATABASE IF EXISTS internship_system;

CREATE DATABASE internship_system;

USE internship_system;

-- =========================================
-- 1. DEPARTMENTS
-- =========================================
CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL,
    faculty VARCHAR(100)
);

-- =========================================
-- 2. USERS
-- =========================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,

    department_id INT,

    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,

    role ENUM(
        'student',
        'lecturer',
        'company',
        'admin'
    ) NOT NULL,

    major VARCHAR(100),
    student_code VARCHAR(20) UNIQUE,
    phone VARCHAR(20),

    status ENUM(
        'active',
        'inactive'
    ) DEFAULT 'active',

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_users_department
        FOREIGN KEY (department_id)
        REFERENCES departments(department_id)
);

-- =========================================
-- 3. COMPANIES
-- =========================================
CREATE TABLE companies (
    company_id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,
    location VARCHAR(150),
    description TEXT,

    contact_email VARCHAR(100),
    phone VARCHAR(20),

    status ENUM(
        'active',
        'inactive'
    ) DEFAULT 'active'
);

-- =========================================
-- 4. SKILLS
-- =========================================
CREATE TABLE skills (
    skill_id INT AUTO_INCREMENT PRIMARY KEY,

    skill_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

-- =========================================
-- 5. INTERNSHIP_POSITIONS
-- =========================================
CREATE TABLE internship_positions (
    position_id INT AUTO_INCREMENT PRIMARY KEY,

    company_id INT NOT NULL,

    title VARCHAR(150) NOT NULL,
    description TEXT,

    required_major VARCHAR(100),

    quota INT NOT NULL,

    status ENUM(
        'open',
        'closed'
    ) DEFAULT 'open',

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_positions_company
        FOREIGN KEY (company_id)
        REFERENCES companies(company_id)
);

-- =========================================
-- 6. POSITION_SKILLS
-- =========================================
CREATE TABLE position_skills (
    position_skill_id INT AUTO_INCREMENT PRIMARY KEY,

    position_id INT NOT NULL,
    skill_id INT NOT NULL,

    UNIQUE(position_id, skill_id),

    CONSTRAINT fk_positionskills_position
        FOREIGN KEY (position_id)
        REFERENCES internship_positions(position_id),

    CONSTRAINT fk_positionskills_skill
        FOREIGN KEY (skill_id)
        REFERENCES skills(skill_id)
);
-- =========================================
-- 7. INTERNSHIP_REGISTRATIONS
-- =========================================
CREATE TABLE internship_registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,

    student_id INT NOT NULL,
    position_id INT NOT NULL,

    status ENUM(
        'pending',
        'approved',
        'rejected',
        'cancelled'
    ) DEFAULT 'pending',

    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(student_id, position_id),

    CONSTRAINT fk_registrations_student
        FOREIGN KEY (student_id)
        REFERENCES users(user_id),

    CONSTRAINT fk_registrations_position
        FOREIGN KEY (position_id)
        REFERENCES internship_positions(position_id)
);

-- =========================================
-- 8. INTERNSHIP_ASSIGNMENTS
-- =========================================
CREATE TABLE internship_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,

    registration_id INT NOT NULL,
    lecturer_id INT NOT NULL,

    start_date DATE,
    end_date DATE,

    assignment_status ENUM(
        'active',
        'completed'
    ) DEFAULT 'active',

    notes TEXT,

    CONSTRAINT fk_assignments_registration
        FOREIGN KEY (registration_id)
        REFERENCES internship_registrations(registration_id),

    CONSTRAINT fk_assignments_lecturer
        FOREIGN KEY (lecturer_id)
        REFERENCES users(user_id)
);

-- =========================================
-- 9. WEEKLY_JOURNALS
-- =========================================
CREATE TABLE weekly_journals (
    journal_id INT AUTO_INCREMENT PRIMARY KEY,

    assignment_id INT NOT NULL,

    week_number INT NOT NULL,
    content TEXT NOT NULL,

    lecturer_comment TEXT,

    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    status ENUM(
        'submitted',
        'late',
        'reviewed'
    ) DEFAULT 'submitted',

    UNIQUE(assignment_id, week_number),

    CONSTRAINT fk_journals_assignment
        FOREIGN KEY (assignment_id)
        REFERENCES internship_assignments(assignment_id)
);

-- =========================================
-- 10. COMPANY_EVALUATIONS
-- =========================================
CREATE TABLE company_evaluations (
    evaluation_id INT AUTO_INCREMENT PRIMARY KEY,

    assignment_id INT NOT NULL,

    evaluator_name VARCHAR(100),

    score DECIMAL(5,2) NOT NULL,
    feedback TEXT,

    evaluated_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_companyevaluations_assignment
        FOREIGN KEY (assignment_id)
        REFERENCES internship_assignments(assignment_id)
);

-- =========================================
-- 11. LECTURER_EVALUATIONS
-- =========================================
CREATE TABLE lecturer_evaluations (
    evaluation_id INT AUTO_INCREMENT PRIMARY KEY,

    assignment_id INT NOT NULL,
    lecturer_id INT NOT NULL,

    score DECIMAL(5,2) NOT NULL,
    feedback TEXT,

    evaluated_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_lecturerevaluations_assignment
    FOREIGN KEY (assignment_id)
        REFERENCES internship_assignments(assignment_id),

    CONSTRAINT fk_lecturerevaluations_lecturer
        FOREIGN KEY (lecturer_id)
        REFERENCES users(user_id)
);

-- =========================================
-- 12. FINAL_GRADES
-- =========================================
CREATE TABLE final_grades (
    final_id INT AUTO_INCREMENT PRIMARY KEY,

    assignment_id INT NOT NULL UNIQUE,

    company_score DECIMAL(5,2),
    lecturer_score DECIMAL(5,2),
    final_score DECIMAL(5,2),

    result_status ENUM(
        'pass',
        'fail'
    ),

    calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_finalgrades_assignment
        FOREIGN KEY (assignment_id)
        REFERENCES internship_assignments(assignment_id)
);