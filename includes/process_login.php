<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_type = $_POST['login_type'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        if ($login_type === 'student') {
            $name = trim($_POST['name'] ?? '');
            if (loginStudent($name, $password)) {
                header('Location: ../student/dashboard.php');
                exit();
            } else {
                header('Location: ../login.php?error=1&type=student');
                exit();
            }
        } elseif ($login_type === 'teacher') {
            $username = trim($_POST['username'] ?? '');
            if (loginTeacher($username, $password)) {
                header('Location: ../teacher/dashboard.php');
                exit();
            } else {
                header('Location: ../login.php?error=1&type=teacher');
                exit();
            }
        }
    } catch (Exception $e) {
        error_log("Login process error: " . $e->getMessage());
        header('Location: ../login.php?error=system&type=' . $login_type);
        exit();
    }
} 