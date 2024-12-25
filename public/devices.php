<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$pageTitle = 'Device Management';
$currentPage = 'devices';
require_once 'template/header.php';
require_once 'template/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Device Management</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="showAddDeviceModal()">
                <i class="fas fa-plus"></i> Add Device
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search devices..." class="form-control">
        </div>
        <select id="statusFilter" class="form-control">
            <option value="">All Statuses</option>
            <option value="online">Online</option>
            <option value="offline">Offline</option>
            <option value="pending">Pending</option>
        </select>
        <select id="groupFilter" class="form-control">
            <option value="">All Groups</option>
        </select>
    </div>

    <!-- Bulk Actions -->
    <div id="bulkActions" class="bulk-actions" style="display: none;">
        <button onclick="bulkAction('restart')" class="btn btn-secondary">Restart Selected</button>
        <button onclick="bulkAction('update')" class="btn btn-secondary">Update Content</button>
        <button onclick="assignToGroup()" class="btn btn-secondary">Assign to Group</button>
    </div>

    <!-- Device List -->
    <div id="devicesList" class="devices-list">
        <!-- Devices will be loaded here -->
    </div>

    <!-- Add/Edit Device Modal -->
    <div id="deviceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Device</h2>
                <button class="close-modal" onclick="closeModal('deviceModal')">&times;</button>
            </div>
            <form id="deviceForm">
                <input type="hidden" id="deviceId" name="id">
                <div class="form-group">
                    <label for="deviceName">Device Name*</label>
                    <input type="text" id="deviceName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="deviceIP">IP Address*</label>
                    <input type="text" id="deviceIP" name="ip_address" required>
                </div>
                <div class="form-group">
                    <label for="deviceLocation">Location</label>
                    <input type="text" id="deviceLocation" name="location">
                </div>
                <div class="form-group">
                    <label for="deviceGroup">Group</label>
                    <select id="deviceGroup" name="group_id">
                        <option value="">Select Group</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="devicePlaylist">Default Playlist</label>
                    <select id="devicePlaylist" name="playlist_id">
                        <option value="">Select Playlist</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('deviceModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Device</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/digiplayer/public/assets/js/devices.js"></script>
<?php require_once 'template/footer.php'; ?>