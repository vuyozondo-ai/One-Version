-- =====================================================
-- UniTutor Database - University Peer Tutoring Management System
-- =====================================================
-- Database: unitutor_db
-- Purpose: Peer tutoring management system for universities
-- 
-- NORMALIZATION EXPLANATION:
-- 
-- UNF (Unnormalized Form): All data in one table with repeating groups
-- 
-- 1NF (First Normal Form): 
-- - Eliminated repeating groups
-- - Each cell contains atomic values
-- - Each record is unique
-- Example: Separated tutor_modules from tutors to avoid repeating module data
-- 
-- 2NF (Second Normal Form):
-- - Meets 1NF requirements
-- - All non-key attributes are fully dependent on the primary key
-- - Removed partial dependencies
-- Example: programs table depends only on program_id, not on department_id
-- 
-- 3NF (Third Normal Form):
-- - Meets 2NF requirements
-- - No transitive dependencies
-- - Non-key attributes depend only on the primary key
-- Example: student email depends only on student_id, not on program_id
-- =====================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS unitutor_db;
USE unitutor_db;

-- =====================================================
-- TABLE: departments
-- Represents academic departments in the university
-- =====================================================
CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    admin_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE SET NULL
);

-- =====================================================
-- TABLE: programs
-- Represents academic programs (degrees/courses)
-- One department has many programs
-- =====================================================
CREATE TABLE programs (
    program_id INT AUTO_INCREMENT PRIMARY KEY,
    program_name VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE CASCADE
);

-- =====================================================
-- TABLE: modules
-- Represents individual modules/subjects within programs
-- One program has many modules
-- =====================================================
CREATE TABLE modules (
    module_id INT AUTO_INCREMENT PRIMARY KEY,
    module_code VARCHAR(20) NOT NULL UNIQUE,
    module_name VARCHAR(100) NOT NULL,
    program_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(program_id) ON DELETE CASCADE
);

-- =====================================================
-- TABLE: admins
-- System administrators who manage the platform
-- =====================================================
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_number VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLE: students
-- Students who can book tutoring sessions
-- =====================================================
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    student_number VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    program_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(program_id) ON DELETE SET NULL
);

-- =====================================================
-- TABLE: tutors
-- Peer tutors who provide tutoring services
-- =====================================================
CREATE TABLE tutors (
    tutor_id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_number VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLE: tutor_modules
-- Junction table linking tutors to modules they teach
-- One tutor can teach many modules
-- One module can have many tutors
-- =====================================================
CREATE TABLE tutor_modules (
    tutor_module_id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    module_id INT NOT NULL,
    proficiency_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'intermediate',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES tutors(tutor_id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(module_id) ON DELETE CASCADE,
    UNIQUE KEY unique_tutor_module (tutor_id, module_id)
);

-- =====================================================
-- TABLE: available_slots
-- Time slots when tutors are available for sessions
-- =====================================================
CREATE TABLE available_slots (
    slot_id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    module_id INT NOT NULL,
    slot_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    availability_status ENUM('available', 'booked', 'cancelled') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES tutors(tutor_id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(module_id) ON DELETE CASCADE
);

-- =====================================================
-- TABLE: bookings
-- Booking records for tutoring sessions
-- =====================================================
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    tutor_id INT NOT NULL,
    module_id INT NOT NULL,
    slot_id INT,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_id) REFERENCES tutors(tutor_id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(module_id) ON DELETE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES available_slots(slot_id) ON DELETE SET NULL
);

-- =====================================================
-- TABLE: sessions
-- Actual tutoring sessions created from confirmed bookings
-- =====================================================
CREATE TABLE sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(100),
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
);

-- =====================================================
-- TABLE: feedback
-- Student feedback on tutoring sessions
-- =====================================================
CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    tutor_id INT NOT NULL,
    booking_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_id) REFERENCES tutors(tutor_id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE SET NULL
);

-- =====================================================
-- TABLE: payments
-- Payment records for tutoring services
-- =====================================================
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    admin_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_status ENUM('pending', 'completed', 'refunded') DEFAULT 'pending',
    reference VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE SET NULL
);

