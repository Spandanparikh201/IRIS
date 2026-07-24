# IRIS - Intelligent RFID Identification System

A web-based attendance management system for educational institutions that uses **RFID technology** to track student attendance in real time. It integrates an ESP32-based RFID scanner with a PHP web backend, MySQL database, email notifications, and PDF reporting.

---

## System Overview

```
┌─────────────────────┐     ┌──────────────────────┐     ┌──────────────┐
│  ESP32 RFID Scanner │────▶│  PHP Web Application  │────▶│  MySQL DB    │
│  (MFRC522 + LCD)    │HTTP │  (rfid_api.php)       │     │  (studentdb) │
└─────────────────────┘     └──────────────────────┘     └──────────────┘
                                    │
                                    ├──▶ Email Reports (PHPMailer)
                                    ├──▶ PDF Reports (Dompdf)
                                    ├──▶ CSV Exports
                                    └──▶ MQTT (Adafruit IO)
```

## Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP (plain, no framework) |
| Database | MySQL 8.x |
| Frontend | HTML5, CSS3, JavaScript, Chart.js, Font Awesome 6 |
| RFID Hardware | ESP32, MFRC522 RFID module, 16x2 I2C LCD |
| IoT/MQTT | Adafruit IO |
| PDF | Dompdf |
| Email | PHPMailer via Gmail SMTP |
| Environment | vlucas/phpdotenv |
| Testing | PHPUnit, Playwright PHP |
| Package Manager | Composer |

---

## File-by-File Guide

### Configuration & Setup

| File | What it does |
|------|-------------|
| `.env` | **Contains secrets.** Stores SMTP credentials (Gmail), database connection details. **Never commit this to GitHub.** |
| `.env.example` | Template showing what variables go in `.env`. Safe to share. |
| `db_connect.php` | **Contains secrets.** Loads `.env`, creates MySQLi database connection. **Never commit this.** |
| `db_connect.example.php` | Example version of db_connect.php with dummy values. Safe to share. |
| `composer.json` | Declares PHP dependencies (PHPMailer, Dompdf, phpdotenv, PHPUnit, Playwright) |
| `composer.lock` | Locks dependency versions so everyone installs the same |
| `phpunit.xml` | PHPUnit configuration with test suites (Unit, Integration, Characterization, E2E) |
| `.gitignore` | Tells Git which files to ignore (secrets, caches, IDE files, backups, vendor) |

### Entry Points & Authentication

| File | What it does |
|------|-------------|
| `index.php` | Public landing page — shows IRIS branding, feature overview cards, login button |
| `login.php` | Login form. Redirects to dashboard if already logged in. |
| `login_action.php` | Processes login: looks up user, verifies bcrypt password, starts session, redirects to dashboard or password reset |
| `logout.php` | Destroys session and returns to index |
| `reset_password.php` | Forces password change on first login (min 8 chars, must have uppercase, lowercase, digit) |

### Layout & Navigation

| File | What it does |
|------|-------------|
| `header.php` | Shared HTML header + sidebar navigation. Included at the top of every page. Adapts menu based on user role. |
| `footer.php` | Shared footer. Closes main content div, loads page-specific scripts. |

### Dashboard & Home

| File | What it does |
|------|-------------|
| `dashboard.php` | Main overview: shows stats (total students, present today, attendance rate), quick action buttons, recent attendance feed (last 15) |
| `settings.php` | Password change form for all users. Admins also see "Create User" form + system info summary. |

### Student Management

| File | What it does |
|------|-------------|
| `add_student.php` | Register a single student. Auto-generates roll number (`<year><dept><3-digit-seq>`). |
| `bulk_upload.php` | Upload many students at once via CSV. Shows preview before importing. |
| `view_students.php` | Searchable student table with delete. Filters by department for non-admin roles. |
| `students_template.csv` | CSV template with header row + 2 sample rows for bulk upload. |

### Attendance

| File | What it does |
|------|-------------|
| `mark_attendance.php` | Manually mark attendance: enter RFID UID, select IN/OUT. Shows last 20 marks. |
| `attendance.php` | Analytics dashboard with Chart.js charts: pie (IN/OUT split), bar (by department), line (7-day trend), bar (hourly distribution). Date filtering. |
| `rfid_api.php` | JSON API for ESP32 scanner. Accepts RFID tag + API key, toggles IN/OUT, returns student info. |

### Reports & Exports

