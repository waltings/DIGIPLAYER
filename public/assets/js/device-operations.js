class DeviceOperations {
    constructor() {
        this.selectedDevices = new Set();
        this.refreshInterval = null;
        this.init();
    }

    init() {
        this.startAutoRefresh();
        this.setupBulkOperations();
        this.setupEventListeners();
        this.loadPlaylists();
    }

    async loadPlaylists() {
        try {
            const response = await fetch('/digiplayer/public/api/playlists/index.php');
            const data = await response.json();
            const playlistSelect = document.getElementById('defaultPlaylist');
            
            if (playlistSelect) {
                const options = data.playlists.map(playlist => 
                    `<option value="${playlist.id}">${playlist.name}</option>`
                ).join('');
                playlistSelect.innerHTML += options;
            }
        } catch (error) {
            console.error('Error loading playlists:', error);
        }
    }

    startAutoRefresh() {
        this.refreshInterval = setInterval(() => {
            if (document.getElementById('devicesList')) {
                this.refreshDeviceStatuses();
            }
        }, 30000);
    }

    async refreshDeviceStatuses() {
        try {
            const response = await fetch('/digiplayer/public/api/devices/status.php');
            if (!response.ok) throw new Error('Failed to fetch device statuses');
            const data = await response.json();
            this.updateDeviceStatuses(data.statuses);
        } catch (error) {
            console.error('Failed to refresh device statuses:', error);
        }
    }

    updateDeviceStatuses(statuses) {
        if (!statuses) return;
        
        statuses.forEach(status => {
            const statusElement = document.querySelector(`[data-id="${status.device_id}"] .status-badge`);
            if (statusElement) {
                statusElement.className = `status-badge ${status.status}`;
                statusElement.textContent = status.status;
            }
        });
    }

    async executeBulkAction(action, params = {}) {
        if (this.selectedDevices.size === 0) {
            showNotification('Please select devices first', 'warning');
            return;
        }

        try {
            let endpoint = '/digiplayer/public/api/devices/bulk-action.php';
            let data = {
                devices: Array.from(this.selectedDevices),
                action: action,
                ...params
            };

            if (action === 'assignGroup') {
                const groupId = await this.promptGroupSelection();
                if (!groupId) return;
                data.group_id = groupId;
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (!response.ok) throw new Error('Bulk action failed');

            showNotification('Bulk action completed successfully', 'success');
            loadDevices();
            this.selectedDevices.clear();
            this.updateBulkActionsVisibility();
        } catch (error) {
            showNotification('Error executing bulk action: ' + error.message, 'error');
        }
    }

    async promptGroupSelection() {
        return new Promise((resolve) => {
            // You could create a modal for group selection here
            const groupId = prompt('Enter group ID:');
            resolve(groupId);
        });
    }

    updateBulkActionsVisibility() {
        const bulkActions = document.getElementById('bulkActions');
        if (bulkActions) {
            bulkActions.style.display = this.selectedDevices.size > 0 ? 'block' : 'none';
        }
    }
}

// Initialize Device Operations
window.addEventListener('DOMContentLoaded', () => {
    window.deviceOps = new DeviceOperations();
});

// Device Settings Management
async function openDeviceSettings(deviceId) {
    try {
        const response = await fetch(`/digiplayer/public/api/devices/settings.php?device_id=${deviceId}`);
        if (!response.ok) throw new Error('Failed to fetch device settings');
        
        const data = await response.json();
        const settings = data.settings;

        // Populate settings form
        const form = document.getElementById('deviceSettingsForm');
        form.innerHTML = `
            <div class="settings-group">
                <h3>Network Settings</h3>
                <div class="form-group">
                    <label>Network Mode</label>
                    <select name="network_mode">
                        <option value="dhcp" ${settings.network_mode === 'dhcp' ? 'selected' : ''}>DHCP</option>
                        <option value="static" ${settings.network_mode === 'static' ? 'selected' : ''}>Static IP</option>
                    </select>
                </div>
                <div class="form-group" id="staticIpSettings" style="display: ${settings.network_mode === 'static' ? 'block' : 'none'}">
                    <label>Static IP</label>
                    <input type="text" name="static_ip" value="${settings.static_ip || ''}">
                    <label>Subnet Mask</label>
                    <input type="text" name="subnet_mask" value="${settings.subnet_mask || ''}">
                    <label>Gateway</label>
                    <input type="text" name="gateway" value="${settings.gateway || ''}">
                </div>
            </div>
            
            <div class="settings-group">
                <h3>Display Settings</h3>
                <div class="form-group">
                    <label>Screen Orientation</label>
                    <select name="screen_orientation">
                        <option value="landscape" ${settings.screen_orientation === 'landscape' ? 'selected' : ''}>Landscape</option>
                        <option value="portrait" ${settings.screen_orientation === 'portrait' ? 'selected' : ''}>Portrait</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Screen Resolution</label>
                    <select name="screen_resolution">
                        <option value="1920x1080" ${settings.screen_resolution === '1920x1080' ? 'selected' : ''}>1920x1080</option>
                        <option value="1280x720" ${settings.screen_resolution === '1280x720' ? 'selected' : ''}>1280x720</option>
                    </select>
                </div>
            </div>
            
            <div class="settings-group">
                <h3>Content Settings</h3>
                <div class="form-group">
                    <label>Cache Size (MB)</label>
                    <input type="number" name="cache_size" value="${settings.cache_size || 1000}">
                </div>
                <div class="form-group">
                    <label>Content Check Interval (minutes)</label>
                    <input type="number" name="content_check_interval" value="${settings.content_check_interval || 5}">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        `;

        // Show modal
        document.getElementById('deviceSettingsModal').style.display = 'flex';
        
        // Add event listeners
        form.addEventListener('submit', (e) => saveDeviceSettings(e, deviceId));
        
        // Handle network mode changes
        const networkModeSelect = form.querySelector('[name="network_mode"]');
        networkModeSelect.addEventListener('change', (e) => {
            const staticSettings = document.getElementById('staticIpSettings');
            staticSettings.style.display = e.target.value === 'static' ? 'block' : 'none';
        });

    } catch (error) {
        console.error('Error loading device settings:', error);
        showNotification('Failed to load device settings', 'error');
    }
}

async function saveDeviceSettings(event, deviceId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const settings = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('/digiplayer/public/api/devices/settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                device_id: deviceId,
                settings: settings
            })
        });

        if (!response.ok) throw new Error('Failed to save settings');
        
        showNotification('Device settings saved successfully', 'success');
        closeModal('deviceSettingsModal');
        
    } catch (error) {
        console.error('Error saving device settings:', error);
        showNotification('Failed to save device settings', 'error');
    }
}

