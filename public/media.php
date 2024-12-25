<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$pageTitle = 'Media Management';
$currentPage = 'media';
require_once 'template/header.php';
require_once 'template/sidebar.php';
?>

<div class="main-content">
    <div class="content-header">
        <h1>Media Management</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="showUploadModal()">
                <i class="icon-upload"></i> Upload Media
            </button>
        </div>
    </div>

    <div class="media-tools">
        <div class="search-filters">
            <input type="text" id="mediaSearch" placeholder="Search media...">
            <select id="typeFilter">
                <option value="">All Types</option>
                <option value="image">Images</option>
                <option value="video">Videos</option>
            </select>
            <select id="sortBy">
                <option value="date">Upload Date</option>
                <option value="name">Name</option>
                <option value="size">Size</option>
                <option value="type">Type</option>
            </select>
        </div>
        <div class="view-options">
            <button class="btn-icon" onclick="toggleView('grid')" id="gridViewBtn">
                <i class="icon-grid"></i>
            </button>
            <button class="btn-icon" onclick="toggleView('list')" id="listViewBtn">
                <i class="icon-list"></i>
            </button>
        </div>
    </div>

    <div id="mediaContainer" class="media-grid">
        <!-- Media items will be loaded here -->
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Upload Media</h2>
                <button class="close-modal" onclick="closeModal('uploadModal')">&times;</button>
            </div>
            <div class="upload-container">
                <div class="upload-dropzone" id="dropZone">
                    <i class="icon-upload"></i>
                    <p>Drag & drop files here or click to select</p>
                    <input type="file" id="fileInput" multiple accept="image/*,video/*" style="display: none">
                </div>
                <div id="uploadPreview" class="upload-preview">
                    <!-- Upload previews will appear here -->
                </div>
                <div class="upload-progress" id="uploadProgress" style="display: none">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <div class="progress-text">Uploading... <span id="progressPercent">0%</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('uploadModal')">Cancel</button>
                <button class="btn btn-primary" onclick="startUpload()">Upload Files</button>
            </div>
        </div>
    </div>

    <!-- Media Preview Modal -->
    <div id="previewModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2>Media Preview</h2>
                <button class="close-modal" onclick="closeModal('previewModal')">&times;</button>
            </div>
            <div class="preview-container">
                <div id="mediaPreview" class="media-preview">
                    <!-- Preview content will be loaded here -->
                </div>
                <div class="media-details">
                    <h3 id="previewName"></h3>
                    <div class="details-grid">
                        <div class="detail-item">
                            <label>Type</label>
                            <span id="previewType"></span>
                        </div>
                        <div class="detail-item">
                            <label>Size</label>
                            <span id="previewSize"></span>
                        </div>
                        <div class="detail-item">
                            <label>Dimensions</label>
                            <span id="previewDimensions"></span>
                        </div>
                        <div class="detail-item">
                            <label>Uploaded</label>
                            <span id="previewDate"></span>
                        </div>
                    </div>
                    <div class="preview-actions">
                        <button class="btn btn-secondary" onclick="downloadMedia()">
                            <i class="icon-download"></i> Download
                        </button>
                        <button class="btn btn-danger" onclick="deleteMedia()">
                            <i class="icon-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/digiplayer/public/assets/js/media.js"></script>
<?php require_once 'template/footer.php'; ?>
