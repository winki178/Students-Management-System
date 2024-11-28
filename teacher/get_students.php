<?php
require_once '../includes/header.php';
require_once '../includes/auth.php';

if(!isset($_SESSION['teacher_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('未授权访问');
}

// 获取课程ID
$course_id = $_GET['course_id'] ?? null;

if (!$course_id) {
    header('HTTP/1.1 400 Bad Request');
    exit('缺少必要参数');
}

try {
    // 获取课程信息
    $stmt = $pdo->prepare("
        SELECT c.*, cl.id as class_id 
        FROM courses c
        JOIN classes cl ON c.class_id = cl.id
        WHERE c.id = ? AND c.teacher = (SELECT name FROM teachers WHERE id = ?)
    ");
    $stmt->execute([$course_id, $_SESSION['teacher_id']]);
    $course = $stmt->fetch();

    if (!$course) {
        header('HTTP/1.1 404 Not Found');
        exit('未找到课程信息');
    }

    // 获取班级学生列表
    $stmt = $pdo->prepare("
        SELECT s.*, g.score, g.id as grade_id
        FROM students s
        LEFT JOIN grades g ON s.id = g.student_id 
            AND g.subject = ?
        WHERE s.class_id = ?
        ORDER BY s.name
    ");
    $stmt->execute([$course['name'], $course['class_id']]);
    $students = $stmt->fetchAll();

    // 生成HTML
    if (empty($students)) {
        echo '<div class="alert alert-info">该班级暂无学生</div>';
    } else {
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>学号</th>';
        echo '<th>姓名</th>';
        echo '<th>成绩</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($students as $student) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($student['id']) . '</td>';
            echo '<td>' . htmlspecialchars($student['name']) . '</td>';
            echo '<td>';
            echo '<input type="hidden" name="students[]" value="' . $student['id'] . '">';
            echo '<input type="number" class="form-control" name="scores[]" ';
            echo 'value="' . ($student['score'] ?? '') . '" ';
            echo 'min="0" max="100" step="0.5">';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('服务器错误：' . $e->getMessage());
} 