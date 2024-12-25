class PlaylistManager {
    constructor() {
        this.selectedPlaylist = null;
        this.mediaItems = new Set();
        this.init();
    }

    async init() {
        await this.loadPlaylists();
        this.setupEventListeners();
        this.initSortable();
    }

    async loadPlaylists() {
        const response = await fetch('/digiplayer/public/api/playlists');
        const data = await response.json();
        this.renderPlaylists(data.playlists);
    }

    initSortable() {
        new Sortable(document.getElementById('playlistContent'), {
            animation: 150,
            onEnd: () => this.updatePlaylistOrder()
        });
    }

    renderPlaylists(playlists) {
        document.getElementById('playlistsList').innerHTML = playlists.map(p => `
            <div class="playlist-item" onclick="playlistManager.selectPlaylist(${p.id})">
                <div class="playlist-info">
                    <h3>${p.name}</h3>
                    <span>${p.items_count} items · ${this.formatDuration(p.total_duration)}</span>
                </div>
                <div class="playlist-actions">
                    <button onclick="playlistManager.editPlaylist(${p.id})">Edit</button>
                    <button onclick="playlistManager.deletePlaylist(${p.id})">Delete</button>
                </div>
            </div>
        `).join('');
    }

    formatDuration(seconds) {
        return new Date(seconds * 1000).toISOString().substr(11, 8);
    }

    setupEventListeners() {
        // Event listeners setup
    }
}

const playlistManager = new PlaylistManager();
