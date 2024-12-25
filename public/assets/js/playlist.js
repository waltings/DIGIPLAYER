let selectedPlaylists = new Set();

function showAddPlaylistModal() {
    document.getElementById('addPlaylistModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('addPlaylistModal').style.display = 'none';
    document.getElementById('addPlaylistForm').reset();
}

function editPlaylist(id) {
    fetch(`/digiplayer/public/api/playlists/index.php?id=${id}`)
        .then(r => r.json())
        .then(playlist => {
            document.getElementById('playlistName').value = playlist.name;
            document.getElementById('playlistDescription').value = playlist.description || '';
            document.getElementById('addPlaylistForm').dataset.editId = id;
            showAddPlaylistModal();
        });
}

function deletePlaylist(id) {
    if (!confirm('Are you sure you want to delete this playlist?')) return;
    
    fetch('/digiplayer/public/api/playlists/index.php', {
        method: 'DELETE',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id})
    })
    .then(r => r.json())
    .then(() => loadPlaylists());
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addPlaylistForm');
    form.onsubmit = async function(e) {
        e.preventDefault();
        
        const data = {
            name: document.getElementById('playlistName').value,
            description: document.getElementById('playlistDescription').value
        };

        try {
            const response = await fetch('/digiplayer/public/api/playlists/index.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });

            if (!response.ok) throw new Error('Server error');

            closeModal();
            loadPlaylists();
        } catch (error) {
            console.error('Error:', error);
        }
    };
});

function loadPlaylists() {
    fetch('/digiplayer/public/api/playlists/index.php')
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('playlistsList');
            if (!list) return;
            list.innerHTML = data.playlists.map(p => `
                <div class="list-row">
                    <div class="col-name" style="width: 30%;">${p.name}</div>
                    <div class="col-items" style="width: 15%;">0</div>
                    <div class="col-duration" style="width: 15%;">0:00</div>
                    <div class="col-schedule" style="width: 15%;">${p.schedule_type || '-'}</div>
                    <div class="col-status" style="width: 10%;">
                        <span class="badge status-${p.status}">${p.status}</span>
                    </div>
                    <div class="col-actions" style="width: 15%;">
                        <button onclick="editPlaylist(${p.id})" class="btn btn-edit">Edit</button>
                        <button onclick="deletePlaylist(${p.id})" class="btn btn-delete">Delete</button>
                    </div>
                </div>
            `).join('');
        });
}

loadPlaylists();
