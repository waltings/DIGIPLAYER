<?php
session_start();
if (!isset($_SESSION["user"])) { header("Location: /"); exit; }
$pageTitle = 'Group Management';
$currentPage = 'groups';
require_once 'template/header.php';
require_once 'template/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Group Management</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="showCreateGroupModal()">Create Group</button>
        </div>
    </div>

    <div class="groups-container">
        <div class="groups-panel">
            <div id="groupList" class="groups-list">
                <!-- Groups will be loaded here -->
            </div>
        </div>
        
        <div class="devices-panel">
            <h2>Available Devices</h2>
            <div id="availableDevices" class="devices-list">
                <!-- Available devices will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Group Modal -->
<div id="groupModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Create Group</h2>
            <button class="close-modal" onclick="closeModal('groupModal')">&times;</button>
        </div>
        <form id="groupForm">
            <input type="hidden" id="groupId" name="id">
            <div class="form-group">
                <label>Group Name</label>
                <input type="text" id="groupName" name="name" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea id="groupDescription" name="description"></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('groupModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Group</button>
            </div>
        </form>
    </div>
</div>

<!-- Permissions Modal -->
<div id="permissionsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Group Permissions</h2>
            <button class="close-modal" onclick="closeModal('permissionsModal')">&times;</button>
        </div>
        <form id="permissionsForm">
            <input type="hidden" id="permissionsGroupId" name="group_id">
            <div class="permissions-grid">
                <div class="permission-item">
                    <label>
                        <input type="checkbox" name="permissions[]" value="manage_content">
                        Content Management
                    </label>
                </div>
                <div class="permission-item">
                    <label>
                        <input type="checkbox" name="permissions[]" value="manage_playlists">
                        Playlist Management
                    </label>
                </div>
                <div class="permission-item">
                    <label>
                        <input type="checkbox" name="permissions[]" value="manage_schedule">
                        Schedule Management
                    </label>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('permissionsModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Permissions</button>
            </div>
        </form>
    </div>
</div>

<script src="/assets/js/groups.js"></script>
<?php require_once 'template/footer.php'; ?>
