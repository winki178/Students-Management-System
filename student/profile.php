<?php
$pageTitle = '个人信息';
require_once '../includes/header.php';
require_once '../includes/auth.php';

if(!isset($_SESSION['student_id'])) {
    header('Location: ../login.php');
    exit();
}

$studentInfo = getStudentInfo($_SESSION['student_id']);

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    if (password_verify($current_password, $studentInfo['password'])) {
        try {
            $stmt = $pdo->prepare("UPDATE students SET password = ? WHERE id = ?");
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt->execute([$hashed_password, $_SESSION['student_id']]);
            $success_message = "密码修改成功！";
        } catch (PDOException $e) {
            $error_message = "密码修改失败，请稍后重试。";
        }
    } else {
        $error_message = "当前密码不正确！";
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once '../includes/sidebar.php'; ?>

        <!-- 主要内容区域 -->
        <main role="main" class="col-md-10 ml-sm-auto px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">个人信息</h1>
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

            <div class="row">
                <!-- 基本信息卡片 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-info-circle mr-2"></i>基本信息
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">学号</label>
                                <div class="col-sm-9">
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($studentInfo['id']); ?></p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">姓名</label>
                                <div class="col-sm-9">
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($studentInfo['name']); ?></p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">班级</label>
                                <div class="col-sm-9">
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($studentInfo['class_name']); ?></p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">出生日期</label>
                                <div class="col-sm-9">
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($studentInfo['birth_date']); ?></p>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">入学时间</label>
                                <div class="col-sm-9">
                                    <p class="form-control-plaintext"><?php echo htmlspecialchars($studentInfo['enrollment_date']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 修改密码卡片 -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-key mr-2"></i>修改密码
                        </div>
                        <div class="card-body">
                            <form method="post" action="profile.php">
                                <div class="form-group">
                                    <label for="current_password">当前密码</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">新密码</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_new_password">确认新密码</label>
                                    <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary">修改密码</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.querySelector('form').onsubmit = function(e) {
    var newPassword = document.getElementById('new_password').value;
    var confirmPassword = document.getElementById('confirm_new_password').value;
    
    if (newPassword !== confirmPassword) {
        alert('两次输入的新密码不一致！');
        e.preventDefault();
        return false;
    }
};
</script>

<?php require_once '../includes/footer.php'; ?> 