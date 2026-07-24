# IRIS — Progress Tracker

## Current Score: 6/10 → Target: 9/10

---

## DONE

### [x] Git secret cleanup
- Removed `db_connect.php`, `send_email.php`, `Final_IRIS.ino`, `debug_log.txt` from git tracking
- Updated `.gitignore` with secrets, IDE files, test artifacts
- Created example template files
- Force-pushed clean history to `origin/main`

### [x] library.php fixes
- Full POST handlers for issue/return/add books with prepared statements
- Fixed `event` undefined bug in `showTab()`
- Changed year `max` from hardcoded 2024 to dynamic `date('Y')`
- `htmlspecialchars()` on all DB output
- Role check → `checkPermission()` for RBAC tabs

### [x] reports.php SQL injection fix
- Parameterized department chart query (`WHERE d.dept_code = ?`) at line 214
- Refactored `if/else` to assign `$chartResult` directly

### [x] Password hashing
- `password_hash(PASSWORD_BCRYPT)` / `password_verify()` on: `login_action.php`, `create_user.php`, `reset_password.php`, `settings.php`
- On-login auto-migration of plaintext passwords
- Created `migrate_passwords.php` one-time bulk migration script

### [x] Session security
- `session_regenerate_id(true)` on login
- `session_set_cookie_params(['httponly'=>true, 'samesite'=>'Strict'])` in `login_action.php`

### [x] Auth on unprotected endpoints
- `session_start()` + `$_SESSION['user']` check on `export_csv.php`, `export_pdf.php`, `import_students.php`
- API key auth via `X-API-Key` header on `rfid_api.php`

### [x] GET-based CSRF deletion fixed
- `manage_users.php` changed from `?delete=X` GET to POST form with CSRF token + admin-only gate

### [x] XSS fixes
- `htmlspecialchars()` on DB output in `manage_departments.php`, `export_pdf.php`, `send_email.example.php`

### [x] Header injection fixed
- `safeRedirect()` helper in `rbac_helper.php` that strips `\r\n`
- Applied to all `header("Location: ...")` calls in `rbac_helper.php` and `rbac.php`

### [x] Weak password generator fixed
- Replaced `str_shuffle()` on 8-char `A-Z0-9` with `random_int()` on 12-char full set (lowercase + special chars)

### [x] Code quality — shared layout (9 files converted)
- Created `header.php` with centralized sidebar + shared CSS
- Created `footer.php` with `toggleSidebar()` JS
- Converted: `dashboard.php`, `attendance.php`, `add_student.php`, `create_user.php`, `library.php`, `manage_departments.php`, `manage_users.php`, `reports.php`, `settings.php`

### [x] Phase 1 — Critical Security (COMPLETED 2026-07-08)

- **P1.1 — Fix settings.php plaintext passwords**
  - `password_verify()` + `password_hash()` in change_password handler
  - `password_hash()` in add_user handler
- **P1.2 — Move SMTP credentials to `.env`**
  - Built-in `parse_ini_file()` (no composer dep needed)
  - Created `.env` with SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS
  - Updated `send_email.php` to load from `$_ENV`
  - Added `.env` to `.gitignore`
  - Removed hardcoded creds from `send_email.php`
- **P1.3 — Widen `users.password` column**
  - `ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL;`
  - Updated `database_schema.sql`
- **P1.4 — Add CSRF to all POST forms**
  - `csrf_token()` / `verify_csrf()` helpers in `rbac_helper.php`
  - Applied to: `add_student.php`, `library.php`, `settings.php`, `manage_departments.php`, `create_user.php`, `reset_password.php`, `send_email.php`, `manage_users.php`
- **P1.5 — Add Secure flag to session cookie**
  - Added `'secure' => true` to `session_set_cookie_params()` in `login_action.php`

---

## TODO (ordered by priority)

### Phase 2 — Missing Core Features (COMPLETED 2026-07-08)

- **P2.1 — Student list page (`view_students.php`)**
  - Searchable table with Name, Roll Number, Department, Email, RFID, Delete
  - CSRF-protected delete
  - Linked from sidebar + dashboard quick actions
- **P2.2 — Manual attendance page (`mark_attendance.php`)**
  - RFID input + IN/OUT toggle UI
  - Side panel showing the 20 most recent entries
  - Linked from sidebar + dashboard quick actions
- **P2.3 — Aesthetic dashboard charts**
  - 2x2 grid with doughnut (IN/OUT), bar (department), line (weekly), bar (hourly)
  - Smaller 200px containers with pastel gradient backgrounds + matching accents
  - Chart.js with responsive config

### Phase 3 — Hardening

- [ ] **P3.1 — RBAC enforcement on remaining pages**
  - `dashboard.php` — Add `requirePermission('view_dashboard')`
  - `add_student.php` — Add `requirePermission('add_student')`
  - `reports.php` — Add `requirePermission('view_reports')`
  - `manage_departments.php` — Add `requirePermission('manage_departments')`
  - `settings.php` — Add `requirePermission('access_settings')`

- [ ] **P3.2 — Real PDF export**
  - Replace `window.print()` in `reports.php:344` with Dompdf PDF generation
  - Follow pattern from `export_pdf.php`

- [ ] **P3.3 — Remove duplicate CSV import**
  - Decide whether to keep `add_student.php` bulk or `import_students.php`
  - Remove the other, add nav link to the survivor

---

## Relevant Files

| File | Purpose |
|------|---------|
| `login_action.php` | Password verify + session security + plaintext migration |
| `rbac_helper.php` | `safeRedirect()`, `csrf_token()`, `verify_csrf()`, `checkPermission()` |
| `rbac.php` | `requirePermission()` with CRLF stripping |
| `header.php` | Centralized sidebar + shared CSS |
| `footer.php` | Centralized `toggleSidebar()` + closing HTML |
| `database_schema.sql` | Full deployment-ready schema with triggers + seed data |
| `migrate_passwords.php` | One-time script to bcrypt-hash existing plaintext passwords |
| `PROGRESS.md` | This file |

## DB Context
- **DB name:** `studentdb` on localhost MySQL
- **6 tables:** `users`, `students`, `attendance`, `departments`, `books`, `book_transactions`
- **`users.password` is `VARCHAR(255)`** — already widened for bcrypt compatibility
