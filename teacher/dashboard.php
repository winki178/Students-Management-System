<?php
$pageTitle = '教师仪表板';
require_once '../includes/header.php';
require_once '../includes/auth.php';

if(!isset($_SESSION['teacher_id'])) {
    header('Location: ../login.php');
    exit();
}

$teacherInfo = getTeacherInfo($_SESSION['teacher_id']);
$stats = getTeacherStats($_SESSION['teacher_id']);
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/teacher_sidebar.php'; ?>
        
        <!-- 主要内容区域 -->
        <main role="main" class="col-md-10 ml-sm-auto px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">欢迎回来，<?php echo htmlspecialchars($teacherInfo['name']); ?></h1>
            </div>

            <!-- 统计卡片 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">学生总数</h5>
                            <p class="card-text display-4"><?php echo $stats['student_count']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">班级数量</h5>
                            <p class="card-text display-4"><?php echo $stats['class_count']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">课程信息</h5>
                            <p class="card-text display-4"><?php echo $stats['course_count']; ?></p>
                            <small>教授课程数量</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">本周课程</h5>
                            <p class="card-text display-4"><?php echo $stats['weekly_courses']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 快捷操作 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-tasks mr-2"></i>快捷操作
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <a href="attendance.php?action=new" class="btn btn-outline-primary btn-block">
                                        <i class="fas fa-clipboard-check mr-2"></i>登记考勤
                                    </a>
                                </div>
                                <div class="col-6 mb-3">
                                    <a href="manage_grades.php?action=new" class="btn btn-outline-success btn-block">
                                        <i class="fas fa-plus-circle mr-2"></i>录入成绩
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="manage_students.php" class="btn btn-outline-info btn-block">
                                        <i class="fas fa-users mr-2"></i>学生管理
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="manage_classes.php" class="btn btn-outline-warning btn-block">
                                        <i class="fas fa-chalkboard mr-2"></i>班级管理
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 最近活动 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-history mr-2"></i>最近活动
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <?php foreach($stats['recent_activities'] as $activity): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($activity['title']); ?></h6>
                                        <small><?php echo htmlspecialchars($activity['time']); ?></small>
                                    </div>
                                    <p class="mb-1 text-muted small"><?php echo htmlspecialchars($activity['description']); ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 