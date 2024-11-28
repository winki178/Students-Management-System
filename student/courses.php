<?php
require_once dirname(__DIR__) . '/includes/config.php';

// 获取个人成绩
function getPersonalGrades($student_id) {
    global $pdo;
    $query = "SELECT * FROM grades WHERE student_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$student_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?> 