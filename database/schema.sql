-- ============================================================
-- University Seminar Management System — Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS seminar_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE seminar_db;

-- Users (Admin + Teachers)
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)  NOT NULL,
    email         VARCHAR(100)  NOT NULL UNIQUE,
    password      VARCHAR(255)  NOT NULL,
    role          ENUM('admin','teacher') NOT NULL DEFAULT 'teacher',
    phone         VARCHAR(25),
    department    VARCHAR(120),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Seminars
CREATE TABLE IF NOT EXISTS seminars (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title          VARCHAR(200)  NOT NULL,
    description    TEXT,
    speaker        VARCHAR(150),
    seminar_date   DATE          NOT NULL,
    seminar_time   TIME          NOT NULL,
    venue          VARCHAR(200),
    capacity       INT UNSIGNED  DEFAULT 50,
    status         ENUM('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
    created_by     INT UNSIGNED,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Seminar ↔ Teacher assignments
CREATE TABLE IF NOT EXISTS seminar_teachers (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seminar_id  INT UNSIGNED NOT NULL,
    teacher_id  INT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_seminar_teacher (seminar_id, teacher_id),
    FOREIGN KEY (seminar_id)  REFERENCES seminars(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id)  REFERENCES users(id)    ON DELETE CASCADE
);

-- Student Registrations (public, no login)
CREATE TABLE IF NOT EXISTS registrations (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seminar_id        INT UNSIGNED  NOT NULL,
    student_name      VARCHAR(100)  NOT NULL,
    student_email     VARCHAR(100)  NOT NULL,
    student_phone     VARCHAR(25),
    student_roll      VARCHAR(50),
    department        VARCHAR(120),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seminar_id) REFERENCES seminars(id) ON DELETE CASCADE
);

-- Attendance
CREATE TABLE IF NOT EXISTS attendance (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    registration_id INT UNSIGNED NOT NULL,
    seminar_id      INT UNSIGNED NOT NULL,
    status          ENUM('present','absent') NOT NULL DEFAULT 'absent',
    marked_by       INT UNSIGNED,
    marked_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_attendance (registration_id, seminar_id),
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    FOREIGN KEY (seminar_id)      REFERENCES seminars(id)      ON DELETE CASCADE,
    FOREIGN KEY (marked_by)       REFERENCES users(id)         ON DELETE SET NULL
);
