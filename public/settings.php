<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$pageTitle = 'System Settings';
$currentPage = 'settings';
require_once 'template/header.php';
require_once 'template/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>System Settings</h1>
    </div>

    <div class="settings-container">
        <div id="settingsContainer">
            <!-- Settings content will be loaded here by system-settings.js -->
        </div>
    </div>
</div>

<script src="/digiplayer/public/assets/js/system-settings.js"></script>
<?php require_once 'template/footer.php'; ?>