-- =====================================================
-- TABLE: applications
-- Tutor applications and other system applications
-- =====================================================
CREATE TABLE applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    application_date DATE NOT NULL,
    application_type ENUM('tutor_application', 'other') DEFAULT 'tutor_application',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE SET NULL
);

-- =====================================================
-- SAMPLE DATA INSERTION
-- =====================================================

-- Insert Departments
INSERT INTO departments (department_name, description) VALUES
('Computer Science', 'Department of Computer Science and Information Technology'),
('Mathematics', 'Department of Mathematics and Statistics'),
('Physics', 'Department of Physics'),
('Chemistry', 'Department of Chemistry'),
('Business', 'School of Business and Management');

-- Insert Programs
INSERT INTO programs (program_name, department_id) VALUES
('BSc Computer Science', 1),
('BSc Information Technology', 1),
('BSc Mathematics', 2),
('BSc Statistics', 2),
('BSc Physics', 3),
('BSc Chemistry', 4),
('BBA Business Administration', 5),
('BSc Software Engineering', 1);

-- Insert Modules
INSERT INTO modules (module_code, module_name, program_id) VALUES
('CS101', 'Database Systems', 1),
('CS102', 'Operating Systems', 1),
('CS103', 'System Analysis and Design', 1),
('CS104', 'Algorithm Analysis and Program Design', 1),
('CS105', 'Programming', 1),
('CS106', 'Computer Networks', 1),
('IT101', 'Web Development', 2),
('IT102', 'Mobile App Development', 2),
('MATH101', 'Calculus I', 3),
('MATH102', 'Linear Algebra', 3),
('MATH103', 'Probability and Statistics', 4),
('PHYS101', 'Mechanics', 5),
('CHEM101', 'Organic Chemistry', 6),
('BUS101', 'Principles of Management', 7),
('SE101', 'Software Engineering', 8),
('CS201', 'Data Structures', 1),
('CS202', 'Artificial Intelligence', 1),
('CS203', 'Machine Learning', 1),
('CS204', 'Cybersecurity', 1),
('CS205', 'Cloud Computing', 1);

