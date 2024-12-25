// Global variables
let selectedDevices = new Set();

// Core Functions
function loadDevices() {
    const searchTerm = document.getElementById('searchInput')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const groupId = document.getElementById('groupFilter')?.value || '';

    const queryParams = new URLSearchParams({
        search: searchTerm,
        status: status,
        group_id: groupId
    });

    fetch(`/digiplayer/public/api/devices/index.php?${queryParams}`)
        .then(response => response.json())
        .then(data => {
            renderDevices(data.devices);
        })
        .catch(error => {
            console.error('Error loading devices:', error);
        });
}

function loadGroups() {
    fetch('/digiplayer/public/api/groups/index.php')
        .then(response => response.json())
        .then(data => {
            const groupSelect = document.getElementById('groupFilter');
            const deviceGroupSelect = document.getElementById('deviceGroup');
            
            const groupOptions = data.groups.map(group => 
                `<option value="${group.id}">${group.name}</option>`
            ).join('');
            
            if (groupSelect) groupSelect.innerHTML = '<option value="">All Groups</option>' + groupOptions;
            if (deviceGroupSelect) deviceGroupSelect.innerHTML = '<option value="">Select Group</option>' + groupOptions;
        })
        .catch(error => {
            console.error('Error loading groups:', error);
        });
}

function renderDevices(devices) {
    const devicesList = document.getElementById('devicesList');
    
    if (!devices || devices.length === 0) {
        devicesList.innerHTML = '<div class="no-data">No devices found</div>';
        return;
    }

    devicesList.innerHTML = `
        <div class="list-header">
            <div class="col"><input type="checkbox" onchange="toggleAllDevices(this)"></div>
            <div class="col">Name</div>
            <div class="col">Status</div>
            <div class="col">IP Address</div>
            <div class="col">Group</div>
            <div class="col">Current Playlist</div>
            <div class="col">Actions</div>
        </div>
        ${devices.map(device => `
            <div class="list-row" data-device-id="${device.id}">
                <div class="col">
                    <input type="checkbox" onchange="toggleDevice(${device.id})" 
                           ${selectedDevices.has(device.id) ? 'checked' : ''}>
                </div>
                <div class="col">${device.name}</div>
                <div class="col">
                    <span class="status-badge ${device.status}">${device.status}</span>
                </div>
                <div class="col">${device.ip_address || '-'}</div>
                <div class="col">${device.group_names || '-'}</div>
                <div class="col">${device.playlist_name || '-'}</div>
                <div class="col">
                    <button onclick="editDevice(${device.id})" class="btn-icon">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteDevice(${device.id})" class="btn-icon">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('')}`;
}

function toggleDevice(id) {
    if (selectedDevices.has(id)) {
        selectedDevices.delete(id);
    } else {
        selectedDevices.add(id);
    }
    updateBulkActionsVisibility();
}

function toggleAllDevices(checkbox) {
    const deviceCheckboxes = document.querySelectorAll('.list-row input[type="checkbox"]');
    deviceCheckboxes.forEach(cb => {
        const deviceId = parseInt(cb.closest('.list-row').dataset.deviceId);
        cb.checked = checkbox.checked;
        if (checkbox.checked) {
            selectedDevices.add(deviceId);
        } else {
            selectedDevices.delete(deviceId);
        }
    });
    updateBulkActionsVisibility();
}

function updateBulkActionsVisibility() {
    const bulkActions = document.getElementById('bulkActions');
    if (bulkActions) {
        bulkActions.style.display = selectedDevices.size > 0 ? 'block' : 'none';
    }
}

function showAddDeviceModal() {
    const form = document.getElementById('deviceForm');
    if (form) form.reset();
    document.getElementById('modalTitle').textContent = 'Add New Device';
    document.getElementById('deviceId').value = '';
    document.getElementById('deviceModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function editDevice(id) {
    fetch(`/digiplayer/public/api/devices/index.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            const device = data.device;
            document.getElementById('deviceId').value = device.id;
            document.getElementById('deviceName').value = device.name;
            document.getElementById('deviceIP').value = device.ip_address;
            document.getElementById('deviceLocation').value = device.location || '';
            document.getElementById('deviceGroup').value = device.group_id || '';
            document.getElementById('devicePlaylist').value = device.playlist_id || '';
            document.getElementById('modalTitle').textContent = 'Edit Device';
            document.getElementById('deviceModal').style.display = 'flex';
        })
        .catch(error => console.error('Error:', error));
}

function deleteDevice(id) {
    if (!confirm('Are you sure you want to delete this device?')) return;
    
    fetch('/digiplayer/public/api/devices/index.php', {
        method: 'DELETE',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: id})
    })
    .then(response => response.json())
    .then(() => loadDevices())
    .catch(error => console.error('Error:', error));
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', () => {
    loadDevices();
    loadGroups();
    
    // Setup form submission
    const deviceForm = document.getElementById('deviceForm');
    if (deviceForm) {
        deviceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            const method = data.id ? 'PUT' : 'POST';
            
            fetch('/digiplayer/public/api/devices/index.php', {
                method: method,
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(() => {
                closeModal('deviceModal');
                loadDevices();
            })
            .catch(error => console.error('Error:', error));
        });
    }
    
    // Setup filters
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const groupFilter = document.getElementById('groupFilter');
    
    if (searchInput) searchInput.addEventListener('input', () => loadDevices());
    if (statusFilter) statusFilter.addEventListener('change', () => loadDevices());
    if (groupFilter) groupFilter.addEventListener('change', () => loadDevices());
});

// Make functions globally available
window.loadDevices = loadDevices;
window.loadGroups = loadGroups;
window.toggleDevice = toggleDevice;
window.toggleAllDevices = toggleAllDevices;
window.editDevice = editDevice;
window.deleteDevice = deleteDevice;
window.showAddDeviceModal = showAddDeviceModal;
window.closeModal = closeModal;