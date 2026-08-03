-- =========================================================
-- StudySync - Smart Study Planner & Goal Tracker
-- Database Schema (MySQL / MariaDB - for XAMPP phpMyAdmin)
-- =========================================================
-- Import this file via phpMyAdmin, or run:
--   mysql -u root -p < studysync.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS studysync CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studysync;

-- ---------------------------------------------------------
-- Table: users
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,          -- password_hash()
    profile_pic VARCHAR(255) DEFAULT 'assets/img/default-avatar.png',
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expiry DATETIME DEFAULT NULL,
    study_streak INT DEFAULT 0,
    last_active_date DATE DEFAULT NULL,
    theme ENUM('light','dark') DEFAULT 'light',
    status ENUM('active','blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: admin
-- ---------------------------------------------------------
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin login: username = admin | password = Admin@123
INSERT INTO admin (username, email, password) VALUES
('admin', 'admin@studysync.com', '$2b$10$HLkHOWbnUc.lb4QlWsZj8.GA4UMudPwV7yG08e8Zf05gfCbn70cVy');
-- The hash above is a genuine bcrypt hash for the password 'Admin@123'
-- and works directly with PHP's password_verify(). Change it after first login
-- via a custom admin "change password" query if desired.

-- ---------------------------------------------------------
-- Table: subjects
-- ---------------------------------------------------------
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    color VARCHAR(20) DEFAULT '#6C63FF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: study_tasks  (Study Planner)
-- ---------------------------------------------------------
CREATE TABLE study_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_id INT DEFAULT NULL,
    topic VARCHAR(150) NOT NULL,
    task_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    priority ENUM('High','Medium','Low') DEFAULT 'Medium',
    status ENUM('Pending','Completed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: todos  (Daily To-Do List)
-- ---------------------------------------------------------
CREATE TABLE todos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    task_title VARCHAR(200) NOT NULL,
    due_date DATE DEFAULT NULL,
    is_completed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: goals  (Goal Tracker)
-- ---------------------------------------------------------
CREATE TABLE goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    goal_title VARCHAR(150) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    target_date DATE DEFAULT NULL,
    progress INT DEFAULT 0,             -- 0 to 100
    status ENUM('Not Started','In Progress','Completed') DEFAULT 'Not Started',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: notes
-- ---------------------------------------------------------
CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    content TEXT,
    is_pinned TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: study_hours  (for Analytics / streak / dashboard)
-- ---------------------------------------------------------
CREATE TABLE study_hours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    log_date DATE NOT NULL,
    hours DECIMAL(4,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_user_date (user_id, log_date)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: calendar_events (Exams, Assignments, Deadlines)
-- ---------------------------------------------------------
CREATE TABLE calendar_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    event_type ENUM('Study','Exam','Assignment','Deadline','Other') DEFAULT 'Study',
    event_date DATE NOT NULL,
    event_time TIME DEFAULT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Helpful indexes
-- ---------------------------------------------------------
CREATE INDEX idx_tasks_user_date ON study_tasks(user_id, task_date);
CREATE INDEX idx_todos_user ON todos(user_id);
CREATE INDEX idx_goals_user ON goals(user_id);
CREATE INDEX idx_notes_user ON notes(user_id);
CREATE INDEX idx_events_user_date ON calendar_events(user_id, event_date);
