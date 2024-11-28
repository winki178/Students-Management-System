<?php
$pageTitle = '考勤管理';
require_once '../includes/header.php';
require_once '../includes/auth.php';

if(!isset($_SESSION['teacher_id'])) {
    header('Location: ../login.php');
    exit();
}

$teacherInfo = getTeacherInfo($_SESSION['teacher_id']);

// 获取教师所教的班级和课程
$classes = getTeacherClasses($_SESSION['teacher_id']);
$selected_class = $_GET['class_id'] ?? ($classes[0]['id'] ?? null);
$courses = $selected_class ? getTeacherCourses($_SESSION['teacher_id'], $selected_class) : [];
$selected_course = $_GET['course_id'] ?? ($courses[0]['id'] ?? null);

// 获取班级学生列表和考勤记录
function getClassStudentsAttendance($class_id, $course_id, $date) {
    global $pdo;
    $query = "
        SELECT s.*, 
               COALESCE(a.status, '未记录') as attendance_status,
               a.remark
        FROM students s
        LEFT JOIN attendance a ON s.id = a.student_id 
            AND a.course_id = ?
            AND a.date = ?
        WHERE s.class_id = ?
        ORDER BY s.name
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$course_id, $date, $class_id]);
    return $stmt->fetchAll();
}

// 处理考勤记录提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        $date = $_POST['date'];
        $students = $_POST['students'];
        $status = $_POST['status'];
        $remarks = $_POST['remarks'];
        $course_id = $_POST['course_id'];
        
        // 删除当天该班级该课程的旧记录
        $stmt = $pdo->prepare("
            DELETE FROM attendance 
            WHERE date = ? AND course_id = ? AND student_id IN (
                SELECT id FROM students WHERE class_id = ?
            )
        ");
        $stmt->execute([$date, $course_id, $selected_class]);
        
        // 插入新的考勤记录
        $stmt = $pdo->prepare("
            INSERT INTO attendance (student_id, course_id, date, status, remark) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($students as $index => $student_id) {
            if (isset($status[$index])) {
                $stmt->execute([
                    $student_id,
                    $course_id,
                    $date,
                    $status[$index],
                    $remarks[$index] ?? ''
                ]);
            }
        }
        
        $pdo->commit();
        $success_message = "考勤记录保存成功！";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "操作失败：" . $e->getMessage();
    }
}

$date = $_GET['date'] ?? date('Y-m-d');
$students = ($selected_class && $selected_course) ? getClassStudentsAttendance($selected_class, $selected_course, $date) : [];
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/teacher_sidebar.php'; ?>

        <!-- 主要内容区域 -->
        <main role="main" class="col-md-10 ml-sm-auto px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">考勤管理</h1>
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

            <!-- 选择班级、课程和日期 -->
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
                        <?php if ($selected_class): ?>
                            <select class="form-control mr-2" name="course_id" onchange="this.form.submit()">
                                <option value="">选择课程</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>" <?php echo $selected_course == $course['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                        <input type="date" class="form-control" name="date" value="<?php echo $date; ?>" onchange="this.form.submit()">
                    </form>
                </div>
            </div>

            <?php if ($selected_class && $selected_course): ?>
                <!-- 考勤记录表格 -->
                <div class="card">
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="date" value="<?php echo $date; ?>">
                            <input type="hidden" name="course_id" value="<?php echo $selected_course; ?>">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>学号</th>
                                            <th>姓名</th>
                                            <th>考勤状态</th>
                                            <th>备注</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['id']); ?></td>
                                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                <td>
                                                    <input type="hidden" name="students[]" value="<?php echo $student['id']; ?>">
                                                    <select class="form-control" name="status[]">
                                                        <option value="出勤" <?php echo $student['attendance_status'] === '出勤' ? 'selected' : ''; ?>>出勤</option>
                                                        <option value="缺勤" <?php echo $student['attendance_status'] === '缺勤' ? 'selected' : ''; ?>>缺勤</option>
                                                        <option value="请假" <?php echo $student['attendance_status'] === '请假' ? 'selected' : ''; ?>>请假</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" name="remarks[]" 
                                                           value="<?php echo htmlspecialchars($student['remark'] ?? ''); ?>"
                                                           placeholder="输入备注">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>保存考勤记录
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 