// Content Synchronization
async function syncDeviceContent(deviceId) {
    try {
        const response = await fetch('/digiplayer/public/api/devices/action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: deviceId,
                action: 'updateContent'
            })
        });

        if (!response.ok) throw new Error('Failed to initiate content sync');
        
        showNotification('Content synchronization initiated', 'success');
        
    } catch (error) {
        console.error('Error syncing content:', error);
        showNotification('Failed to sync content', 'error');
    }
}

// Cache Management
async function manageCacheContent(deviceId, action) {
    try {
        const response = await fetch('/digiplayer/public/api/devices/action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: deviceId,
                action: action // 'clearCache' or 'validateCache'
            })
        });

        if (!response.ok) throw new Error(`Failed to ${action}`);
        
        showNotification(`Cache ${action} completed`, 'success');
        
    } catch (error) {
        console.error(`Error managing cache:`, error);
        showNotification(`Failed to manage cache`, 'error');
    }
}

// Status Monitoring
class DeviceMonitor {
    constructor(deviceId) {
        this.deviceId = deviceId;
        this.updateInterval = 30000; // 30 seconds
        this.monitoring = false;
    }

    start() {
        if (this.monitoring) return;
        this.monitoring = true;
        this.update();
        this.interval = setInterval(() => this.update(), this.updateInterval);
    }

    stop() {
        if (!this.monitoring) return;
        this.monitoring = false;
        if (this.interval) {
            clearInterval(this.interval);
        }
    }

    async update() {
        try {
            const response = await fetch(`/digiplayer/public/api/devices/status.php?device_id=${this.deviceId}`);
            if (!response.ok) throw new Error('Failed to fetch device status');
            
            const data = await response.json();
            this.updateUI(data);
            
        } catch (error) {
            console.error('Error updating device status:', error);
        }
    }

    updateUI(data) {
        // Update status indicators
        const statusElement = document.querySelector(`[data-id="${this.deviceId}"] .status-badge`);
        if (statusElement) {
            statusElement.className = `status-badge ${data.status}`;
            statusElement.textContent = data.status;
        }

        // Update other metrics if they exist
        if (data.metrics) {
            const cpuElement = document.querySelector(`[data-id="${this.deviceId}"] .cpu-usage`);
            if (cpuElement) {
                cpuElement.textContent = `${data.metrics.cpu_usage}%`;
            }

            const memoryElement = document.querySelector(`[data-id="${this.deviceId}"] .memory-usage`);
            if (memoryElement) {
                memoryElement.textContent = `${data.metrics.memory_usage}%`;
            }
        }
    }
}
