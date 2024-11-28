<?php
require_once 'config.php';

// 学生登录
function loginStudent($name, $password) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id, name, password FROM students WHERE name = ?");
        $stmt->execute([$name]);
        $student = $stmt->fetch();

        if ($student && password_verify($password, $student['password'])) {
            session_start();
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_name'] = $student['name'];
            $_SESSION['user_type'] = 'student';
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

// 教师登录
function loginTeacher($username, $password) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id, name, password FROM teachers WHERE username = ?");
        $stmt->execute([$username]);
        $teacher = $stmt->fetch();

        if ($teacher && password_verify($password, $teacher['password'])) {
            session_start();
            $_SESSION['teacher_id'] = $teacher['id'];
            $_SESSION['teacher_name'] = $teacher['name'];
            $_SESSION['user_type'] = 'teacher';
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

// 获取教师信息
function getTeacherInfo($teacher_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT id, name, username, email, phone, created_at 
            FROM teachers 
            WHERE id = ?
        ");
        $stmt->execute([$teacher_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting teacher info: " . $e->getMessage());
        return false;
    }
}

// 获取教师统计信息
function getTeacherStats($teacher_id) {
    global $pdo;
    try {
        // 获取教师所教班级的学生总数
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT s.id) as student_count
            FROM students s
            JOIN classes c ON s.class_id = c.id
            JOIN courses co ON c.id = co.class_id
            WHERE co.teacher = (SELECT name FROM teachers WHERE id = ?)
        ");
        $stmt->execute([$teacher_id]);
        $student_count = $stmt->fetchColumn();

        // 获取教师所教的班级数量
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT c.id) as class_count
            FROM classes c
            JOIN courses co ON c.id = co.class_id
            WHERE co.teacher = (SELECT name FROM teachers WHERE id = ?)
        ");
        $stmt->execute([$teacher_id]);
        $class_count = $stmt->fetchColumn();

        // 获取教师的课程数量
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as course_count
            FROM courses
            WHERE teacher = (SELECT name FROM teachers WHERE id = ?)
        ");
        $stmt->execute([$teacher_id]);
        $course_count = $stmt->fetchColumn();

        // 获取本周课程数
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as weekly_courses
            FROM schedule s
            JOIN courses c ON s.course_id = c.id
            WHERE c.teacher = (SELECT name FROM teachers WHERE id = ?)
            AND WEEK(CURDATE()) = WEEK(CURDATE())
        ");
        $stmt->execute([$teacher_id]);
        $weekly_courses = $stmt->fetchColumn();

        // 获取最近活动
        $stmt = $pdo->prepare("
            SELECT 
                CASE 
                    WHEN type = 'grade' THEN '成绩录入'
                    WHEN type = 'attendance' THEN '考勤记录'
                    ELSE '其他活动'
                END as title,
                description,
                created_at as time
            FROM (
                -- 成绩录入记录
                SELECT 'grade' as type, 
                       CONCAT('录入了 ', s.name, ' 的 ', g.subject, ' 成绩') as description,
                       g.created_at
                FROM grades g
                JOIN students s ON g.student_id = s.id
                JOIN classes c ON s.class_id = c.id
                JOIN courses co ON c.id = co.class_id
                WHERE co.teacher = (SELECT name FROM teachers WHERE id = ?)
                
                UNION ALL
                
                -- 考勤记录
                SELECT 'attendance' as type,
                       CONCAT('记录了 ', s.name, ' 的考勤状态') as description,
                       a.created_at
                FROM attendance a
                JOIN students s ON a.student_id = s.id
                JOIN courses c ON a.course_id = c.id
                WHERE c.teacher = (SELECT name FROM teachers WHERE id = ?)
            ) activities
            ORDER BY time DESC
            LIMIT 5
        ");
        $stmt->execute([$teacher_id, $teacher_id]);
        $recent_activities = $stmt->fetchAll();

        return [
            'student_count' => $student_count,
            'class_count' => $class_count,
            'course_count' => $course_count,
            'weekly_courses' => $weekly_courses,
            'recent_activities' => $recent_activities
        ];
    } catch (PDOException $e) {
        error_log("Error getting teacher stats: " . $e->getMessage());
        return [
            'student_count' => 0,
            'class_count' => 0,
            'course_count' => 0,
            'weekly_courses' => 0,
            'recent_activities' => []
        ];
    }
}

// 获取教师所教班级的学生列表
function getTeacherStudents($teacher_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT s.*, c.name as class_name
            FROM students s
            JOIN classes c ON s.class_id = c.id
            JOIN courses co ON c.id = co.class_id
            WHERE co.teacher = (SELECT name FROM teachers WHERE id = ?)
            ORDER BY c.name, s.name
        ");
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting teacher students: " . $e->getMessage());
        return [];
    }
}

// 检查学生是否属于教师的班级
function isStudentInTeacherClass($teacher_id, $student_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM students s
            JOIN classes c ON s.class_id = c.id
            JOIN courses co ON c.id = co.class_id
            WHERE co.teacher = (SELECT name FROM teachers WHERE id = ?)
            AND s.id = ?
        ");
        $stmt->execute([$teacher_id, $student_id]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error checking student in teacher class: " . $e->getMessage());
        return false;
    }
}

// 检查学生是否已存在
function isStudentExists($name) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE name = ?");
    $stmt->execute([$name]);
    return $stmt->fetchColumn() > 0;
}

