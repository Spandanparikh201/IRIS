    </div>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const btn = document.getElementById('toggleBtn');
            const icon = btn.querySelector('i');
            const isCollapsed = sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            btn.style.left = isCollapsed ? '54px' : '262px';
            icon.style.transform = isCollapsed ? 'rotate(180deg)' : 'rotate(0deg)';
            if (window.innerWidth <= 1024) sidebar.classList.toggle('mobile-open');
        }
    </script>
    <script><?= $pageScripts ?? '' ?></script>
</body>
</html>
