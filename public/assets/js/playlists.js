let currentPlaylist = null;
let selectedMedia = new Set();
let playlists = [];
let mediaItems = [];

document.addEventListener('DOMContentLoaded', function() {
    initializePage();
    setupEventListeners();
});

function initializePage() {
    loadPlaylists();
    initializeSortable();
}

function setupEventListeners() {
    // Search functionality
    document.getElementById('playlistSearch').addEventListener('input', filterPlaylists);
    document.getElementById('contentSearch').addEventListener('input', filterMedia);
    document.getElementById('contentTypeFilter').addEventListener('change', filterMedia);
    
    // Form submissions
    document.getElementById('addPlaylistForm').addEventListener('submit', handleAddPlaylist);
}

function initializeSortable() {
    const contentList = document.getElementById('playlistContent');
    Sortable.create(contentList, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        onEnd: function() {
            updatePlaylistOrder();
        }
    });
}

async function loadPlaylists() {
    try {
        const response = await fetch('/api/playlists/index.php');
        const data = await response.json();
        playlists = data.playlists;
        renderPlaylists(playlists);
    } catch (error) {
        showNotification('Error loading playlists', 'error');
    }
}

async function loadPlaylistContent(playlistId) {
    try {
        const response = await fetch(`/api/playlists/index.php?id=${playlistId}&items=1`);
        const data = await response.json();
        currentPlaylist = data.playlist;
        renderPlaylistContent(data.items);
        updatePlaylistInfo(data.items);
    } catch (error) {
        showNotification('Error loading playlist content', 'error');
    }
}

function renderPlaylists(playlists) {
    const playlistsList = document.getElementById('playlistsList');
    playlistsList.innerHTML = playlists.map(playlist => `
        <div class="playlist-item ${currentPlaylist?.id === playlist.id ? 'active' : ''}"
             onclick="selectPlaylist(${playlist.id})">
            <div class="playlist-info">
                <h4>${playlist.name}</h4>
                <span class="item-count">${playlist.items_count} items</span>
            </div>
            <div class="playlist-duration">${formatDuration(playlist.total_duration)}</div>
            <div class="playlist-actions">
                <button class="btn-icon" onclick="duplicatePlaylist(${playlist.id})">
                    <i class="icon-copy"></i>
                </button>
                <button class="btn-icon" onclick="deletePlaylist(${playlist.id})">
                    <i class="icon-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
}

function renderPlaylistContent(items) {
    const contentList = document.getElementById('playlistContent');
    contentList.innerHTML = items.map((item, index) => `
        <div class="content-item" data-id="${item.id}">
            <div class="drag-handle">
                <i class="icon-drag"></i>
            </div>
            <div class="content-preview">
                ${item.type === 'video' 
                    ? `<video src="${item.file_path}"></video>`
                    : `<img src="${item.file_path}" alt="${item.name}">`
                }
            </div>
            <div class="content-info">
                <h4>${item.name}</h4>
                <span class="duration">${formatDuration(item.duration)}</span>
            </div>
            <div class="content-actions">
                <input type="number" 
                       class="duration-input" 
                       value="${item.duration}"
                       onchange="updateItemDuration(${item.id}, this.value)">
                <button class="btn-icon" onclick="removeContent(${item.id})">
                    <i class="icon-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
}

async function handleAddPlaylist(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const playlistData = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('/api/playlists/index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(playlistData)
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showNotification('Playlist created successfully', 'success');
            closeModal('addPlaylistModal');
            loadPlaylists();
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        showNotification(error.message, 'error');
    }
}

async function loadAvailableMedia() {
    try {
        const response = await fetch('/api/media/index.php');
        const data = await response.json();
        mediaItems = data.media;
        renderMediaGrid(mediaItems);
    } catch (error) {
        showNotification('Error loading media', 'error');
    }
}

function renderMediaGrid(media) {
    const mediaGrid = document.getElementById('mediaGrid');
    mediaGrid.innerHTML = media.map(item => `
        <div class="media-item ${selectedMedia.has(item.id) ? 'selected' : ''}"
             onclick="toggleMediaSelection(${item.id})">
            <div class="media-preview">
                ${item.type === 'video'
                    ? `<video src="${item.file_path}"></video>`
                    : `<img src="${item.file_path}" alt="${item.name}">`
                }
            </div>
            <div class="media-info">
                <span class="media-name">${item.name}</span>
                <span class="media-duration">${formatDuration(item.duration)}</span>
            </div>
        </div>
    `).join('');
}

function toggleMediaSelection(mediaId) {
    if (selectedMedia.has(mediaId)) {
        selectedMedia.delete(mediaId);
    } else {
        selectedMedia.add(mediaId);
    }
    
    // Update UI to show selection
    const mediaElement = document.querySelector(`.media-item[data-id="${mediaId}"]`);
    if (mediaElement) {
        mediaElement.classList.toggle('selected');
    }
}

async function addSelectedContent() {
    if (!currentPlaylist || selectedMedia.size === 0) return;
    
    try {
        const response = await fetch('/api/playlists/media.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                playlist_id: currentPlaylist.id,
                media_ids: Array.from(selectedMedia)
            })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            showNotification('Content added successfully', 'success');
            closeModal('addContentModal');
            loadPlaylistContent(currentPlaylist.id);
            selectedMedia.clear();
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        showNotification('Failed to add content', 'error');
    }
}

// Utility functions
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

function padZero(num) {
    return num.toString().padStart(2, '0');
}

function showNotification(message, type = 'info') {
    // Implementation depends on your notification system
    console.log(`${type}: ${message}`);
}

function showModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
