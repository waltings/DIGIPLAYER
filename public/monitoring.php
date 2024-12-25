<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$pageTitle = 'System Monitoring';
$currentPage = 'monitoring';
require_once 'template/header.php';
require_once 'template/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>System Monitoring</h1>
    </div>

    <div class="monitor-grid">
        <div class="status-card">
            <h3>Overview</h3>
            <div id="statusOverview"></div>
        </div>
        
        <div class="monitor-table">
            <table>
                <thead>
                    <tr>
                        <th>Device</th>
                        <th>Status</th>
                        <th>Network</th>
                        <th>CPU</th>
                        <th>Memory</th>
                        <th>Last Update</th>
                    </tr>
                </thead>
                <tbody id="monitorData"></tbody>
            </table>
        </div>
    </div>
</div>

<script src="/digiplayer/public/assets/js/monitoring.js"></script>
<?php require_once 'template/footer.php'; ?>
