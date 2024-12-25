let mediaItems = [];
let selectedFiles = [];
let currentView = 'grid';

document.addEventListener('DOMContentLoaded', function() {
    initializeMediaManager();
    setupEventListeners();
});

function initializeMediaManager() {
    loadMedia();
    setupDropZone();
}

function setupEventListeners() {
    // Search and filters
    document.getElementById('mediaSearch').addEventListener('input', filterMedia);
    document.getElementById('typeFilter').addEventListener('change', filterMedia);
    document.getElementById('sortBy').addEventListener('change', sortMedia);
    
    // File input
    const fileInput = document.getElementById('fileInput');
    fileInput.addEventListener('change', handleFileSelect);
    
    // Drop zone
    const dropZone = document.getElementById('dropZone');
    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover', handleDragOver);
    dropZone.addEventListener('dragleave', handleDragLeave);
    dropZone.addEventListener('drop', handleDrop);
}

async function loadMedia() {
    try {
        const response = await fetch('/api/media/index.php');
        const data = await response.json();
        mediaItems = data.media;
        renderMedia(mediaItems);
    } catch (error) {
        showNotification('Error loading media', 'error');
    }
}

function renderMedia(items) {
    const container = document.getElementById('mediaContainer');
    container.className = currentView === 'grid' ? 'media-grid' : 'media-list';
    
    if (currentView === 'grid') {
        container.innerHTML = items.map(item => `
            <div class="media-item" onclick="previewMedia(${item.id})">
                <div class="media-preview">
                    ${item.type === 'video' 
                        ? `<video src="${item.file_path}" preload="metadata"></video>`
                        : `<img src="${item.file_path}" alt="${item.name}">`
                    }
                    <div class="media-overlay">
                        <span class="media-type">${item.type}</span>
                        ${item.duration ? `<span class="media-duration">${formatDuration(item.duration)}</span>` : ''}
                    </div>
                </div>
                <div class="media-info">
                    <h4>${item.name}</h4>
                    <span class="media-size">${formatSize(item.size)}</span>
                </div>
            </div>
        `).join('');
    } else {
        container.innerHTML = `
            <table class="media-table">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Duration</th>
                        <th>Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${items.map(item => `
                        <tr>
                            <td>
                                <div class="table-preview" onclick="previewMedia(${item.id})">
                                    ${item.type === 'video' 
                                        ? `<video src="${item.file_path}" preload="metadata"></video>`
                                        : `<img src="${item.file_path}" alt="${item.name}">`
                                    }
                                </div>
                            </td>
                            <td>${item.name}</td>
                            <td>${item.type}</td>
                            <td>${formatSize(item.size)}</td>
                            <td>${item.duration ? formatDuration(item.duration) : '-'}</td>
                            <td>${formatDate(item.created_at)}</td>
                            <td>
                                <button class="btn-icon" onclick="previewMedia(${item.id})">
                                    <i class="icon-eye"></i>
                                </button>
                                <button class="btn-icon" onclick="deleteMedia(${item.id})">
                                    <i class="icon-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }
}

function handleFileSelect(e) {
    const files = Array.from(e.target.files);
    addFilesToUpload(files);
}

function handleDragOver(e) {
    e.preventDefault();
    e.currentTarget.classList.add('dragover');
}

function handleDragLeave(e) {
    e.currentTarget.classList.remove('dragover');
}

function handleDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('dragover');
    
    const files = Array.from(e.dataTransfer.files);
    addFilesToUpload(files);
}

function addFilesToUpload(files) {
    const validFiles = files.filter(file => {
        const isValid = file.type.startsWith('image/') || file.type.startsWith('video/');
        if (!isValid) {
            showNotification(`Invalid file type: ${file.name}`, 'error');
        }
        return isValid;
    });

    selectedFiles = [...selectedFiles, ...validFiles];
    updateUploadPreview();
}

function updateUploadPreview() {
    const preview = document.getElementById('uploadPreview');
    preview.innerHTML = selectedFiles.map((file, index) => `
        <div class="preview-item">
            ${file.type.startsWith('video') 
                ? '<i class="icon-video"></i>'
                : `<img src="${URL.createObjectURL(file)}" alt="${file.name}">`
            }
            <div class="preview-info">
                <span class="preview-name">${file.name}</span>
                <span class="preview-size">${formatSize(file.size)}</span>
            </div>
            <button class="btn-icon" onclick="removeFile(${index})">
                <i class="icon-x"></i>
            </button>
        </div>
    `).join('');
}

