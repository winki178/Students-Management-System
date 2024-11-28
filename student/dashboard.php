<?php
$pageTitle = '学生仪表板';
require_once '../includes/header.php';
require_once '../includes/auth.php';
require_once '../student/courses.php';

if(!isset($_SESSION['student_id'])) {
    header('Location: ../login.php');
    exit();
}

// 获取学生信息
$studentInfo = getStudentInfo($_SESSION['student_id']);
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/sidebar.php'; ?>
        
        <!-- 主要内容区域 -->
        <main role="main" class="col-md-10 ml-sm-auto px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">欢迎回来，<?php echo htmlspecialchars($studentInfo['name']); ?></h1>
            </div>

            <!-- 功能模块卡片 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-user fa-3x mb-3 text-primary"></i>
                            <h5 class="card-title">个人信息</h5>
                            <p class="card-text">查看和修改个人资料</p>
                            <a href="profile.php" class="btn btn-primary">查看信息</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3 text-danger"></i>
                            <h5 class="card-title">违规信息</h5>
                            <p class="card-text">查看个人违规记录</p>
                            <a href="violations.php" class="btn btn-danger">查看违规</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-bar fa-3x mb-3 text-info"></i>
                            <h5 class="card-title">成绩查询</h5>
                            <p class="card-text">查看个人成绩和分析</p>
                            <a href="grades.php" class="btn btn-info">查看成绩</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-alt fa-3x mb-3 text-warning"></i>
                            <h5 class="card-title">课程表</h5>
                            <p class="card-text">查看每周课程安排</p>
                            <a href="schedule.php" class="btn btn-warning">查看课表</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 最近活动 -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-bell mr-2"></i>最近活动
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">新作业发布</h5>
                                        <small>3 天前</small>
                                    </div>
                                    <p class="mb-1">PHP基础课程作业已发布，请及时完成。</p>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">考试通知</h5>
                                        <small>5 天前</small>
                                    </div>
                                    <p class="mb-1">下周三将进行MySQL数据库期中考试。</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 个人信息卡片 -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-user-circle mr-2"></i>个人信息
                        </div>
                        <div class="card-body">
                            <p><strong>学号：</strong> <?php echo htmlspecialchars($studentInfo['id']); ?></p>
                            <p><strong>班级：</strong> <?php echo htmlspecialchars($studentInfo['class_name']); ?></p>
                            <p><strong>入学时间：</strong> <?php echo htmlspecialchars($studentInfo['enrollment_date']); ?></p>
                            <a href="profile.php" class="btn btn-outline-primary btn-sm">编辑资料</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 