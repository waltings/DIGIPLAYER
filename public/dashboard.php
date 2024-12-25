<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
require_once 'template/header.php';
require_once 'template/sidebar.php';
?>

<div class="main-content">
    <div class="page-title">
        DigiPlayer <span>/ Dashboard</span>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon devices"></div>
            <div class="stat-info">
                <span class="stat-label">Active Devices</span>
                <div class="stat-value" id="activeDevices">0</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon media"></div>
            <div class="stat-info">
                <span class="stat-label">Media Files</span>
                <div class="stat-value" id="mediaCount">0</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon playlists"></div>
            <div class="stat-info">
                <span class="stat-label">Active Playlists</span>
                <div class="stat-value" id="playlistCount">0</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon storage"></div>
            <div class="stat-info">
                <span class="stat-label">Storage Used</span>
                <div class="stat-value" id="storageUsed">0 GB</div>
            </div>
        </div>
    </div>

    <div class="dashboard-charts">
        <div class="chart-card">
            <h3>Playback Activity</h3>
            <canvas id="playbackChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Device Status</h3>
            <canvas id="deviceStatusChart"></canvas>
        </div>
    </div>

    <div class="recent-activity card">
        <h3>Recent Activity</h3>
        <div id="activityList" class="activity-list"></div>
    </div>
</div>

<script src="/digiplayer/public/assets/js/dashboard-widgets.js"></script>
<?php require_once 'template/footer.php'; ?>
