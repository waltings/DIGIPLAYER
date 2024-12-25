let selectedDevice = null;

function showAddDeviceModal() {
    const modal = document.getElementById('deviceModal');
    const form = document.getElementById('deviceForm');
    if (!modal || !form) {
        console.error('Modal or form not found');
        return;
    }

    form.reset();
    
    const modalTitle = document.getElementById('modalTitle');
    if (modalTitle) {
        modalTitle.textContent = 'Add New Device';
    }

    const deviceId = document.getElementById('deviceId');
    if (deviceId) {
        deviceId.value = '';
    }

    modal.style.display = 'flex';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

function deviceAction(id, action) {
    fetch('/digiplayer/public/api/devices/action', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id, action})
    });
}

function deleteDevice(id) {
    if (!confirm('Are you sure you want to delete this device?')) return;
    
    fetch(`/digiplayer/public/api/devices/index.php`, {
        method: 'DELETE',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id })
    })
    .then(() => window.location.href = 'devices.php');
}

function generateKey(deviceId) {
    fetch('/digiplayer/public/api/devices/key.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id: deviceId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.key) {
            alert('Device key: ' + data.key);
        }
    });
}

function editDevice(id) {
    fetch(`/digiplayer/public/api/devices/index.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            const device = data.device;
            const modal = document.getElementById('deviceModal');
            if (!modal) {
                console.error('Device modal not found');
                return;
            }

            // Safely set values checking if elements exist
            const elements = {
                'deviceId': device.id,
                'deviceName': device.name,
                'deviceIP': device.ip_address,
                'deviceLocation': device.location || '',
                'deviceGroup': device.group_id || '',
                'devicePlaylist': device.playlist_id || ''
            };

            Object.entries(elements).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element) {
                    element.value = value;
                }
            });

            const modalTitle = document.getElementById('modalTitle');
            if (modalTitle) {
                modalTitle.textContent = 'Edit Device';
            }

            modal.style.display = 'flex';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load device data');
        });
}