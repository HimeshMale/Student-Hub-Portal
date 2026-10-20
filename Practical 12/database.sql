CREATE DATABASE IF NOT EXISTS event_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE event_db;

DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE events (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    description TEXT,
    event_date  DATE         NOT NULL,
    venue       VARCHAR(150) NOT NULL,
    status      ENUM('Upcoming','Ongoing','Completed','Cancelled') NOT NULL DEFAULT 'Upcoming',
    poster      VARCHAR(100) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin login:  admin / admin123
INSERT INTO admins (username, password_hash) VALUES ('admin', '$2y$10$L2DbTOfAsdVDSUPGkr1LG.OP7UVyQ6XVReIU2FIxSEr9mDsgTkQPW');

INSERT INTO events (title, description, event_date, venue, status) VALUES
('Tech Fest 2026', 'Annual technical festival with coding contests.', '2026-12-05', 'Main Auditorium', 'Upcoming'),
('Alumni Meet',    'Meet and greet with past students.',              '2026-09-10', 'Seminar Hall',    'Completed');