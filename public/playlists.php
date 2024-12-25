<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$pageTitle = 'Playlist Management';
$currentPage = 'playlists';
require_once 'template/header.php';
require_once 'template/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Playlist Management</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="showAddPlaylistModal()">
                <i class="icon-plus"></i> Create Playlist
            </button>
        </div>
    </div>

    <div class="playlist-container">
        <div class="playlists-panel">
            <div class="panel-header">
                <h3>Playlists</h3>
                <div class="search-box">
                    <input type="text" id="playlistSearch" placeholder="Search playlists...">
                </div>
            </div>
            <div id="playlistsList" class="playlists-list">
                <!-- Playlists will be loaded here -->
            </div>
        </div>

        <div class="content-panel" id="playlistContentPanel">
            <div class="panel-header">
                <h3>Playlist Content</h3>
                <button class="btn btn-secondary" onclick="showAddContentModal()">
                    <i class="icon-plus"></i> Add Content
                </button>
            </div>
            <div id="playlistContent" class="content-list sortable">
                <!-- Playlist content will be loaded here -->
            </div>
            <div class="panel-footer">
                <div class="playlist-info">
                    <span id="totalDuration">Total Duration: 0:00</span>
                    <span id="itemCount">Items: 0</span>
                </div>
                <button class="btn btn-primary" onclick="savePlaylistChanges()">
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- Add Playlist Modal -->
    <div id="addPlaylistModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Playlist</h2>
                <button class="close-modal" onclick="closeModal('addPlaylistModal')">&times;</button>
            </div>
            <form id="addPlaylistForm">
                <div class="form-group">
                    <label for="playlistName">Playlist Name</label>
                    <input type="text" id="playlistName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="playlistDescription">Description</label>
                    <textarea id="playlistDescription" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="scheduleType">Schedule Type</label>
                    <select id="scheduleType" name="schedule_type">
                        <option value="always">Always</option>
                        <option value="scheduled">Scheduled</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addPlaylistModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Playlist</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Content Modal -->
    <div id="addContentModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2>Add Content to Playlist</h2>
                <button class="close-modal" onclick="closeModal('addContentModal')">&times;</button>
            </div>
            <div class="content-browser">
                <div class="filter-bar">
                    <select id="contentTypeFilter">
                        <option value="">All Types</option>
                        <option value="image">Images</option>
                        <option value="video">Videos</option>
                    </select>
                    <input type="text" id="contentSearch" placeholder="Search content...">
                </div>
                <div id="mediaGrid" class="media-grid">
                    <!-- Available media items will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('addContentModal')">Cancel</button>
                <button class="btn btn-primary" onclick="addSelectedContent()">Add Selected</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script src="/digiplayer/public/assets/js/playlists.js"></script>
<?php require_once 'template/footer.php'; ?>
