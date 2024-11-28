<?php
require_once 'includes/config.php';
require_once 'admin/classes.php';

$classes = getClasses();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>学生注册 - PHP教学班级管理系统</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <h2 class="text-center mb-4">学生注册</h2>
            <form action="includes/process_register.php" method="post">
                <div class="form-group">
                    <label for="name">姓名</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="class_id">班级</label>
                    <select class="form-control" id="class_id" name="class_id" required>
                        <option value="">请选择班级</option>
                        <?php foreach($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="birth_date">出生日期</label>
                    <input type="date" class="form-control" id="birth_date" name="birth_date" required>
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">确认密码</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">注册</button>
                <div class="text-center mt-3">
                    <a href="login.php">已有账号？立即登录</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.querySelector('form').onsubmit = function(e) {
            var password = document.getElementById('password').value;
            var confirm = document.getElementById('confirm_password').value;
            if (password !== confirm) {
                alert('两次输入的密码不一致！');
                e.preventDefault();
                return false;
            }
        };
    </script>
</body>
</html> 