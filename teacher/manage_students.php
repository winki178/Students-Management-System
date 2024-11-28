<?php
$pageTitle = '学生管理';
require_once '../includes/header.php';
require_once '../includes/auth.php';

if(!isset($_SESSION['teacher_id'])) {
    header('Location: ../login.php');
    exit();
}

$teacherInfo = getTeacherInfo($_SESSION['teacher_id']);

// 获取分页参数
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // 每页显示10条记录
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$order = isset($_GET['order']) ? $_GET['order'] : 'asc';

// 获取教师所教的学生总数
function getTeacherStudentsCount($teacher_id, $search = '') {
    global $pdo;
    try {
        $query = "
            SELECT COUNT(DISTINCT s.id) 
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN courses co ON c.id = co.class_id
            WHERE (co.teacher = (SELECT name FROM teachers WHERE id = ?) OR s.class_id IS NULL)
        ";
        $params = [$teacher_id];
        
        if ($search) {
            $query .= " AND s.name LIKE ?";
            $params[] = "%$search%";
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting student count: " . $e->getMessage());
        return 0;
    }
}

// 获取教师所教的学生列表（带分页和搜索）
function getTeacherStudentsPaginated($teacher_id, $page = 1, $limit = 10, $search = '', $sort = 'name', $order = 'asc') {
    global $pdo;
    try {
        $offset = ($page - 1) * $limit;
        $query = "
            SELECT DISTINCT s.*, COALESCE(c.name, '未分配') as class_name
            FROM students s
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN courses co ON c.id = co.class_id
            WHERE (co.teacher = (SELECT name FROM teachers WHERE id = ?) OR s.class_id IS NULL)
        ";
        $params = [$teacher_id];
        
        if ($search) {
            $query .= " AND s.name LIKE ?";
            $params[] = "%$search%";
        }
        
        // 添加排序
        $allowedSortFields = ['id', 'name', 'class_name', 'enrollment_date'];
        $sort = in_array($sort, $allowedSortFields) ? $sort : 'name';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        
        if ($sort === 'class_name') {
            $query .= " ORDER BY c.name $order, s.name ASC";
        } else {
            $query .= " ORDER BY s.$sort $order";
        }
        
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting students: " . $e->getMessage());
        return [];
    }
}

// 处理学生信息的添加、修改和删除
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($_POST['action'] === 'add') {
            // 添加学生（不分配班级）
            $stmt = $pdo->prepare("
                INSERT INTO students (name, birth_date, enrollment_date, password) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['name'],
                $_POST['birth_date'],
                $_POST['enrollment_date'],
                password_hash('123456', PASSWORD_DEFAULT) // 默认密码设为123456
            ]);
            $success_message = "学生添加成功！初始密码为：123456";
            
            // 添加成功后重定向到当前页面，避免表单重复提交
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=add");
            exit();
        } 
        elseif ($_POST['action'] === 'edit') {
            // 修改学生信息
            $stmt = $pdo->prepare("
                UPDATE students 
                SET name = ?, class_id = ?, birth_date = ?, enrollment_date = ?
                WHERE id = ? AND class_id IN (
                    SELECT DISTINCT c.id 
                    FROM classes c
                    JOIN courses co ON c.id = co.class_id
                    WHERE co.teacher = (SELECT name FROM teachers WHERE id = ?)
                )
            ");
            $stmt->execute([
                $_POST['name'],
                $_POST['class_id'],
                $_POST['birth_date'],
                $_POST['enrollment_date'],
                $_POST['student_id'],
                $_SESSION['teacher_id']
            ]);
            $success_message = "学生信息更新成功！";
        }
        elseif ($_POST['action'] === 'delete') {
            // 删除学生
            $stmt = $pdo->prepare("
                DELETE FROM students 
                WHERE id = ? AND class_id IN (
                    SELECT DISTINCT c.id 
                    FROM classes c
                    JOIN courses co ON c.id = co.class_id
                    WHERE co.teacher = (SELECT name FROM teachers WHERE id = ?)
                )
            ");
            $stmt->execute([$_POST['student_id'], $_SESSION['teacher_id']]);
            $success_message = "学生删除成功！";
        }
    } catch (PDOException $e) {
        $error_message = "操作失败：" . $e->getMessage();
    }
}

// 在页面顶部添加成功消息处理
if (isset($_GET['success']) && $_GET['success'] === 'add') {
    $success_message = "学生添加成功！初始密码为：123456";
}

// 获取学生列表
$total = getTeacherStudentsCount($_SESSION['teacher_id'], $search);
$totalPages = ceil($total / $limit);
$students = getTeacherStudentsPaginated($_SESSION['teacher_id'], $page, $limit, $search, $sort, $order);