async function startUpload() {
    if (selectedFiles.length === 0) return;
    
    const progress = document.getElementById('uploadProgress');
    const progressBar = progress.querySelector('.progress-fill');
    const progressText = document.getElementById('progressPercent');
    
    progress.style.display = 'block';
    let uploaded = 0;
    
    try {
        for (const file of selectedFiles) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('name', file.name.replace(/\.[^/.]+$/, ''));
            
            const response = await fetch('/api/media/index.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) throw new Error(`Failed to upload ${file.name}`);
            
            uploaded++;
            const percent = (uploaded / selectedFiles.length) * 100;
            progressBar.style.width = `${percent}%`;
            progressText.textContent = `${Math.round(percent)}%`;
        }
        
        showNotification('Upload completed successfully', 'success');
        closeModal('uploadModal');
        loadMedia();
    } catch (error) {
        showNotification(error.message, 'error');
    }
    
    // Reset upload state
    selectedFiles = [];
    progress.style.display = 'none';
    progressBar.style.width = '0%';
    document.getElementById('uploadPreview').innerHTML = '';
}

function previewMedia(mediaId) {
    const media = mediaItems.find(item => item.id === mediaId);
    if (!media) return;
    
    const preview = document.getElementById('mediaPreview');
    preview.innerHTML = media.type === 'video'
        ? `<video src="${media.file_path}" controls></video>`
        : `<img src="${media.file_path}" alt="${media.name}">`;
        
    document.getElementById('previewName').textContent = media.name;
    document.getElementById('previewType').textContent = media.type;
    document.getElementById('previewSize').textContent = formatSize(media.size);
    document.getElementById('previewDimensions').textContent = media.resolution || 'N/A';
    document.getElementById('previewDate').textContent = formatDate(media.created_at);
    
    showModal('previewModal');
}

async function deleteMedia(mediaId) {
    if (!confirm('Are you sure you want to delete this media?')) return;
    
    try {
        const response = await fetch('/api/media/index.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: mediaId })
        });
        
        if (response.ok) {
            showNotification('Media deleted successfully', 'success');
            loadMedia();
            closeModal('previewModal');
        } else {
            throw new Error('Failed to delete media');
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

// Utility functions
function formatSize(bytes) {
    if (!bytes) return '0 B';
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / Math.pow(1024, i)).toFixed(1)} ${sizes[i]}`;
}

function formatDuration(seconds) {
    if (!seconds) return '0:00';
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;
    const remainingSeconds = Math.floor(seconds % 60);
    
    if (hours > 0) {
        return `${hours}:${padZero(remainingMinutes)}:${padZero(remainingSeconds)}`;
    }
    return `${remainingMinutes}:${padZero(remainingSeconds)}`;
}

function formatDate(date) {
    return new Date(date).toLocaleString();
}

function padZero(num) {
    return num.toString().padStart(2, '0');
}

function showNotification(message, type = 'info') {
    // Implementation depends on your notification system
    console.log(`${type}: ${message}`);
}

function toggleView(view) {
    currentView = view;
    document.getElementById('gridViewBtn').classList.toggle('
cat > public/assets/js/media.js << 'EOL'
[Previous code remains the same until the toggleView function]

function toggleView(view) {
    currentView = view;
    document.getElementById('gridViewBtn').classList.toggle('active', view === 'grid');
    document.getElementById('listViewBtn').classList.toggle('active', view === 'list');
    renderMedia(mediaItems);
}

function filterMedia() {
    const searchTerm = document.getElementById('mediaSearch').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    
    const filtered = mediaItems.filter(item => {
        const matchesSearch = item.name.toLowerCase().includes(searchTerm);
        const matchesType = !typeFilter || item.type === typeFilter;
        return matchesSearch && matchesType;
    });
    
    renderMedia(filtered);
}

function sortMedia() {
    const sortBy = document.getElementById('sortBy').value;
    
    const sorted = [...mediaItems].sort((a, b) => {
        switch(sortBy) {
            case 'name':
                return a.name.localeCompare(b.name);
            case 'size':
                return b.size - a.size;
            case 'type':
                return a.type.localeCompare(b.type);
            case 'date':
            default:
                return new Date(b.created_at) - new Date(a.created_at);
        }
    });
    
    renderMedia(sorted);
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    updateUploadPreview();
}

function downloadMedia() {
    const preview = document.getElementById('mediaPreview');
    const mediaElement = preview.querySelector('img, video');
    if (!mediaElement) return;
    
    const link = document.createElement('a');
    link.href = mediaElement.src;
    link.download = document.getElementById('previewName').textContent;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function showModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
