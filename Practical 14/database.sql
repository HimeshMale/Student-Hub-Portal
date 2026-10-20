
CREATE DATABASE IF NOT EXISTS student_db;
USE student_db;

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(30) UNIQUE NOT NULL
);

INSERT IGNORE INTO roles (id, role_name) VALUES
(1, 'admin'),
(2, 'faculty'),
(3, 'student');

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(100) NOT NULL,
    event_date DATE
);

CREATE TABLE IF NOT EXISTS menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_name VARCHAR(50) NOT NULL,
    menu_url VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS role_menus (
    role_id INT NOT NULL,
    menu_id INT NOT NULL,
    PRIMARY KEY (role_id, menu_id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (menu_id) REFERENCES menus(id)
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    details VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
);

INSERT INTO menus (menu_name, menu_url)
SELECT 'Dashboard', 'index.php'
WHERE NOT EXISTS (
    SELECT 1 FROM menus WHERE menu_url = 'index.php'
);

INSERT INTO menus (menu_name, menu_url)
SELECT 'Student Management', 'students.php'
WHERE NOT EXISTS (
    SELECT 1 FROM menus WHERE menu_url = 'students.php'
);

INSERT INTO menus (menu_name, menu_url)
SELECT 'Events', 'events.php'
WHERE NOT EXISTS (
    SELECT 1 FROM menus WHERE menu_url = 'events.php'
);

INSERT INTO menus (menu_name, menu_url)
SELECT 'Audit Log', 'audit.php'
WHERE NOT EXISTS (
    SELECT 1 FROM menus WHERE menu_url = 'audit.php'
);

INSERT IGNORE INTO role_menus (role_id, menu_id)
SELECT 1, id FROM menus;

INSERT IGNORE INTO role_menus (role_id, menu_id)
SELECT 2, id FROM menus
WHERE menu_url IN ('students.php', 'events.php');

INSERT IGNORE INTO role_menus (role_id, menu_id)
SELECT 3, id FROM menus
WHERE menu_url = 'index.php';

INSERT IGNORE INTO events (event_name, event_date) VALUES
('Technical Festival', '2026-11-15'),
('Sports Day', '2026-12-10');
