<!-- 教师端侧边栏 -->
<nav class="col-md-2 d-none d-md-block bg-light sidebar">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle === '教师仪表板' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-home mr-2"></i>主页
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle === '学生管理' ? 'active' : ''; ?>" href="manage_students.php">
                    <i class="fas fa-users mr-2"></i>学生管理
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle === '班级管理' ? 'active' : ''; ?>" href="manage_classes.php">
                    <i class="fas fa-chalkboard mr-2"></i>班级管理
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle === '考勤管理' ? 'active' : ''; ?>" href="attendance.php">
                    <i class="fas fa-clipboard-check mr-2"></i>考勤管理
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle === '成绩管理' ? 'active' : ''; ?>" href="manage_grades.php">
                    <i class="fas fa-chart-line mr-2"></i>成绩管理
                </a>
            </li>
        </ul>
    </div>
</nav> 