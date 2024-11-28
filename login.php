<?php
$pageTitle = '用户登录';
if(isset($_SESSION['student_id']) || isset($_SESSION['teacher_id'])) {
    header('Location: ' . (isset($_SESSION['student_id']) ? 'student/dashboard.php' : 'teacher/dashboard.php'));
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户登录 - PHP教学班级管理系统</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.19), 0 6px 6px rgba(0,0,0,0.23);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(.25,.8,.25,1);
        }
        .card:hover {
            box-shadow: 0 15px 30px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
        }
        .card-header {
            background: white;
            border-bottom: none;
            padding: 20px 20px 0;
        }
        .nav-tabs {
            border: none;
        }
        .nav-tabs .nav-link {
            border: none;
            padding: 15px 25px;
            border-radius: 10px;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.3s ease;
            position: relative;
        }
        .nav-tabs .nav-link:hover {
            color: #4e73df;
            background-color: rgba(78,115,223,0.1);
        }
        .nav-tabs .nav-link.active {
            color: #4e73df;
            background-color: rgba(78,115,223,0.1);
        }
        .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 50%;
            height: 3px;
            background-color: #4e73df;
            border-radius: 3px;
        }
        .card-body {
            padding: 30px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e3e6f0;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78,115,223,.25);
        }
        .btn {
            padding: 12px 20px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(to right, #4e73df, #224abe);
            border: none;
        }
        .btn-success {
            background: linear-gradient(to right, #1cc88a, #13855c);
            border: none;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(50,50,93,.1), 0 3px 6px rgba(0,0,0,.08);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .form-label {
            font-weight: 600;
            color: #5a5c69;
            margin-bottom: 8px;
        }
        .input-group-text {
            border-radius: 10px 0 0 10px;
            border: 2px solid #e3e6f0;
            border-right: none;
            background-color: #f8f9fc;
        }
        .input-group .form-control {
            border-radius: 0 10px 10px 0;
        }
        .text-decoration-none:hover {
            color: #4e73df !important;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container fade-in">
                    <div class="text-center mb-4">
                        <h1 class="text-white mb-3">
                            <i class="fas fa-school"></i>
                            教学班级管理系统
                        </h1>
                        <p class="text-white-50">请选择您的身份进行登录</p>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" id="student-tab" data-toggle="tab" href="#student" role="tab">
                                        <i class="fas fa-user-graduate mr-2"></i>学生登录
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" id="teacher-tab" data-toggle="tab" href="#teacher" role="tab">
                                        <i class="fas fa-chalkboard-teacher mr-2"></i>教师登录
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- 学生登录表单 -->
                                <div class="tab-pane fade show active" id="student" role="tabpanel">
                                    <?php if(isset($_GET['error']) && $_GET['type'] === 'student'): ?>
                                        <div class="alert alert-danger alert-dismissible fade show">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            登录失败，请检查用户名和密码
                                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                                        </div>
                                    <?php endif; ?>
                                    <form action="includes/process_login.php" method="post">
                                        <input type="hidden" name="login_type" value="student">
                                        <div class="form-group">
                                            <label class="form-label" for="student_name">用户名</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-user"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control" id="student_name" name="name" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="student_password">密码</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                </div>
                                                <input type="password" class="form-control" id="student_password" name="password" required>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-sign-in-alt mr-2"></i>登录
                                        </button>
                                        <div class="text-center mt-4">
                                            <a href="register.php" class="text-decoration-none text-muted">
                                                <i class="fas fa-user-plus mr-1"></i>还没有账号？立即注册
                                            </a>
                                        </div>
                                    </form>
                                </div>

                                <!-- 教师登录表单 -->
                                <div class="tab-pane fade" id="teacher" role="tabpanel">
                                    <?php if(isset($_GET['error']) && $_GET['type'] === 'teacher'): ?>
                                        <div class="alert alert-danger alert-dismissible fade show">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            登录失败，请检查用户名和密码
                                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                                        </div>
                                    <?php endif; ?>
                                    <div class="text-center mb-4">
                                        <i class="fas fa-chalkboard-teacher fa-3x text-success mb-3"></i>
                                        <p class="text-muted">教师账号由管理员创建，如需账号请联系管理员</p>
                                    </div>
                                    <form action="includes/process_login.php" method="post">
                                        <input type="hidden" name="login_type" value="teacher">
                                        <div class="form-group">
                                            <label class="form-label" for="teacher_username">用户名</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-user"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control" id="teacher_username" name="username" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="teacher_password">密码</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                </div>
                                                <input type="password" class="form-control" id="teacher_password" name="password" required>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-block">
                                            <i class="fas fa-sign-in-alt mr-2"></i>教师登录
                                        </button>
                                        <div class="text-center mt-3">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                忘记密码？请联系系统管理员重置
                                            </small>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 