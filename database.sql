-- 创建数据库
CREATE DATABASE IF NOT EXISTS school_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_management;

-- 创建班级表
CREATE TABLE IF NOT EXISTS classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建学生表
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    class_id INT,
    birth_date DATE,
    enrollment_date DATE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建成绩表
CREATE TABLE IF NOT EXISTS grades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    subject VARCHAR(50) NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建课程表
CREATE TABLE IF NOT EXISTS courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    teacher VARCHAR(50) NOT NULL,
    class_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建课程安排表
CREATE TABLE IF NOT EXISTS schedule (
    id INT PRIMARY KEY AUTO_INCREMENT,
    course_id INT,
    class_id INT,
    weekday TINYINT NOT NULL COMMENT '1-7表示周一到周日',
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    classroom VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (class_id) REFERENCES classes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建违规记录表
CREATE TABLE IF NOT EXISTS violations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    violation_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    violation_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT '未处理',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建教师表
CREATE TABLE IF NOT EXISTS teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 在原有表之后添加考勤表
CREATE TABLE IF NOT EXISTS attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    course_id INT,
    date DATE NOT NULL,
    status ENUM('出勤', '缺勤', '请假') NOT NULL DEFAULT '出勤',
    remark TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY `unique_attendance` (`student_id`, `course_id`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入班级数据
INSERT INTO classes (name) VALUES 
('PHP01班'),
('PHP02班'),
('PHP03班'),
('Web01班'),
('Web02班');

-- 插入学生数据（使用正确的班级ID）
INSERT INTO students (name, class_id, birth_date, enrollment_date, password) VALUES
('张三', 1, '2000-01-01', '2023-09-01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('李四', 1, '2000-02-02', '2023-09-01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('王五', 2, '2000-03-03', '2023-09-01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('赵六', 2, '2000-04-04', '2023-09-01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('孙七', 3, '2000-05-05', '2023-09-01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('周八', 3, '2000-06-06', '2023-09-01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 插入教师数据
INSERT INTO teachers (name, username, password, email, phone) VALUES
('张老师', 'teacher1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher1@example.com', '13800138001'),
('李老师', 'teacher2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher2@example.com', '13800138002');

-- 插入课程数据（每个教师教授多个班级的课程）
INSERT INTO courses (name, teacher, class_id) VALUES 
-- 张老师的课程
('PHP基础', '张老师', 1),
('PHP基础', '张老师', 2),
('PHP基础', '张老师', 3),
-- 李老师的课程
('Web开发', '李老师', 1),
('Web开发', '李老师', 2),
('Web开发', '李老师', 3),
('JavaScript', '李老师', 1),
('JavaScript', '李老师', 2);

-- 插入成绩数据（使用正确的学生ID）
INSERT INTO grades (student_id, subject, score) VALUES
-- PHP基础成绩
(1, 'PHP基础', 85),
(2, 'PHP基础', 92),
(3, 'PHP基础', 78),
(4, 'PHP基础', 88),
(5, 'PHP基础', 95),
(6, 'PHP基础', 82),
-- Web开发成绩
(1, 'Web开发', 88),
(2, 'Web开发', 90),
(3, 'Web开发', 85),
(4, 'Web开发', 87),
(5, 'Web开发', 92),
(6, 'Web开发', 89),
-- JavaScript成绩
(1, 'JavaScript', 86),
(2, 'JavaScript', 89),
(3, 'JavaScript', 92),
(4, 'JavaScript', 85);

-- 插入考勤记录（使用正确的学生ID和课程ID）
INSERT INTO attendance (student_id, course_id, date, status, remark) VALUES
-- PHP基础考勤（张老师）
(1, 1, CURDATE(), '出勤', NULL),
(2, 1, CURDATE(), '出勤', NULL),
(3, 2, CURDATE(), '请假', '家中有事'),
(4, 2, CURDATE(), '缺勤', '无故缺勤'),
(5, 3, CURDATE(), '出勤', NULL),
(6, 3, CURDATE(), '出勤', NULL),
-- Web开发考勤（李老师）
(1, 4, CURDATE(), '出勤', NULL),
(2, 4, CURDATE(), '出勤', NULL),
(3, 5, CURDATE(), '出勤', NULL),
(4, 5, CURDATE(), '请假', '身体不适'),
(5, 6, CURDATE(), '出勤', NULL),
(6, 6, CURDATE(), '请假', '参加比赛'),
-- JavaScript考勤（李老师）
(1, 7, CURDATE(), '出勤', NULL),
(2, 7, CURDATE(), '出勤', NULL),
(3, 8, CURDATE(), '出勤', NULL),
(4, 8, CURDATE(), '出勤', NULL);

-- 插入课程安排数据
INSERT INTO schedule (course_id, class_id, weekday, start_time, end_time, classroom) VALUES 
-- PHP基础课程安排
(1, 1, 1, '08:00:00', '09:40:00', '教室101'),
(1, 1, 3, '10:00:00', '11:40:00', '教室101'),
(2, 2, 2, '10:00:00', '11:40:00', '教室102'),
(2, 2, 4, '14:00:00', '15:40:00', '教室102'),
(3, 3, 3, '14:00:00', '15:40:00', '教室103'),
(3, 3, 5, '08:00:00', '09:40:00', '教室103'),

-- Web开发课程安排
(4, 1, 2, '08:00:00', '09:40:00', '教室301'),
(4, 1, 4, '10:00:00', '11:40:00', '教室301'),
(5, 2, 1, '14:00:00', '15:40:00', '教室302'),
(5, 2, 3, '16:00:00', '17:40:00', '教室302'),
(6, 3, 2, '14:00:00', '15:40:00', '教室303'),
(6, 3, 4, '16:00:00', '17:40:00', '教室303'),

-- JavaScript课程安排
(7, 1, 3, '14:00:00', '15:40:00', '教室401'),
(7, 1, 5, '10:00:00', '11:40:00', '教室401'),
(8, 2, 2, '16:00:00', '17:40:00', '教室402'),
(8, 2, 4, '14:00:00', '15:40:00', '教室402');

-- 创建索引
CREATE INDEX idx_student_name ON students(name);
CREATE INDEX idx_student_class ON students(class_id);
CREATE INDEX idx_grade_student ON grades(student_id);
CREATE INDEX idx_grade_subject ON grades(subject);
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_attendance_status ON attendance(status);