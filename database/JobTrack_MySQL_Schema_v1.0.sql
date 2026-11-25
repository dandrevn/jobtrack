-- JobTrack database schema v1.0
-- MySQL / InnoDB
-- Review and run in a development database before production use.

CREATE DATABASE jobtrack_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE jobtrack_db;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE job_applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    job_title VARCHAR(150) NOT NULL,
    job_type VARCHAR(50) NULL,
    work_setup VARCHAR(30) NULL,
    location VARCHAR(150) NULL,
    salary_min DECIMAL(12,2) NULL,
    salary_max DECIMAL(12,2) NULL,
    salary_currency VARCHAR(3) DEFAULT 'PHP',
    job_url VARCHAR(2048) NULL,
    source VARCHAR(100) NULL,
    date_applied DATE NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'interested',
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_application_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_user_date (user_id, date_applied)
) ENGINE=InnoDB;

CREATE TABLE interviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id BIGINT UNSIGNED NOT NULL,
    interview_type VARCHAR(50) NOT NULL,
    scheduled_at DATETIME NOT NULL,
    location_or_link VARCHAR(500) NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'scheduled',
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_interview_application
        FOREIGN KEY (application_id) REFERENCES job_applications(id)
        ON DELETE CASCADE,
    INDEX idx_interview_schedule (scheduled_at)
) ENGINE=InnoDB;

CREATE TABLE application_status_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id BIGINT UNSIGNED NOT NULL,
    old_status VARCHAR(30) NULL,
    new_status VARCHAR(30) NOT NULL,
    changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes VARCHAR(500) NULL,
    CONSTRAINT fk_history_application
        FOREIGN KEY (application_id) REFERENCES job_applications(id)
        ON DELETE CASCADE,
    INDEX idx_history_application_date (application_id, changed_at)
) ENGINE=InnoDB;
