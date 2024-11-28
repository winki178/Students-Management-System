<?php
$pageTitle = '违规信息';
require_once '../includes/header.php';
require_once '../includes/auth.php';

if(!isset($_SESSION['student_id'])) {
    header('Location: ../login.php');
    exit();
}

$studentInfo = getStudentInfo($_SESSION['student_id']);

// 获取学生的违规记录
function getStudentViolations($student_id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM violations 
            WHERE student_id = ? 
            ORDER BY violation_date DESC
        ");
        $stmt->execute([$student_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting violations: " . $e->getMessage());
        return [];
    }
}

// 获取违规类型统计
function getViolationStats($violations) {
    $stats = [];
    foreach ($violations as $violation) {
        $type = $violation['violation_type'];
        if (!isset($stats[$type])) {
            $stats[$type] = 0;
        }
        $stats[$type]++;
    }
    return $stats;
}

// 获取违规记录和统计
$violations = getStudentViolations($_SESSION['student_id']);
$violationStats = getViolationStats($violations);
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/sidebar.php'; ?>

        <!-- 主要内容区域 -->
        <main role="main" class="col-md-10 ml-sm-auto px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">违规信息查看</h1>
            </div>

            <!-- 违规统计卡片 -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5 class="card-title">总违规次数</h5>
                            <p class="card-text display-4"><?php echo count($violations); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">未处理违规</h5>
                            <p class="card-text display-4">
                                <?php 
                                echo count(array_filter($violations, function($v) {
                                    return $v['status'] === '未处理';
                                }));
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">违规类型数</h5>
                            <p class="card-text display-4"><?php echo count($violationStats); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 违规记录表格 -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>违规日期</th>
                                    <th>违规类型</th>
                                    <th>违规描述</th>
                                    <th>状态</th>
                                    <th>记录时间</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($violations)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">暂无违规记录</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($violations as $violation): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($violation['violation_date']); ?></td>
                                            <td>
                                                <span class="badge badge-pill badge-danger">
                                                    <?php echo htmlspecialchars($violation['violation_type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($violation['description']); ?></td>
                                            <td>
                                                <?php if($violation['status'] === '未处理'): ?>
                                                    <span class="badge badge-warning">未处理</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">已处理</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($violation['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 违规类型统计图表 -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">违规类型统计</h5>
                    <canvas id="violationChart"></canvas>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- 添加Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// 准备数据
const violationTypes = <?php echo json_encode(array_keys($violationStats)); ?>;
const violationCounts = <?php echo json_encode(array_values($violationStats)); ?>;

// 创建图表
const ctx = document.getElementById('violationChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: violationTypes,
        datasets: [{
            label: '违规次数',
            data: violationCounts,
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
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

.badge-pill {
    padding-right: .6em;
    padding-left: .6em;
}
</style>

<?php require_once '../includes/footer.php'; ?> 