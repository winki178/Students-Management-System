<?php
require_once 'config.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $class_id = $_POST['class_id'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // 检查用户名是否已存在
        if (isStudentExists($name)) {
            header('Location: ../register.php?error=exists');
            exit();
        }

        // 密码加密
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // 插入新学生记录
        $stmt = $pdo->prepare("
            INSERT INTO students (name, class_id, birth_date, password, enrollment_date) 
            VALUES (?, ?, ?, ?, CURDATE())
        ");
        
        $stmt->execute([$name, $class_id, $birth_date, $hashed_password]);
        
        header('Location: ../login.php?registered=1');
        exit();
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        header('Location: ../register.php?error=1');
        exit();
    }
} 