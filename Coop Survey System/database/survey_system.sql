-- ============================================================
-- Web-Based Survey Management System for a Multipurpose Cooperative
-- IT 305 - Advance Web Development | Activity 5 - Set B
-- ============================================================

CREATE DATABASE IF NOT EXISTS survey_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE survey_system;

-- ------------------------------------------------------------
-- Table: staff  (Cooperative Staff / Admin accounts)
-- ------------------------------------------------------------
CREATE TABLE staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    profile_photo VARCHAR(255) NULL,
    role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    first_login TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- If you already imported an earlier version of this file into a live
-- database, run this instead of re-importing everything from scratch:
--   ALTER TABLE staff ADD COLUMN first_login TINYINT(1) NOT NULL DEFAULT 1 AFTER status;
--   ALTER TABLE staff ADD COLUMN profile_photo VARCHAR(255) NULL AFTER email;
--   UPDATE staff SET first_login = 0 WHERE username = 'admin';

-- ------------------------------------------------------------
-- Table: members  (Cooperative Members)
-- ------------------------------------------------------------
CREATE TABLE members (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    account_number VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    contact_number VARCHAR(20),
    address VARCHAR(255),
    first_login TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: surveys
-- ------------------------------------------------------------
CREATE TABLE surveys (
    survey_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    open_date DATETIME NOT NULL,
    close_date DATETIME NOT NULL,
    status ENUM('draft','active','closed') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES staff(staff_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: survey_questions
-- ------------------------------------------------------------
CREATE TABLE survey_questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('multiple_choice','yes_no','rating','short_answer') NOT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    question_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (survey_id) REFERENCES surveys(survey_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: survey_choices  (options for multiple_choice questions)
-- ------------------------------------------------------------
CREATE TABLE survey_choices (
    choice_id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    choice_text VARCHAR(255) NOT NULL,
    choice_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES survey_questions(question_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: responses  (one row per member submission per survey)
-- ------------------------------------------------------------
CREATE TABLE responses (
    response_id INT AUTO_INCREMENT PRIMARY KEY,
    survey_id INT NOT NULL,
    member_id INT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_member_survey (survey_id, member_id),
    FOREIGN KEY (survey_id) REFERENCES surveys(survey_id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: response_answers  (individual answers within a response)
-- ------------------------------------------------------------
CREATE TABLE response_answers (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    response_id INT NOT NULL,
    question_id INT NOT NULL,
    choice_id INT NULL,
    rating_value TINYINT NULL,
    answer_text TEXT NULL,
    FOREIGN KEY (response_id) REFERENCES responses(response_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES survey_questions(question_id) ON DELETE CASCADE,
    FOREIGN KEY (choice_id) REFERENCES survey_choices(choice_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: login_history (optional requirement)
-- ------------------------------------------------------------
CREATE TABLE login_history (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('member','staff') NOT NULL,
    user_id INT NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45)
) ENGINE=InnoDB;

-- Note on "Survey Results" table required by the spec:
-- Results are computed live (COUNT/AVG queries joining responses -> response_answers)
-- instead of being cached in a separate table. This keeps numbers always accurate and
-- avoids update-anomalies that a duplicated results table would introduce. See
-- staff/results_dashboard.php and staff/survey_results.php for the aggregation queries.

-- ============================================================
-- SEED DATA
-- ============================================================

-- Default staff/admin account -> username: admin | password: admin123
-- first_login is set to 0 because this is a documented, permanent default
-- credential (see README), not an auto-generated account that needs a
-- first-login password-change nudge like newly created staff accounts do.
INSERT INTO staff (username, password, full_name, email, role, first_login) VALUES
('admin', '$2y$10$82mA5uBVZRTQfk8tTju7EelJoOfVOn5pXwIrFiHA7Rr3utT7sOJjO', 'Cooperative Administrator', 'admin@coop.local', 'admin', 0);
-- NOTE: the hash above is bcrypt for "admin123"

-- Sample members -> username = account number, default password = account number
-- (2024-0001 / 2024-0001), (2024-0002 / 2024-0002)
INSERT INTO members (account_number, password, full_name, email, contact_number, first_login) VALUES
('2024-0001', '$2y$10$FqX3dwgtKuTNsXsZzMm1LOJo8zgmUgnuOmo/l0OaNSdjn2zm0n1pK', 'Juan Dela Cruz', 'juan@example.com', '09171234567', 1),
('2024-0002', '$2y$10$hy8gvRrlalhtXdr2ixYN5.LimCL11dJx5de3PBjKDE/QQJerPtNnm', 'Maria Santos', 'maria@example.com', '09179876543', 1);
