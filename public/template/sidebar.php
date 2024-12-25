<div class="sidebar">
    <div class="sidebar-header">
        <h2>DigiPlayer</h2>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
        </a>
        <a href="devices.php" class="nav-item <?php echo $currentPage === 'devices' ? 'active' : ''; ?>">
            <i class="fas fa-desktop"></i> <span>Devices</span>
        </a>
        <a href="groups.php" class="nav-item <?php echo $currentPage === 'groups' ? 'active' : ''; ?>">
            <i class="fas fa-layer-group"></i> <span>Groups</span>
        </a>
        <a href="media.php" class="nav-item <?php echo $currentPage === 'media' ? 'active' : ''; ?>">
            <i class="fas fa-photo-video"></i> <span>Media</span>
        </a>
        <a href="playlists.php" class="nav-item <?php echo $currentPage === 'playlists' ? 'active' : ''; ?>">
            <i class="fas fa-list"></i> <span>Playlists</span>
        </a>
        <a href="schedule.php" class="nav-item <?php echo $currentPage === 'schedule' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i> <span>Schedule</span>
        </a>
        <a href="monitoring.php" class="nav-item <?php echo $currentPage === 'monitoring' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> <span>Monitoring</span>
        </a>

        <div class="nav-divider"></div>
        
        <a href="logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </nav>
</div>
