<?php
$pageTitle = '班级管理';
require_once '../includes/header.php';
require_once '../includes/auth.php';

if(!isset($_SESSION['teacher_id'])) {
    header('Location: ../login.php');
    exit();
}

$teacherInfo = getTeacherInfo($_SESSION['teacher_id']);

// 处理班级管理操作
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($_POST['action'] === 'add_class') {
            // 添加班级
            $stmt = $pdo->prepare("INSERT INTO classes (name) VALUES (?)");
            $stmt->execute([$_POST['class_name']]);
            $success_message = "班级添加成功！";
        } 
        elseif ($_POST['action'] === 'edit_class') {
            // 修改班级信息
            $stmt = $pdo->prepare("
                UPDATE classes 
                SET name = ?
                WHERE id = ? AND id IN (
                    SELECT DISTINCT c.id 
                    FROM classes c
                    JOIN courses co ON c.id = co.class_id
                    WHERE co.teacher = (SELECT name FROM teachers WHERE id = ?)
                )
            ");
            $stmt->execute([
                $_POST['class_name'],
                $_POST['class_id'],
                $_SESSION['teacher_id']
            ]);
            $success_message = "班级信息更新成功！";
        }
        elseif ($_POST['action'] === 'delete_class') {
            // 删除班级
            $stmt = $pdo->prepare("
                DELETE FROM classes 
                WHERE id = ? AND id IN (
                    SELECT DISTINCT c.id 
                    FROM classes c
                    JOIN courses co ON c.id = co.class_id
                    WHERE co.teacher = (SELECT name FROM teachers WHERE id = ?)
                )
            ");
            $stmt->execute([$_POST['class_id'], $_SESSION['teacher_id']]);
            $success_message = "班级删除成功！";
        }
    } catch (PDOException $e) {
        $error_message = "操作失败：" . $e->getMessage();
    }
}

// 获取教师的所有课程
$courses = getTeacherAllCourses($_SESSION['teacher_id']);
$selected_course = $_GET['course'] ?? '';
$classes = $selected_course ? getCourseClasses($selected_course, $_SESSION['teacher_id']) : [];
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/teacher_sidebar.php'; ?>

        <!-- 主要内容区域 -->
        <main role="main" class="col-md-10 ml-sm-auto px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">班级管理</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addClassModal">
                        <i class="fas fa-plus mr-1"></i>添加班级
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

            <!-- 课程选择区域 -->
            <div class="row mb-4">
                <?php foreach ($courses as $course): ?>
                    <div class="col-md-4 mb-4">
                        <a href="?course=<?php echo urlencode($course['name']); ?>" class="text-decoration-none">
                            <div class="card h-100 <?php echo $selected_course === $course['name'] ? 'border-primary' : ''; ?>">
                                <div class="card-body text-center">
                                    <i class="fas fa-book fa-3x mb-3 text-primary"></i>
                                    <h5 class="card-title"><?php echo htmlspecialchars($course['name']); ?></h5>
                                    <p class="card-text">
                                        <span class="badge badge-primary"><?php echo $course['class_count']; ?> 个班级</span>
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($selected_course): ?>
                <!-- 班级列表 -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <?php echo htmlspecialchars($selected_course); ?> - 班级列表
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>班级名称</th>
                                        <th>学生人数</th>
                                        <th>创建时间</th>
                                        <th>操作</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $class): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($class['name']); ?></td>
                                            <td><?php echo $class['student_count']; ?></td>
                                            <td><?php echo htmlspecialchars($class['created_at']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="editClass(<?php echo htmlspecialchars(json_encode($class)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteClass(<?php echo $class['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <a href="class_students.php?class_id=<?php echo $class['id']; ?>&course=<?php echo urlencode($selected_course); ?>" class="btn btn-sm btn-primary">
    <i class="fas fa-users"></i>
</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- 添加班级模态框 -->
<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">添加班级</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_class">
                    <div class="form-group">
                        <label>班级名称</label>
                        <input type="text" class="form-control" name="class_name" required>
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

<!-- 编辑班级模态框 -->
<div class="modal fade" id="editClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">编辑班级</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_class">
                    <input type="hidden" name="class_id" id="edit_class_id">
                    <div class="form-group">
                        <label>班级名称</label>
                        <input type="text" class="form-control" name="class_name" id="edit_class_name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 删除确认模态框 -->
<div class="modal fade" id="deleteClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">确认删除</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>确定要删除这个班级吗？此操作不可恢复。</p>
            </div>
            <div class="modal-footer">
                <form action="" method="post">
                    <input type="hidden" name="action" value="delete_class">
                    <input type="hidden" name="class_id" id="delete_class_id">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-danger">删除</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editClass(classInfo) {
    document.getElementById('edit_class_id').value = classInfo.id;
    document.getElementById('edit_class_name').value = classInfo.name;
    $('#editClassModal').modal('show');
}

function deleteClass(classId) {
    document.getElementById('delete_class_id').value = classId;
    $('#deleteClassModal').modal('show');
}
</script>

<?php require_once '../includes/footer.php'; ?> 