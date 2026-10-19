-- Web-Based Student Academic Performance Monitoring System
CREATE DATABASE IF NOT EXISTS sams_db;
USE sams_db;

DROP TABLE IF EXISTS attendance, grades, enrollments, subjects, teachers, students, users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','teacher','student') NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  student_number VARCHAR(20) NOT NULL UNIQUE,
  year_level VARCHAR(20),
  section VARCHAR(20),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE teachers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  employee_number VARCHAR(20) NOT NULL UNIQUE,
  department VARCHAR(50),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subject_code VARCHAR(20) NOT NULL UNIQUE,
  subject_name VARCHAR(100) NOT NULL,
  units DECIMAL(3,1) DEFAULT 3.0,
  teacher_id INT NULL,
  school_year VARCHAR(9) DEFAULT '2026-2027',
  semester VARCHAR(20) DEFAULT '1st Semester',
  FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
);
CREATE TABLE enrollments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  UNIQUE KEY unique_enrollment (student_id, subject_id)
);
CREATE TABLE grades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  enrollment_id INT NOT NULL,
  grading_period ENUM('Prelim','Midterm','Final') NOT NULL,
  score DECIMAL(5,2) NOT NULL,
  remarks VARCHAR(100),
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
  UNIQUE KEY unique_grade (enrollment_id, grading_period)
);
CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  enrollment_id INT NOT NULL,
  attendance_date DATE NOT NULL,
  status ENUM('Present','Absent','Late','Excused') NOT NULL,
  FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
  UNIQUE KEY unique_attendance (enrollment_id, attendance_date)
);

-- Sample data. Password for ALL accounts: password123
INSERT INTO users (username, password, role, full_name, email) VALUES
('admin','$2y$12$KfbRjGuSlcSsZ/2kYy.qV.u1Okc/neKYbUtkmlnD.Ss9oncrQsM4O','admin','System Administrator','admin@sams.edu'),
('tsmith','$2y$12$KfbRjGuSlcSsZ/2kYy.qV.u1Okc/neKYbUtkmlnD.Ss9oncrQsM4O','teacher','Mrs. Tara Smith','tsmith@sams.edu'),
('jcruz','$2y$12$KfbRjGuSlcSsZ/2kYy.qV.u1Okc/neKYbUtkmlnD.Ss9oncrQsM4O','teacher','Mr. Jun Cruz','jcruz@sams.edu'),
('mreyes','$2y$12$KfbRjGuSlcSsZ/2kYy.qV.u1Okc/neKYbUtkmlnD.Ss9oncrQsM4O','student','Maria Reyes','mreyes@sams.edu'),
('jdelacruz','$2y$12$KfbRjGuSlcSsZ/2kYy.qV.u1Okc/neKYbUtkmlnD.Ss9oncrQsM4O','student','Juan Dela Cruz','jdelacruz@sams.edu'),
('asantos','$2y$12$KfbRjGuSlcSsZ/2kYy.qV.u1Okc/neKYbUtkmlnD.Ss9oncrQsM4O','student','Ana Santos','asantos@sams.edu');
INSERT INTO teachers (user_id, employee_number, department) VALUES (2,'T-1001','Mathematics'),(3,'T-1002','Science');
INSERT INTO students (user_id, student_number, year_level, section) VALUES
(4,'S-2026-001','3rd Year','BSCS-3A'),(5,'S-2026-002','3rd Year','BSCS-3A'),(6,'S-2026-003','3rd Year','BSCS-3B');
INSERT INTO subjects (subject_code, subject_name, units, teacher_id) VALUES
('MATH301','Calculus III',3.0,1),('SCI210','General Physics',4.0,2);
INSERT INTO enrollments (student_id, subject_id) VALUES (1,1),(2,1),(3,1),(1,2),(2,2);
INSERT INTO grades (enrollment_id, grading_period, score, remarks) VALUES
(1,'Prelim',88.50,''),(1,'Midterm',85.00,''),(2,'Prelim',76.00,''),(3,'Prelim',92.00,''),(4,'Prelim',81.00,'');
INSERT INTO attendance (enrollment_id, attendance_date, status) VALUES
(1,'2026-09-01','Present'),(1,'2026-09-03','Present'),(1,'2026-09-08','Late'),
(2,'2026-09-01','Absent'),(2,'2026-09-03','Present'),(3,'2026-09-01','Present');
