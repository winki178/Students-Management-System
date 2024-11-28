<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'PHP教学班级管理系统'; ?></title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <?php if(isset($isDashboard)): ?>
    <link href="/assets/css/dashboard.css" rel="stylesheet">
    <?php endif; ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php 
                if (isset($_SESSION['student_id'])) {
                    echo '/student/dashboard.php';
                } elseif (isset($_SESSION['teacher_id'])) {
                    echo '/teacher/dashboard.php';
                } else {
                    echo '/login.php';
                }
            ?>">
                <i class="fas fa-school mr-2"></i>教学班级管理系统
            </a>
            <?php if(isset($_SESSION['student_id']) || isset($_SESSION['teacher_id'])): ?>
            <ul class="navbar-nav ml-auto">
                <?php if(isset($_SESSION['teacher_id'])): ?>
                <li class="nav-item mr-3">
                    <span class="nav-link">
                        <i class="fas fa-user mr-1"></i>
                        <?php echo htmlspecialchars($teacherInfo['name']); ?>
                    </span>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="/includes/logout.php">
                        <i class="fas fa-sign-out-alt mr-1"></i>退出登录
                    </a>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </nav>
</body>
</html> 