| File | What it does |
|------|-------------|
| `reports.php` | Report generation hub: Daily, Weekly, Monthly, Department, Student reports. Exports to CSV or PDF. |
| `send_email.php` | Email attendance reports via SMTP. Supports daily/monthly/yearly periods, personalized PDFs per student, bulk send. |
| `export_csv.php` | Quick CSV export of all attendance records. |
| `export_pdf.php` | Quick PDF export of all attendance records via Dompdf. |

### Users & Permissions

| File | What it does |
|------|-------------|
| `rbac.php` | Role-Based Access Control config: defines 4 roles (admin, hod, teacher, staff) with ~40 permissions and helper functions. |
| `rbac_helper.php` | Utility functions: CSRF protection, permission checks, safe redirects, department scoping. |
| `manage_users.php` | Admin-only: list all users with role badges, delete users. |
| `create_user.php` | Admin-only: create new user, auto-generates strong 12-char password. |

### Department Management

| File | What it does |
|------|-------------|
| `manage_departments.php` | Admin-only: CRUD for departments. Add/edit/delete with student count tracking. |

### Arduino / RFID Hardware

| File | What it does |
|------|-------------|
| `Final_IRIS.ino` | **Contains secrets.** ESP32 firmware: connects to WiFi, reads RFID tags via MFRC522, POSTs to rfid_api.php, shows name on LCD, publishes to Adafruit IO MQTT, auto-emails hourly. **Never commit this.** |
| `Final_IRIS.example.ino` | Template version with placeholder credentials. Safe to share. |

### Database / SQL Files

| File | What it does |
|------|-------------|
| `schema.sql` | Full production schema dump of `studentdb` database |
| `database_schema.sql` | Deployment-ready schema with triggers + seed data (departments, users, books) |
| `setup_users.sql` | Original users table + sample users |
| `update_users_for_hod.sql` | Migration: adds `hod` role, inserts HOD users |
| `update_users_for_library.sql` | Placeholder, no longer relevant |
| `update_departments.sql` | Adds ENUM departments to students/attendance tables |
| `department_master_setup.sql` | Department table creation + seed + triggers |
| `department_master_optimized.sql` | Optimized version with ENUM dept_code + analytical queries |
| `department_queries.sql` | 7 handy analytical queries for department reporting |

---

## Directory Structure

```
IRIS/
├── assets/                  # CSS themes, images
│   └── iris-theme.css       # Optional dark theme
├── backups/                 # SQL database backups (gitignored)
├── src/                     # PSR-4 namespace IRIS\ (reserved for future OOP code)
├── tests/                   # PHPUnit test files
│   ├── Unit/                # Unit tests
│   ├── Integration/         # Integration tests
│   ├── Characterization/    # Characterization tests
│   └── Fixtures/            # Test fixture data
├── vendor/                  # Composer dependencies (gitignored)
├── test-failures/           # Test failure screenshots (gitignored)
├── .planning/               # Planning docs (gitignored)
├── .idea/                   # JetBrains IDE config (gitignored)
├── .phpunit.cache/          # PHPUnit cache (gitignored)
```

---

## Roles & Permissions

| Role | Access |
|------|--------|
| **admin** | Full access to everything — all pages, all departments |
| **hod** | Head of Department — can manage their own department's students, attendance, reports |
| **teacher** | Can mark/view attendance and generate reports (department-scoped) |
| **staff** | Basic attendance marking only (department-scoped) |

Non-admin users can only see data from their own department.

---

## How RFID Scanning Works

1. Student taps RFID card on the **ESP32 + MFRC522** reader
2. ESP32 sends the RFID UID to `rfid_api.php` via HTTP POST
3. Server looks up the student by RFID, checks last scan status
4. Toggles IN → OUT or OUT → IN (with 20-second cooldown to prevent double-scans)
5. Returns student name, department, current status, and active count
6. ESP32 displays the info on the LCD screen
7. Attendance is recorded in the database with a timestamp

---

## Security Features

- Bcrypt password hashing (auto-migrates old plaintext passwords)
- CSRF tokens on all forms
- Prepared statements (prevents SQL injection)
- `htmlspecialchars()` on all output (prevents XSS)
- Session regeneration on login
- Secure cookie params (httponly, samesite=Strict, secure)
- API key authentication for RFID endpoint
- Role-based access control + department scoping
- 30-minute session inactivity timeout
- Secrets stored in `.env` (gitignored)