// 获取学生信息
function getStudentInfo($student_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT s.*, c.name as class_name 
        FROM students s 
        LEFT JOIN classes c ON s.class_id = c.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$student_id]);
    return $stmt->fetch();
}

// 获取教师所教的班级列表
function getTeacherClasses($teacher_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT c.* 
            FROM classes c
            JOIN courses co ON c.id = co.class_id
            WHERE co.teacher = (SELECT name FROM teachers WHERE id = ?)
            ORDER BY c.name
        ");
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting teacher classes: " . $e->getMessage());
        return [];
    }
}

// 获取教师的课程列表
function getTeacherCourses($teacher_id, $class_id = null) {
    global $pdo;
    try {
        $query = "
            SELECT DISTINCT c.*, cl.name as class_name
            FROM courses c
            JOIN classes cl ON c.class_id = cl.id
            WHERE c.teacher = (SELECT name FROM teachers WHERE id = ?)
            " . ($class_id ? "AND c.class_id = ?" : "") . "
            ORDER BY c.name
        ";
        
        $params = [$teacher_id];
        if ($class_id) {
            $params[] = $class_id;
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting teacher courses: " . $e->getMessage());
        return [];
    }
}

// 获取教师的所有课程（不区分班级）
function getTeacherAllCourses($teacher_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT name, 
                   COUNT(DISTINCT class_id) as class_count,
                   GROUP_CONCAT(DISTINCT class_id) as class_ids
            FROM courses 
            WHERE teacher = (SELECT name FROM teachers WHERE id = ?)
            GROUP BY name
            ORDER BY name
        ");
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting teacher courses: " . $e->getMessage());
        return [];
    }
}

// 获取课程的所有班级
function getCourseClasses($course_name, $teacher_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT c.*, 
                (SELECT COUNT(*) FROM students WHERE class_id = c.id) as student_count,
                (SELECT COUNT(*) FROM attendance a 
                    JOIN courses co ON a.course_id = co.id 
                    WHERE co.class_id = c.id 
                    AND co.name = ? 
                    AND DATE(a.date) = CURDATE()) as today_attendance
            FROM classes c
            JOIN courses co ON c.id = co.class_id
            WHERE co.name = ? 
            AND co.teacher = (SELECT name FROM teachers WHERE id = ?)
            ORDER BY c.name
        ");
        $stmt->execute([$course_name, $course_name, $teacher_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting course classes: " . $e->getMessage());
        return [];
    }
}
?> 