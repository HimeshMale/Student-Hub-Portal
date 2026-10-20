CREATE DATABASE IF NOT EXISTS student_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_db;

DROP TABLE IF EXISTS students;
CREATE TABLE students (
    student_id VARCHAR(20)  NOT NULL PRIMARY KEY,   -- e.g. 25DCE011
    name       VARCHAR(100) NOT NULL,
    course     VARCHAR(100) NOT NULL
);

INSERT INTO students (student_id, name, course) VALUES
('25DCE011', 'Rudra', 'Computer Engineering'),
('25DCE012', 'Riya',  'Computer Engineering'),
('25DCS004', 'Neha',  'Computer Science'),
('25DIT005', 'Amit',  'Information Technology');