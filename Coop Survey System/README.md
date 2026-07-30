# Web-Based Survey Management System for a Multipurpose Cooperative
IT 305 — Advance Web Development | Student Group Activity (Act 5 — Set B)

A full PHP + MySQL web application where cooperative members log in to answer
surveys, and cooperative staff create surveys, monitor participation, and
generate results/reports.

---

## 1. Tech Stack

- **HTML5 / CSS3** — no framework, just a plain custom stylesheet
  (`assets/css/style.css`) with its own small grid/utility system
- **JavaScript** — star-rating widget, dynamic question builder, client-side
  validation, delete confirmations (`assets/js/main.js`)
- **PHP 8** — procedural style using **PDO** with prepared statements throughout
  (no raw string-concatenated SQL, so the app is protected against SQL injection)
- **MySQL** — 7 core tables + 1 optional login-history table
- **Chart.js** (via CDN) — bar/pie charts on the Results Dashboard

---

## 2. Folder Structure

```
survey-system/
├── config/database.php        Database connection + session bootstrap
├── database/survey_system.sql Full schema + seed data (import this first)
├── includes/                  Shared header/footer/functions
├── assets/css/style.css       Cooperative theme
├── assets/js/main.js          Client-side behaviors
├── index.php                  Portal selector (Member vs Staff)
├── member/                    Member Module (7 pages)
└── staff/                     Cooperative Staff Module (Survey, Question,
                                Member, Results, Reports, User Management)
    └── uploads/profile_photos/  Staff profile picture uploads (auto-created)
```

---

## 3. Setup Instructions (XAMPP / local server)

1. Copy the whole `survey-system` folder into your `htdocs` directory
   (e.g. `C:\xampp\htdocs\survey-system`).
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
4. Click **Import**, choose `database/survey_system.sql`, and run it.
   This creates the `survey_system` database, all tables, and seed accounts.
5. If your MySQL root user has a password, edit `config/database.php` and
   update `DB_USER` / `DB_PASS` accordingly. Default is `root` / *(blank)*,
   which matches a stock XAMPP install.

   > **Already imported an earlier copy of this project?** The `staff` table
   > gained `first_login` and `profile_photo` columns. Either re-import
   > `survey_system.sql` into a fresh database, or run the migration commands
   > commented near the top of that file instead of starting over.

6. `staff/uploads/profile_photos/` is created automatically the first time a
   staff member uploads a photo. A `.htaccess` in `staff/uploads/` blocks any
   uploaded file from being executed as a script — this relies on Apache's
   `AllowOverride` being enabled, which is the XAMPP default, so no extra
   configuration should be needed.
7. Visit `http://localhost/survey-system/` in your browser.

### Default login credentials (seed data)

| Portal | Username / Account # | Password   | Notes                       |
|--------|-----------------------|------------|------------------------------|
| Staff  | `admin`               | `admin123` | Administrator role — permanent default, not auto-generated |
| Member | `2024-0001`           | `2024-0001`| First login forces password change |
| Member | `2024-0002`           | `2024-0002`| First login forces password change |

**Adding new staff accounts:** go to Staff → User Management → Add Staff
Account. You only enter name, email, and role — the system generates an
account number like `STF-0002`, which is also that account's default
password. Share it with them directly (there's no email delivery built in).
On their first login they'll see a dismissible reminder to change it — they
can do it right away or click "remind me later" and change it anytime from
their **Profile** page.

---

## 4. Feature Checklist (mapped to the activity brief)

**A. Member Module**
- [x] Login with Cooperative Account Number, default password = account number
- [x] Forced password change on first login (`first_login` flag)
- [x] View active surveys (`available_surveys.php`, `dashboard.php`)
- [x] Answer surveys only once (DB unique constraint + app-level check)
- [x] Submit survey responses (`submit_survey.php`)
- [x] Confirmation screen after submission (`confirmation.php`)
- [x] Update profile — optional (`profile.php`)

**B. Cooperative Staff Module**
- [x] Secure login (`staff/login.php`, bcrypt-hashed passwords)
- [x] Auto-generated staff account numbers (`STF-0001`, `STF-0002`, ...) — the
  admin only enters name/email/role; the account number doubles as the login
  and default password, exactly like the Member Module (`user_create.php`)
- [x] Dismissible first-login password reminder on the staff dashboard —
  staff can change it immediately or click "remind me later" and keep working
  (`staff/dashboard.php`)
- [x] Unified staff **Profile** page — view account details, edit name/email,
  change password, and upload/remove a profile picture, all in one place
  (`staff/profile.php`)
- [x] Profile picture upload with server-side validation (type/size/real-image
  check) and a script-execution-blocked upload folder (`staff/uploads/`)
- [x] Downloadable profile summary via browser print-to-PDF (`staff/profile_pdf.php`)
- [x] Create / edit / deactivate surveys (`survey_create.php`, `survey_edit.php`, `survey_status.php`)
- [x] Question types: Multiple Choice, Yes/No, Rating Scale, Short Answer
- [x] Set open/close dates
- [x] View respondents (`respondents.php`)
- [x] Monitor participation rate
- [x] Auto-computed results (live SQL aggregation, not stale cached numbers)
- [x] Tables + Chart.js charts on results pages
- [x] Printable reports (`report_view.php`, browser print/"Save as PDF")
- [x] Export to Excel — CSV export that opens directly in Excel (`export_csv.php`)

**C. Database** — `members`, `staff`, `surveys`, `survey_questions`,
`survey_choices`, `responses`, `response_answers`, `login_history` (optional).
Survey Results are computed live via SQL joins instead of a separate cached
table — see the note at the bottom of `survey_system.sql` for the reasoning.

**D/E. Functional & Non-Functional Requirements** — authentication for both
roles, duplicate-submission prevention, graphical summaries, responsive
plain responsive layout, organized relational schema with foreign keys.

**F. Suggested Pages** — every page listed in the brief exists under
`member/` and `staff/`, plus a few supporting action-scripts
(`survey_status.php`, `question_delete.php`, etc.) that keep each page focused.

---

## 5. Still on you before submission

The brief's submission requirements include a few things Claude can't produce:
- **Video presentation** demoing the system
- **Screenshots** of the Login, Dashboard, Update Personal Information, and
  Successful Update Confirmation pages (`member/profile.php` covers the last two)
- **Members' participation writeup** — who built which part

Everything else (source code, this README, and the `.sql` file) is ready to submit.
