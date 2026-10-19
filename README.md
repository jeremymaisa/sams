# Web-Based Student Academic Performance Monitoring System (SAMS)

PHP + MySQL system with three roles (Admin, Teacher, Student) for tracking grades,
attendance, and performance charts.

## Project Team
1. Maisa, Jeremy O.
2. Malicay, Jayson Mark T.
3. Dacles, Rayver E.
4. Garbo, Johnpaul B.
5. Balderamos, Gerald D.
6. Villar, Emmalyn D.
7. Morfe, Riczhie Kyreme M.
8. Bayongan, Keth Lee Mark B.

## Setup (XAMPP)
1. Unzip and copy the `sams` folder into `C:\xampp\htdocs\` (so you have `C:\xampp\htdocs\sams`).
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open http://localhost/phpmyadmin -> **Import** -> choose `sams/sql/schema.sql` -> Go.
4. If your MySQL has a password, edit `config/db.php`.
5. If you rename the folder, edit `BASE_URL` in `includes/auth.php` (default `/sams`).
6. Open http://localhost/sams/

## Demo accounts (password for all: password123)
| Username  | Role    |
|-----------|---------|
| admin     | Admin   |
| tsmith    | Teacher (Calculus III) |
| jcruz     | Teacher (General Physics) |
| mreyes    | Student |
| jdelacruz | Student |
| asantos   | Student |

## Features
- Admin: manage users, subjects, enrollments; overview dashboard with chart
- Teacher: input/edit Prelim/Midterm/Final grades; record attendance (own subjects only)
- Student: view grades, attendance history, performance report with charts (Chart.js)

## How it works (for defense)
- Passwords use password_hash()/password_verify(); sessions store user id, role, and name.
- Every protected page calls require_role() from includes/auth.php.
- Grades and attendance reference an enrollment (a student in a subject), and teacher
  saves are checked against the subjects they own.
- All queries use PDO prepared statements; all output is escaped with h().
- Charts use SQL aggregates (AVG, CASE WHEN, GROUP BY) passed to Chart.js as JSON.

## Structure
config/db.php, includes/{auth,header,footer}.php, login.php, logout.php, index.php, about.php,
admin/*, teacher/*, student/*, assets/css/style.css, sql/schema.sql
