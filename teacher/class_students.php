<?php
$pageTitle = '班级学生管理';
require_once '../includes/header.php';
require_once '../includes/auth.php';

if(!isset($_SESSION['teacher_id']) || !isset($_GET['class_id']) || !isset($_GET['course'])) {
    header('Location: ../login.php');
    exit();
}

$teacherInfo = getTeacherInfo($_SESSION['teacher_id']);
$class_id = $_GET['class_id'];
$course_name = $_GET['course'];

// 获取班级信息
function getClassInfo($class_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
    $stmt->execute([$class_id]);
    return $stmt->fetch();
}

// 获取班级学生列表
function getClassStudents($class_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT s.*, 
               COALESCE(g.score, '未录入') as course_score,
               COALESCE(a.status, '未记录') as attendance_status
        FROM students s
        LEFT JOIN grades g ON s.id = g.student_id 
            AND g.subject = ?
        LEFT JOIN attendance a ON s.id = a.student_id 
            AND a.course_id = (SELECT id FROM courses WHERE name = ? AND class_id = ?)
            AND DATE(a.date) = CURDATE()
        WHERE s.class_id = ?
        ORDER BY s.name
    ");
    $stmt->execute([$_GET['course'], $_GET['course'], $class_id, $class_id]);
    return $stmt->fetchAll();
}

// 获取所有未分配到该班级的学生
function getUnassignedStudents($class_id, $search = '') {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM students 
        WHERE (class_id IS NULL OR class_id != ?) 
        AND (name LIKE ? OR id LIKE ?)
        ORDER BY name
    ");
    $searchTerm = "%$search%";
    $stmt->execute([$class_id, $searchTerm, $searchTerm]);
    return $stmt->fetchAll();
}

// 处理添加和移除学生
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($_POST['action'] === 'add_student') {
            // 添加现有学生到班级
            $stmt = $pdo->prepare("
                UPDATE students 
                SET class_id = ?, enrollment_date = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $class_id,
                $_POST['enrollment_date'],
                $_POST['student_id']
            ]);
            $success_message = "已将学生添加到班级！";
        }
        elseif ($_POST['action'] === 'remove_student') {
            // 将学生从班级中移除（设置class_id为NULL）
            $stmt = $pdo->prepare("
                UPDATE students 
                SET class_id = NULL 
                WHERE id = ? AND class_id = ?
            ");
            $stmt->execute([
                $_POST['student_id'],
                $class_id
            ]);
            $success_message = "已将学生从班级中移除！";
        }
    } catch (PDOException $e) {
        $error_message = "操作失败：" . $e->getMessage();
    }
}

$classInfo = getClassInfo($class_id);
$students = getClassStudents($class_id);
$search = $_GET['search'] ?? '';
$unassignedStudents = getUnassignedStudents($class_id, $search);
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/teacher_sidebar.php'; ?>

        <!-- 主要内容区域 -->
        <main role="main" class="col-md-10 ml-sm-auto px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <div>
                    <a href="manage_classes.php?course=<?php echo urlencode($course_name); ?>" class="btn btn-outline-secondary mr-2">
                        <i class="fas fa-arrow-left"></i> 返回班级列表
                    </a>
                    <h1 class="h2 d-inline-block"><?php echo htmlspecialchars($classInfo['name']); ?> - <?php echo htmlspecialchars($course_name); ?></h1>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addStudentModal">
                        <i class="fas fa-user-plus mr-1"></i>添加学生
                    </button>
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

            <!-- 学生列表 -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>学号</th>
                                    <th>姓名</th>
                                    <th>出生日期</th>
                                    <th>入学时间</th>
                                    <th>课程成绩</th>
                                    <th>今日考勤</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">暂无学生数据</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['birth_date']); ?></td>
                                            <td><?php echo htmlspecialchars($student['enrollment_date']); ?></td>
                                            <td>
                                                <?php if ($student['course_score'] === '未录入'): ?>
                                                    <span class="badge badge-secondary">未录入</span>
                                                <?php else: ?>
                                                    <?php echo $student['course_score']; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($student['attendance_status'] === '出勤'): ?>
                                                    <span class="badge badge-success">出勤</span>
                                                <?php elseif ($student['attendance_status'] === '请假'): ?>
                                                    <span class="badge badge-warning">请假</span>
                                                <?php elseif ($student['attendance_status'] === '缺勤'): ?>
                                                    <span class="badge badge-danger">缺勤</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">未记录</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="recordGrade(<?php echo $student['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="recordAttendance(<?php echo $student['id']; ?>)">
                                                    <i class="fas fa-clipboard-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="removeStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['name']); ?>')">
                                                    <i class="fas fa-user-minus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- 添加学生模态框 -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">添加学生到 <?php echo htmlspecialchars($classInfo['name']); ?></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_student">
                    
                    <!-- 学生搜索框 -->
                    <div class="form-group">
                        <label>搜索学生</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="studentSearch" 
                                   placeholder="输入学生姓名或学号搜索...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 学生选择列表 -->
                    <div class="form-group">
                        <label>选择学生</label>
                        <select class="form-control" name="student_id" id="studentSelect" required>
                            <option value="">请选择学生</option>
                            <?php foreach ($unassignedStudents as $student): ?>
                                <option value="<?php echo $student['id']; ?>">
                                    <?php echo htmlspecialchars($student['name']); ?>
                                    (学号: <?php echo $student['id']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 入班时间 -->
                    <div class="form-group">
                        <label>入班时间</label>
                        <input type="date" class="form-control" name="enrollment_date" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">添加</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 修改搜索相关的JavaScript代码部分 -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
$(document).ready(function() {
    let searchTimeout;
    let studentOptions = $('#studentSelect option').clone(); // 保存所有学生选项的副本
    
    // 监听搜索框输入
    $('#studentSearch').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterStudents, 300);
    });
    
    // 点击搜索按钮
    $('#searchBtn').click(function(e) {
        e.preventDefault();
        filterStudents();
    });
    
    // 回车触发搜索
    $('#studentSearch').on('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            filterStudents();
        }
    });
    
    // 过滤学生列表
    function filterStudents() {
        const searchTerm = $('#studentSearch').val().toLowerCase();
        const $select = $('#studentSelect');
        
        // 清空当前选项
        $select.empty();
        
        // 添加默认选项
        $select.append('<option value="">请选择学生</option>');
        
        // 过滤并添加匹配的选项
        studentOptions.each(function() {
            const $option = $(this);
            const text = $option.text().toLowerCase();
            if (text.includes(searchTerm)) {
                $select.append($option.clone());
            }
        });
    }

    // 初始化模态框
    $('#addStudentModal').on('shown.bs.modal', function () {
        $('#studentSearch').val('').focus();
        // 重置学生列表到初始状态
        $('#studentSelect').empty().append(studentOptions.clone());
    });
});

function removeStudent(studentId, studentName) {
    document.getElementById('removeStudentId').value = studentId;
    document.getElementById('removeStudentName').textContent = studentName;
    $('#removeStudentModal').modal('show');
}
</script>

<!-- 添加移除学生确认模态框 -->
<div class="modal fade" id="removeStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">移除学生</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>确定要将 <strong id="removeStudentName"></strong> 从班级中移除吗？</p>
                <p class="text-muted small">移除后该学生将变为未分配班级状态，可以重新分配到其他班级。</p>
            </div>
            <div class="modal-footer">
                <form action="" method="post">
                    <input type="hidden" name="action" value="remove_student">
                    <input type="hidden" name="student_id" id="removeStudentId">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-danger">移除</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 