-- Insert Admins (password: Admin123)
INSERT INTO admins (admin_number, first_name, last_name, email, password) VALUES
('ADM001', 'John', 'Smith', 'admin@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('ADM002', 'Sarah', 'Johnson', 'sarah.admin@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Update departments with admin_id
UPDATE departments SET admin_id = 1 WHERE department_id IN (1, 2, 3);
UPDATE departments SET admin_id = 2 WHERE department_id IN (4, 5);

-- Insert Students (password: Student123)
INSERT INTO students (student_number, first_name, last_name, email, password, program_id) VALUES
('STU001', 'Michael', 'Brown', 'student@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('STU002', 'Emily', 'Davis', 'emily.davis@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('STU003', 'James', 'Wilson', 'james.wilson@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('STU004', 'Sophia', 'Taylor', 'sophia.taylor@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('STU005', 'William', 'Anderson', 'william.anderson@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4),
('STU006', 'Olivia', 'Thomas', 'olivia.thomas@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5),
('STU007', 'Benjamin', 'Jackson', 'benjamin.jackson@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6),
('STU008', 'Emma', 'White', 'emma.white@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 7),
('STU009', 'Lucas', 'Harris', 'lucas.harris@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8),
('STU010', 'Ava', 'Martin', 'ava.martin@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('STU011', 'Alexander', 'Garcia', 'alexander.garcia@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('STU012', 'Mia', 'Rodriguez', 'mia.rodriguez@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('STU013', 'Daniel', 'Clark', 'daniel.clark@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4),
('STU014', 'Charlotte', 'Lewis', 'charlotte.lewis@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5),
('STU015', 'Henry', 'Walker', 'henry.walker@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6),
('STU016', 'Amelia', 'Hall', 'amelia.hall@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 7),
('STU017', 'Sebastian', 'Young', 'sebastian.young@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8),
('STU018', 'Isabella', 'King', 'isabella.king@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('STU019', 'Jack', 'Wright', 'jack.wright@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('STU020', 'Victoria', 'Scott', 'victoria.scott@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('STU021', 'Owen', 'Green', 'owen.green@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4),
('STU022', 'Grace', 'Baker', 'grace.baker@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5),
('STU023', 'Aiden', 'Adams', 'aiden.adams@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6),
('STU024', 'Chloe', 'Nelson', 'chloe.nelson@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 7),
('STU025', 'Liam', 'Hill', 'liam.hill@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 8),
('STU026', 'Zoe', 'Moore', 'zoe.moore@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('STU027', 'Ethan', 'Clark', 'ethan.clark@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2),
('STU028', 'Lily', 'Robinson', 'lily.robinson@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3),
('STU029', 'Noah', 'Carter', 'noah.carter@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4),
('STU030', 'Harper', 'Mitchell', 'harper.mitchell@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5);

-- Insert Tutors (password: Tutor123)
INSERT INTO tutors (tutor_number, first_name, last_name, email, password, phone, status) VALUES
('TUT001', 'Robert', 'Chen', 'tutor@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0101', 'active'),
('TUT002', 'Jennifer', 'Lee', 'jennifer.lee@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0102', 'active'),
('TUT003', 'David', 'Kim', 'david.kim@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0103', 'active'),
('TUT004', 'Amanda', 'Patel', 'amanda.patel@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0104', 'active'),
('TUT005', 'Christopher', 'Wong', 'christopher.wong@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0105', 'active'),
('TUT006', 'Jessica', 'Nguyen', 'jessica.nguyen@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0106', 'active'),
('TUT007', 'Matthew', 'Gupta', 'matthew.gupta@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0107', 'active'),
('TUT008', 'Ashley', 'Singh', 'ashley.singh@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0108', 'active'),
('TUT009', 'Andrew', 'Foster', 'andrew.foster@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0109', 'active'),
('TUT010', 'Stephanie', 'Hayes', 'stephanie.hayes@unitutor.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-0110', 'active');

-- Insert Tutor Modules (assign tutors to modules)
INSERT INTO tutor_modules (tutor_id, module_id, proficiency_level) VALUES
(1, 1, 'advanced'),
(1, 2, 'advanced'),
(1, 16, 'advanced'),
(2, 3, 'advanced'),
(2, 4, 'intermediate'),
(2, 15, 'advanced'),
(3, 5, 'advanced'),
(3, 7, 'advanced'),
(3, 8, 'intermediate'),
(4, 9, 'advanced'),
(4, 10, 'advanced'),
(5, 11, 'advanced'),
(5, 12, 'intermediate'),
(6, 13, 'advanced'),
(6, 14, 'intermediate'),
(7, 17, 'advanced'),
(7, 18, 'intermediate'),
(8, 19, 'advanced'),
(8, 20, 'intermediate'),
(9, 1, 'intermediate'),
(9, 5, 'advanced'),
(10, 6, 'advanced'),
(10, 16, 'intermediate');

-- Insert Available Slots
INSERT INTO available_slots (tutor_id, module_id, slot_date, start_time, end_time, availability_status) VALUES
(1, 1, '2026-10-01', '09:00:00', '10:00:00', 'available'),
(1, 1, '2026-10-01', '10:00:00', '11:00:00', 'available'),
(1, 2, '2026-10-02', '14:00:00', '15:00:00', 'available'),
(2, 3, '2026-10-01', '11:00:00', '12:00:00', 'available'),
(2, 4, '2026-10-03', '09:00:00', '10:00:00', 'available'),
(3, 5, '2026-10-02', '10:00:00', '11:00:00', 'available'),
(3, 7, '2026-10-04', '14:00:00', '15:00:00', 'available'),
(4, 9, '2026-10-01', '15:00:00', '16:00:00', 'available'),
(4, 10, '2026-10-05', '09:00:00', '10:00:00', 'available'),
(5, 11, '2026-10-03', '11:00:00', '12:00:00', 'available'),
(6, 13, '2026-10-02', '16:00:00', '17:00:00', 'available'),
(7, 17, '2026-10-04', '10:00:00', '11:00:00', 'available'),
(8, 19, '2026-10-05', '14:00:00', '15:00:00', 'available'),
(9, 1, '2026-10-01', '13:00:00', '14:00:00', 'available'),
(10, 6, '2026-10-03', '15:00:00', '16:00:00', 'available');

-- Insert Bookings
INSERT INTO bookings (student_id, tutor_id, module_id, slot_id, booking_date, status, reason) VALUES
(1, 1, 1, 1, '2026-09-26 10:00:00', 'confirmed', 'Need help with database normalization'),
(2, 2, 3, 4, '2026-09-26 11:00:00', 'confirmed', 'Struggling with system analysis'),
(3, 3, 5, 6, '2026-09-26 12:00:00', 'pending', 'Programming concepts review'),
(4, 4, 9, 8, '2026-09-26 13:00:00', 'confirmed', 'Calculus help needed'),
(5, 5, 11, 10, '2026-09-26 14:00:00', 'pending', 'Statistics preparation'),
(6, 6, 13, 11, '2026-09-26 15:00:00', 'confirmed', 'Chemistry lab support'),
(7, 7, 17, 12, '2026-09-26 16:00:00', 'completed', 'Data structures review'),
(8, 8, 19, 13, '2026-09-26 17:00:00', 'completed', 'Cybersecurity concepts'),
(9, 1, 2, 3, '2026-09-26 18:00:00', 'cancelled', 'Schedule conflict'),
(10, 9, 1, 14, '2026-09-26 19:00:00', 'confirmed', 'Database design help'),
(11, 2, 4, 5, '2026-09-26 20:00:00', 'pending', 'Algorithm analysis'),
(12, 3, 7, 7, '2026-09-26 21:00:00', 'confirmed', 'Web development project'),
(13, 10, 6, 15, '2026-09-26 22:00:00', 'pending', 'Network protocols'),
(14, 1, 16, 2, '2026-09-26 23:00:00', 'confirmed', 'Data structures help'),
(15, 4, 10, 9, '2026-09-26 23:30:00', 'completed', 'Linear algebra review'),
(16, 5, 12, NULL, '2026-09-27 00:00:00', 'pending', 'Probability theory'),
(17, 6, 14, NULL, '2026-09-27 01:00:00', 'confirmed', 'Business management'),
(18, 7, 18, NULL, '2026-09-27 02:00:00', 'pending', 'Machine learning basics'),
(19, 8, 20, NULL, '2026-09-27 03:00:00', 'confirmed', 'Cloud computing'),
(20, 9, 5, NULL, '2026-09-27 04:00:00', 'cancelled', 'Time conflict');

-- Insert Sessions
INSERT INTO sessions (booking_id, session_date, start_time, end_time, location, status) VALUES
(1, '2026-10-01', '09:00:00', '10:00:00', 'Library Room 101', 'completed'),
(2, '2026-10-01', '11:00:00', '12:00:00', 'Library Room 102', 'completed'),
(4, '2026-10-01', '15:00:00', '16:00:00', 'Library Room 103', 'completed'),
(6, '2026-10-02', '16:00:00', '17:00:00', 'Library Room 104', 'scheduled'),
(7, '2026-10-04', '10:00:00', '11:00:00', 'Library Room 105', 'completed'),
(8, '2026-10-05', '14:00:00', '15:00:00', 'Library Room 106', 'completed'),
(10, '2026-10-01', '13:00:00', '14:00:00', 'Library Room 107', 'scheduled'),
(12, '2026-10-04', '14:00:00', '15:00:00', 'Library Room 108', 'scheduled'),
(14, '2026-10-01', '10:00:00', '11:00:00', 'Library Room 109', 'scheduled'),
(15, '2026-10-05', '09:00:00', '10:00:00', 'Library Room 110', 'completed'),
(17, '2026-10-03', '11:00:00', '12:00:00', 'Library Room 111', 'scheduled'),
(19, '2026-09-27', '04:00:00', '05:00:00', 'Library Room 112', 'cancelled');

-- Insert Feedback
INSERT INTO feedback (student_id, tutor_id, booking_id, rating, comments) VALUES
(1, 1, 1, 5, 'Excellent tutor! Explained database concepts very clearly.'),
(2, 2, 2, 4, 'Very helpful session. Good explanation of system analysis.'),
(4, 4, 4, 5, 'Amazing calculus tutor. Made complex topics easy to understand.'),
(6, 6, 6, 4, 'Great chemistry help. Patient and knowledgeable.'),
(7, 7, 7, 5, 'Best data structures tutor! Very thorough explanations.'),
(8, 8, 8, 4, 'Good cybersecurity session. Learned a lot.'),
(10, 1, 10, 5, 'Another excellent session with Robert. Highly recommend!'),
(12, 3, 12, 4, 'Helpful web development guidance. Practical examples.'),
(14, 1, 14, 5, 'Consistently great tutoring. Very professional.'),
(15, 4, 15, 4, 'Good linear algebra review. Clear explanations.');

-- Insert Payments
INSERT INTO payments (student_id, admin_id, amount, payment_date, payment_status, reference) VALUES
(1, 1, 25.00, '2026-09-26', 'completed', 'PAY001'),
(2, 1, 25.00, '2026-09-26', 'completed', 'PAY002'),
(3, 1, 25.00, '2026-09-26', 'pending', 'PAY003'),
(4, 1, 25.00, '2026-09-26', 'completed', 'PAY004'),
(5, 1, 25.00, '2026-09-26', 'pending', 'PAY005'),
(6, 2, 25.00, '2026-09-26', 'completed', 'PAY006'),
(7, 2, 25.00, '2026-09-26', 'completed', 'PAY007'),
(8, 2, 25.00, '2026-09-26', 'completed', 'PAY008'),
(9, 2, 25.00, '2026-09-26', 'refunded', 'PAY009'),
(10, 2, 25.00, '2026-09-26', 'completed', 'PAY010'),
(11, 1, 25.00, '2026-09-27', 'pending', 'PAY011'),
(12, 1, 25.00, '2026-09-27', 'completed', 'PAY012'),
(13, 1, 25.00, '2026-09-27', 'pending', 'PAY013'),
(14, 2, 25.00, '2026-09-27', 'completed', 'PAY014'),
(15, 2, 25.00, '2026-09-27', 'completed', 'PAY015');

-- Insert Applications
INSERT INTO applications (student_id, application_date, application_type, status, admin_id, notes) VALUES
(11, '2026-09-20', 'tutor_application', 'approved', 1, 'Strong academic record'),
(12, '2026-09-21', 'tutor_application', 'approved', 1, 'Good communication skills'),
(13, '2026-09-22', 'tutor_application', 'pending', NULL, 'Under review'),
(14, '2026-09-23', 'tutor_application', 'rejected', 2, 'Insufficient experience'),
(15, '2026-09-24', 'tutor_application', 'approved', 2, 'Excellent candidate'),
(16, '2026-09-25', 'tutor_application', 'pending', NULL, 'Awaiting interview'),
(17, '2026-09-25', 'tutor_application', 'approved', 1, 'Qualified applicant'),
(18, '2026-09-26', 'tutor_application', 'pending', NULL, 'New application'),
(19, '2026-09-26', 'tutor_application', 'approved', 2, 'Strong recommendation'),
(20, '2026-09-26', 'tutor_application', 'rejected', 1, 'Does not meet requirements');

-- Update available_slots status for booked slots
UPDATE available_slots SET availability_status = 'booked' WHERE slot_id IN (1, 4, 6, 8, 10, 11, 12, 13, 14, 3, 5, 7, 15, 2, 9);

-- =====================================================
-- END OF SAMPLE DATA
-- =====================================================
