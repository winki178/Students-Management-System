<?php
$pageTitle = '成绩查询';
require_once '../includes/header.php';
require_once '../includes/auth.php';
require_once '../student/courses.php';

if(!isset($_SESSION['student_id'])) {
    header('Location: ../login.php');
    exit();
}

$studentInfo = getStudentInfo($_SESSION['student_id']);
$grades = getPersonalGrades($_SESSION['student_id']);

// 计算平均分
$totalScore = 0;
$count = count($grades);
foreach($grades as $grade) {
    $totalScore += $grade['score'];
}
$averageScore = $count > 0 ? round($totalScore / $count, 2) : 0;
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/sidebar.php'; ?>

        <!-- 主要内容区域 -->
        <main role="main" class="col-md-10 ml-sm-auto px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">成绩查询</h1>
            </div>

            <!-- 成绩概览卡片 -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">平均分</h5>
                            <p class="card-text display-4"><?php echo $averageScore; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">课程数量</h5>
                            <p class="card-text display-4"><?php echo $count; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">最高分</h5>
                            <p class="card-text display-4">
                                <?php 
                                echo $count > 0 ? max(array_column($grades, 'score')) : 0;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 成绩详细表格 -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>科目</th>
                                    <th>成绩</th>
                                    <th>状态</th>
                                    <th>排名</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($grades as $grade): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($grade['subject']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['score']); ?></td>
                                        <td>
                                            <?php if($grade['score'] >= 90): ?>
                                                <span class="badge badge-success">优秀</span>
                                            <?php elseif($grade['score'] >= 60): ?>
                                                <span class="badge badge-info">及格</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">不及格</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">待更新</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 成绩分析图表 -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">成绩分布图</h5>
                    <canvas id="gradesChart"></canvas>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- 添加Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// 准备数据
const subjects = <?php echo json_encode(array_column($grades, 'subject')); ?>;
const scores = <?php echo json_encode(array_column($grades, 'score')); ?>;

// 创建图表
const ctx = document.getElementById('gradesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: subjects,
        datasets: [{
            label: '成绩',
            data: scores,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
</script>

<style>
.sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}

.sidebar-sticky {
    position: relative;
    top: 0;
    height: calc(100vh - 48px);
    padding-top: .5rem;
    overflow-x: hidden;
    overflow-y: auto;
}

.nav-link {
    font-weight: 500;
    color: #333;
}

.nav-link.active {
    color: #007bff;
}

main {
    margin-top: 48px;
}

.card {
    margin-bottom: 1rem;
    transition: transform .2s;
}

.card:hover {
    transform: translateY(-5px);
}
</style>

<?php require_once '../includes/footer.php'; ?> 