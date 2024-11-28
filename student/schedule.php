<?php
$pageTitle = '课程表';
require_once '../includes/header.php';
require_once '../includes/auth.php';

if(!isset($_SESSION['student_id'])) {
    header('Location: ../login.php');
    exit();
}

$studentInfo = getStudentInfo($_SESSION['student_id']);

// 定义星期和时间段
$weekdays = ['周一', '周二', '周三', '周四', '周五', '周六', '周日'];
$timeSlots = [
    '1' => ['08:00', '09:40'],
    '2' => ['10:00', '11:40'],
    '3' => ['14:00', '15:40'],
    '4' => ['16:00', '17:40'],
    '5' => ['19:00', '20:40']
];

// 获取学生的课程表
function getStudentSchedule($student_id) {
    global $pdo;
    $schedule = [];
    try {
        $stmt = $pdo->prepare("
            SELECT s.*, c.name as course_name, c.teacher, s.classroom
            FROM schedule s
            JOIN courses c ON s.course_id = c.id
            JOIN students st ON c.class_id = st.class_id
            WHERE st.id = ?
            ORDER BY s.weekday, s.start_time
        ");
        $stmt->execute([$student_id]);
        $results = $stmt->fetchAll();
        
        // 按星期整理课程
        foreach ($results as $class) {
            $schedule[$class['weekday']][] = [
                'course_name' => $class['course_name'],
                'teacher' => $class['teacher'],
                'classroom' => $class['classroom'],
                'start_time' => $class['start_time'],
                'end_time' => $class['end_time']
            ];
        }
        return $schedule;
    } catch (PDOException $e) {
        error_log("Error getting student schedule: " . $e->getMessage());
        return [];
    }
}

$weekSchedule = getStudentSchedule($_SESSION['student_id']);
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/sidebar.php'; ?>

        <!-- 主要内容区域 -->
        <main role="main" class="col-md-10 ml-sm-auto px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">课程表</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group mr-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="fas fa-print mr-1"></i>打印课程表
                        </button>
                    </div>
                </div>
            </div>

            <!-- 课程表 -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead class="thead-light">
                                <tr>
                                    <th>时间</th>
                                    <?php foreach ($weekdays as $day): ?>
                                        <th><?php echo $day; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($timeSlots as $slot => $times): ?>
                                    <tr>
                                        <td class="align-middle">
                                            第<?php echo $slot; ?>节<br>
                                            <small class="text-muted">
                                                <?php echo $times[0] . '-' . $times[1]; ?>
                                            </small>
                                        </td>
                                        <?php for ($day = 1; $day <= 7; $day++): ?>
                                            <td class="align-middle">
                                                <?php
                                                $found = false;
                                                if (isset($weekSchedule[$day])) {
                                                    foreach ($weekSchedule[$day] as $class) {
                                                        if (substr($class['start_time'], 0, 5) === $times[0]) {
                                                            echo '<div class="course-item">';
                                                            echo '<strong>' . htmlspecialchars($class['course_name']) . '</strong><br>';
                                                            echo '<small>' . htmlspecialchars($class['teacher']) . '</small><br>';
                                                            echo '<small class="text-muted">' . htmlspecialchars($class['classroom']) . '</small>';
                                                            echo '</div>';
                                                            $found = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                                if (!$found) {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.course-item {
    padding: 5px;
    background-color: #f8f9fa;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.course-item:hover {
    background-color: #e9ecef;
    transform: scale(1.02);
}

@media print {
    .sidebar, .btn-toolbar {
        display: none !important;
    }
    main {
        margin: 0 !important;
    }
    .card {
        border: none !important;
    }
    .card-body {
        padding: 0 !important;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?> 