<!-- 侧边栏 -->
<nav class="col-md-2 d-none d-md-block bg-light sidebar">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle === '学生仪表板' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-home mr-2"></i>主页
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle === '个人信息' ? 'active' : ''; ?>" href="profile.php">
                    <i class="fas fa-user mr-2"></i>个人信息
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle === '课程表' ? 'active' : ''; ?>" href="schedule.php">
                    <i class="fas fa-calendar-alt mr-2"></i>课程表
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle === '成绩查询' ? 'active' : ''; ?>" href="grades.php">
                    <i class="fas fa-chart-line mr-2"></i>成绩查询
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle === '违规信息' ? 'active' : ''; ?>" href="violations.php">
                    <i class="fas fa-exclamation-triangle mr-2"></i>违规信息
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 48px 0 0;
    box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
}

.sidebar-sticky {
    position: relative;
    top: 0;
    height: calc(100vh - 48px);
    padding-top: .5rem;
    overflow-x: hidden;
    overflow-y: auto;
}

.nav-link {
    font-weight: 500;
    color: #333;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background-color: rgba(0, 123, 255, 0.1);
    color: #007bff;
}

.nav-link.active {
    color: #007bff;
    background-color: rgba(0, 123, 255, 0.1);
}

main {
    margin-top: 48px;
}
</style> 