// 获取教师所教的班级（用于添加/编辑学生时选择班级）
$classes = getTeacherClasses($_SESSION['teacher_id']);
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/teacher_sidebar.php'; ?>

        <!-- 主要内容区域 -->
        <main role="main" class="col-md-10 ml-sm-auto px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">学生管理</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addStudentModal">
                        <i class="fas fa-plus mr-1"></i>添加学生
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

            <!-- 搜索栏 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form class="form-inline" method="get">
                        <input type="text" class="form-control mb-2 mr-sm-2" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" placeholder="搜索学生姓名...">
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                        <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>">
                        <button type="submit" class="btn btn-primary mb-2">
                            <i class="fas fa-search mr-1"></i>搜索
                        </button>
                    </form>
                </div>
            </div>

            <!-- 学生列表 -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="?sort=id&order=<?php echo $sort === 'id' && $order === 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>" class="text-dark">
                                            学号
                                            <?php if ($sort === 'id'): ?>
                                                <i class="fas fa-sort-<?php echo $order === 'asc' ? 'up' : 'down'; ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?sort=name&order=<?php echo $sort === 'name' && $order === 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>" class="text-dark">
                                            姓名
                                            <?php if ($sort === 'name'): ?>
                                                <i class="fas fa-sort-<?php echo $order === 'asc' ? 'up' : 'down'; ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?sort=class_name&order=<?php echo $sort === 'class_name' && $order === 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>" class="text-dark">
                                            班级
                                            <?php if ($sort === 'class_name'): ?>
                                                <i class="fas fa-sort-<?php echo $order === 'asc' ? 'up' : 'down'; ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>出生日期</th>
                                    <th>
                                        <a href="?sort=enrollment_date&order=<?php echo $sort === 'enrollment_date' && $order === 'asc' ? 'desc' : 'asc'; ?>&search=<?php echo urlencode($search); ?>" class="text-dark">
                                            入学时间
                                            <?php if ($sort === 'enrollment_date'): ?>
                                                <i class="fas fa-sort-<?php echo $order === 'asc' ? 'up' : 'down'; ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">暂无学生数据</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['id']); ?></td>
                                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['class_name']); ?></td>
                                            <td><?php echo htmlspecialchars($student['birth_date']); ?></td>
                                            <td><?php echo htmlspecialchars($student['enrollment_date']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="editStudent(<?php echo htmlspecialchars(json_encode($student)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteStudent(<?php echo $student['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary" onclick="viewGrades(<?php echo $student['id']; ?>)">
                                                    <i class="fas fa-chart-line"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="viewAttendance(<?php echo $student['id']; ?>)">
                                                    <i class="fas fa-clipboard-check"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- 分页 -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>">上一页</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>&search=<?php echo urlencode($search); ?>">下一页</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
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
                <h5 class="modal-title">添加学生</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>姓名</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>出生日期</label>
                        <input type="date" class="form-control" name="birth_date" required>
                    </div>
                    <div class="form-group">
                        <label>入学时间</label>
                        <input type="date" class="form-control" name="enrollment_date" required>
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

<!-- 编辑学生模态框 -->
<div class="modal fade" id="editStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">编辑学生信息</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="student_id" id="edit_student_id">
                    <div class="form-group">
                        <label>姓名</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label>班级</label>
                        <select class="form-control" name="class_id" id="edit_class_id" required>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>出生日期</label>
                        <input type="date" class="form-control" name="birth_date" id="edit_birth_date" required>
                    </div>
                    <div class="form-group">
                        <label>入学时间</label>
                        <input type="date" class="form-control" name="enrollment_date" id="edit_enrollment_date" required>
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
<div class="modal fade" id="deleteStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">确认删除</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>确定要删除这名学生吗？此操作不可恢复。</p>
            </div>
            <div class="modal-footer">
                <form action="" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="student_id" id="delete_student_id">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-danger">删除</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editStudent(student) {
    document.getElementById('edit_student_id').value = student.id;
    document.getElementById('edit_name').value = student.name;
    document.getElementById('edit_class_id').value = student.class_id;
    document.getElementById('edit_birth_date').value = student.birth_date;
    document.getElementById('edit_enrollment_date').value = student.enrollment_date;
    $('#editStudentModal').modal('show');
}

function deleteStudent(studentId) {
    document.getElementById('delete_student_id').value = studentId;
    $('#deleteStudentModal').modal('show');
}

function viewGrades(studentId) {
    window.location.href = `manage_grades.php?student_id=${studentId}`;
}

function viewAttendance(studentId) {
    window.location.href = `attendance.php?student_id=${studentId}`;
}
</script>

<?php require_once '../includes/footer.php'; ?> 