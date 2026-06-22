<?php
// ── AUTH ──────────────────────────────────────────────────────────────
$r->add('ANY', '/auth/login.php',          'AuthController',         'login');
$r->add('ANY', '/auth/register.php',       'AuthController',         'register');
$r->add('ANY', '/auth/logout.php',         'AuthController',         'logout');

// ── DASHBOARD ─────────────────────────────────────────────────────────
$r->add('ANY', '/dashboard/admin.php',     'DashboardController',    'admin');
$r->add('ANY', '/dashboard/student.php',   'DashboardController',    'student');
$r->add('ANY', '/dashboard/company.php',   'DashboardController',    'company');
$r->add('ANY', '/dashboard/lecturer.php',  'DashboardController',    'lecturer');

// ── PROFILES ──────────────────────────────────────────────────────────
$r->add('ANY', '/student_profiles/edit.php',   'ProfileController',  'editStudent');
$r->add('ANY', '/student_profiles/list.php',   'ProfileController',  'listStudents');
$r->add('ANY', '/student_profiles/index.php',  'ProfileController',  'indexStudent');
$r->add('ANY', '/company_profiles/edit.php',   'ProfileController',  'editCompany');
$r->add('ANY', '/company_profiles/list.php',   'ProfileController',  'listCompanies');
$r->add('ANY', '/lecturer_profiles/list.php',  'ProfileController',  'listLecturers');

// ── INTERNSHIPS ───────────────────────────────────────────────────────
$r->add('ANY', '/internships/list.php',    'InternshipController',   'list');
$r->add('ANY', '/internships/browse.php',  'InternshipController',   'browse');
$r->add('ANY', '/internships/my_jobs.php', 'InternshipController',   'myJobs');
$r->add('ANY', '/internships/create.php',  'InternshipController',   'create');
$r->add('ANY', '/internships/edit.php',    'InternshipController',   'edit');

// ── APPLICATIONS ──────────────────────────────────────────────────────
$r->add('ANY', '/applications/list.php',              'ApplicationController', 'list');
$r->add('ANY', '/applications/review.php',            'ApplicationController', 'review');
$r->add('ANY', '/applications/my_applications.php',   'ApplicationController', 'myApplications');
$r->add('ANY', '/applications/apply.php',             'ApplicationController', 'apply');
$r->add('ANY', '/applications/company_review.php',    'ApplicationController', 'companyReview');
$r->add('ANY', '/applications/company_candidates.php','ApplicationController', 'companyCandidates');

// ── MESSAGES ──────────────────────────────────────────────────────────
$r->add('ANY', '/messages/inbox.php',               'MessageController',  'inbox');
$r->add('ANY', '/messages/chat.php',                'MessageController',  'chat');
$r->add('ANY', '/messages/lecturer_chat.php',       'MessageController',  'lecturerChat');
$r->add('ANY', '/messages/set_interview.php',       'InterviewController','setInterview');
$r->add('ANY', '/messages/set_interview_result.php','InterviewController','setResult');

// ── REGISTRATIONS ─────────────────────────────────────────────────────
$r->add('ANY', '/registrations/list.php',            'RegistrationController', 'list');
$r->add('ANY', '/registrations/assign.php',          'RegistrationController', 'assign');
$r->add('ANY', '/registrations/my_internship.php',   'RegistrationController', 'myInternship');
$r->add('ANY', '/registrations/my_students.php',     'RegistrationController', 'myStudents');
$r->add('ANY', '/registrations/company_view.php',    'RegistrationController', 'companyView');
$r->add('ANY', '/registrations/create_from_app.php', 'RegistrationController', 'createFromApp');
$r->add('ANY', '/registrations/edit.php',            'RegistrationController', 'edit');

// ── REPORTS ───────────────────────────────────────────────────────────
$r->add('ANY', '/reports/list.php',    'ReportController', 'list');
$r->add('ANY', '/reports/review.php',  'ReportController', 'reviewByLecturer');
$r->add('ANY', '/reports/submit.php',  'ReportController', 'submit');

// ── EVALUATIONS ───────────────────────────────────────────────────────
$r->add('ANY', '/evaluations/list.php', 'EvaluationController', 'list');
$r->add('ANY', '/evaluations/add.php',  'EvaluationController', 'add');

// ── INTERVIEWS ────────────────────────────────────────────────────────
$r->add('ANY', '/interviews/list.php',  'InterviewController', 'list');

// ── USERS ─────────────────────────────────────────────────────────────
$r->add('ANY', '/users/list.php',             'UserController', 'list');
$r->add('ANY', '/users/create_lecturer.php',  'UserController', 'createLecturer');

// ── CONVERSATIONS ─────────────────────────────────────────────────────
$r->add('ANY', '/conversations/list.php', 'MessageController', 'inbox');

// ── ROOT ──────────────────────────────────────────────────────────────
$r->add('ANY', '/',          'DashboardController', 'index');
$r->add('ANY', '/index.php', 'DashboardController', 'index');
