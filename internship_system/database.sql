-- =========================================
-- DATABASE: INTERNSHIP MANAGEMENT SYSTEM
-- MEMBER 1 - DATABASE STRUCTURE
-- =========================================

CREATE DATABASE internship_system;

USE internship_system;

-- =========================================
-- 1. DEPARTMENTS
-- =========================================

CREATE TABLE departments (

    department_id INT PRIMARY KEY AUTO_INCREMENT,

    department_name VARCHAR(100) NOT NULL,

    description TEXT
);

-- =========================================
-- 2. USERS
-- =========================================

CREATE TABLE users (

    user_id INT PRIMARY KEY AUTO_INCREMENT,

    full_name VARCHAR(100) NOT NULL,

    email VARCHAR(100) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    role ENUM(
        'student',
        'lecturer',
        'company'
    ) NOT NULL,

    department_id INT,

    phone VARCHAR(20),

    status ENUM(
        'active',
        'inactive'
    ) DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (department_id)
    REFERENCES departments(department_id)
);

-- =========================================
-- 3. COMPANIES
-- =========================================

CREATE TABLE companies (

    company_id INT PRIMARY KEY AUTO_INCREMENT,

    company_name VARCHAR(150) NOT NULL,

    address TEXT,

    description TEXT,

    contact_email VARCHAR(100) UNIQUE,

    status ENUM(
        'active',
        'inactive'
    ) DEFAULT 'active'
);

-- =========================================
-- 4. SKILLS
-- =========================================

CREATE TABLE skills (

    skill_id INT PRIMARY KEY AUTO_INCREMENT,

    skill_name VARCHAR(100) NOT NULL UNIQUE
);

-- =========================================
-- SAMPLE DATA
-- =========================================

INSERT INTO departments
(department_name, description)
VALUES
('Information Technology', 'IT Department'),
('Business Administration', 'Business Department');

INSERT INTO companies
(company_name, address, description, contact_email)
VALUES
(
    'FPT Software',
    'Ha Noi',
    'Software Development Company',
    'contact@fpt.com'
),
(
    'VNG Corporation',
    'Ho Chi Minh City',
    'Technology Company',
    'hr@vng.com'
);

INSERT INTO skills
(skill_name)
VALUES
('Java'),
('MySQL'),
('Web Development'),
('UI/UX Design');

INSERT INTO users
(
    full_name,
    email,
    password,
    role,
    department_id,
    phone
)
VALUES
(
    'Nguyen Van A',
    'student1@gmail.com',
    '123456',
    'student',
    1,
    '0123456789'
),
(
    'Tran Thi B',
    'lecturer1@gmail.com',
    '123456',
    'lecturer',
    1,
    '0987654321'
);