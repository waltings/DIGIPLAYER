<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$pageTitle = 'Schedule Management';
$currentPage = 'schedule';
require_once 'template/header.php';
require_once 'template/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Schedule Management</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="showAddScheduleModal()">
                <i class="icon-plus"></i> Add Schedule
            </button>
        </div>
    </div>

    <div class="schedule-container">
        <div class="schedule-grid">
            <div class="schedule-table">
                <table>
                    <thead>
                        <tr>
                            <th>Playlist</th>
                            <th>Device/Group</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Repeat</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="scheduleData"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Schedule Modal -->
    <div id="scheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Schedule</h2>
                <button class="close-modal" onclick="closeModal('scheduleModal')">&times;</button>
            </div>
            <form id="scheduleForm">
                <!-- Schedule form content -->
            </form>
        </div>
    </div>
</div>

<script src="/digiplayer/public/assets/js/schedule.js"></script>
<?php require_once 'template/footer.php'; ?>
