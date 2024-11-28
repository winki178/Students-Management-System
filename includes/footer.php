    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> PHP教学班级管理系统 版权所有</p>
        </div>
    </footer>
    
    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
    <script src="/assets/js/main.js"></script>
    <?php if(isset($isDashboard)): ?>
    <script src="/assets/js/dashboard.js"></script>
    <?php endif; ?>
</body>
</html> 