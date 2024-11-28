<?php
$pageTitle = '成绩管理';
require_once '../includes/header.php';
require_once '../includes/auth.php';

if(!isset($_SESSION['teacher_id'])) {
    header('Location: ../login.php');
    exit();
}

$teacherInfo = getTeacherInfo($_SESSION['teacher_id']);

// 获取班级学生的成绩
function getClassGrades($class_id, $course_id = null) {
    global $pdo;
    $query = "
        SELECT s.id, s.name, g.score, g.id as grade_id
        FROM students s
        LEFT JOIN grades g ON s.id = g.student_id 
        AND g.subject = (SELECT name FROM courses WHERE id = ?)
        WHERE s.class_id = ?
        ORDER BY s.name
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$course_id, $class_id]);
    return $stmt->fetchAll();
}

// 处理成绩录入
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        $course_id = $_POST['course_id'];
        $course = $pdo->query("SELECT name FROM courses WHERE id = " . $course_id)->fetch();
        $students = $_POST['students'];
        $scores = $_POST['scores'];
        
        foreach ($students as $index => $student_id) {
            // 检查学生是否属于教师的班级
            if (!isStudentInTeacherClass($_SESSION['teacher_id'], $student_id)) {
                throw new Exception("无权操作此学生的成绩");
            }

            if (isset($scores[$index]) && $scores[$index] !== '') {
                // 检查是否已存在成绩
                $stmt = $pdo->prepare("SELECT id FROM grades WHERE student_id = ? AND subject = ?");
                $stmt->execute([$student_id, $course['name']]);
                $exists = $stmt->fetch();
                
                if ($exists) {
                    // 更新成绩
                    $stmt = $pdo->prepare("UPDATE grades SET score = ? WHERE id = ?");
                    $stmt->execute([$scores[$index], $exists['id']]);
                } else {
                    // 新增成绩
                    $stmt = $pdo->prepare("INSERT INTO grades (student_id, subject, score) VALUES (?, ?, ?)");
                    $stmt->execute([$student_id, $course['name'], $scores[$index]]);
                }
            }
        }
        
        $pdo->commit();
        $success_message = "成绩保存成功！";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "操作失败：" . $e->getMessage();
    }
}

// 使用 auth.php 中的函数获取数据
$classes = getTeacherClasses($_SESSION['teacher_id']);
$selected_class = $_GET['class_id'] ?? ($classes[0]['id'] ?? null);
$courses = $selected_class ? getTeacherCourses($_SESSION['teacher_id'], $selected_class) : [];
$selected_course = $_GET['course_id'] ?? ($courses[0]['id'] ?? null);
$students = $selected_class && $selected_course ? getClassGrades($selected_class, $selected_course) : [];

// 计算统计信息
$stats = [
    'total' => count($students),
    'submitted' => count(array_filter($students, function($s) { return $s['score'] !== null; })),
    'average' => 0,
    'max' => 0,
    'min' => 100,
    'pass_rate' => 0
];

if ($stats['submitted'] > 0) {
    $scores = array_filter(array_column($students, 'score'));
    $stats['average'] = round(array_sum($scores) / count($scores), 1);
    $stats['max'] = max($scores);
    $stats['min'] = min($scores);
    $stats['pass_rate'] = round(count(array_filter($scores, function($s) { return $s >= 60; })) / count($scores) * 100, 1);
}
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/teacher_sidebar.php'; ?>

        <!-- 主要内容区域 -->
        <main role="main" class="col-md-10 ml-sm-auto px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">成绩管理</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group mr-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportGrades()">
                            <i class="fas fa-download mr-1"></i>导出成绩
                        </button>
                    </div>
                </div>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle mr-1"></i>
                    <?php echo $success_message; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    <?php echo $error_message; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <!-- 选择班级和课程 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form class="form-inline" method="get">
                        <select class="form-control mr-2" name="class_id" onchange="this.form.submit()">
                            <option value="">选择班级</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo $selected_class == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select class="form-control" name="course_id" onchange="this.form.submit()">
                            <option value="">选择课程</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>" <?php echo $selected_course == $course['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <?php if ($selected_class && $selected_course): ?>
                <!-- 成绩统计 -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6 class="card-title">总人数</h6>
                                <p class="card-text h3"><?php echo $stats['total']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6 class="card-title">已录入</h6>
                                <p class="card-text h3"><?php echo $stats['submitted']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6 class="card-title">平均分</h6>
                                <p class="card-text h3"><?php echo $stats['average']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h6 class="card-title">最高分</h6>
                                <p class="card-text h3"><?php echo $stats['max']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h6 class="card-title">最低分</h6>
                                <p class="card-text h3"><?php echo $stats['min']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card bg-secondary text-white">
                            <div class="card-body">
                                <h6 class="card-title">及格率</h6>
                                <p class="card-text h3"><?php echo $stats['pass_rate']; ?>%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 成绩录入表格 -->
                <div class="card">
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="course_id" value="<?php echo $selected_course; ?>">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>学号</th>
                                            <th>姓名</th>
                                            <th>成绩</th>
                                            <th>状态</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['id']); ?></td>
                                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                <td width="200">
                                                    <input type="hidden" name="students[]" value="<?php echo $student['id']; ?>">
                                                    <input type="number" class="form-control" name="scores[]" 
                                                           value="<?php echo $student['score']; ?>" 
                                                           min="0" max="100" step="0.5">
                                                </td>
                                                <td>
                                                    <?php if ($student['score'] === null): ?>
                                                        <span class="badge badge-secondary">未录入</span>
                                                    <?php elseif ($student['score'] >= 90): ?>
                                                        <span class="badge badge-success">优秀</span>
                                                    <?php elseif ($student['score'] >= 60): ?>
                                                        <span class="badge badge-info">及格</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">不及格</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>保存成绩
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
function exportGrades() {
    // 实现导出功能
    alert('导出功能开发中...');
}
</script>

<?php require_once '../includes/footer.php'; ?> 