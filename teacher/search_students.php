<?php
require_once '../includes/header.php';
require_once '../includes/auth.php';

if(!isset($_SESSION['teacher_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('未授权访问');
}

$search = $_GET['search'] ?? '';
$class_id = $_GET['class_id'] ?? '';

try {
    // 获取未分配到该班级的学生
    $stmt = $pdo->prepare("
        SELECT * FROM students 
        WHERE (class_id IS NULL OR class_id != ?) 
        AND (name LIKE ? OR id LIKE ?)
        ORDER BY name
    ");
    $searchTerm = "%$search%";
    $stmt->execute([$class_id, $searchTerm, $searchTerm]);
    $students = $stmt->fetchAll();
    
    // 生成选项HTML
    echo '<option value="">请选择学生</option>';
    foreach ($students as $student) {
        echo '<option value="' . $student['id'] . '">';
        echo htmlspecialchars($student['name']) . ' (学号: ' . $student['id'] . ')';
        echo '</option>';
    }
    
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('搜索失败：' . $e->getMessage());
} 