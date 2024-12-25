document.addEventListener('DOMContentLoaded', function() {
    initializeMonitoring();
    startMonitoringUpdates();
});

function initializeMonitoring() {
    updateSystemStatus();
    updateDeviceStats();
}

function startMonitoringUpdates() {
    // Update every 30 seconds
    setInterval(updateSystemStatus, 30000);
    setInterval(updateDeviceStats, 30000);
}

function updateSystemStatus() {
    fetch('/digiplayer/public/api/monitoring/system-status')
        .then(response => response.json())
        .then(data => {
            updateOverviewPanel(data);
        })
        .catch(error => console.error('Error updating system status:', error));
}

function updateDeviceStats() {
    fetch('/digiplayer/public/api/monitoring/device-metrics')
        .then(response => response.json())
        .then(data => {
            updateDeviceTable(data.devices);
        })
        .catch(error => console.error('Error updating device stats:', error));
}

function updateOverviewPanel(data) {
    const overview = document.getElementById('statusOverview');
    overview.innerHTML = `
        <div class="status-grid">
            <div class="status-item">
                <label>Online Devices</label>
                <span class="value">${data.online_devices}/${data.total_devices}</span>
            </div>
            <div class="status-item">
                <label>System Load</label>
                <span class="value">${data.system_load.toFixed(2)}</span>
            </div>
            <div class="status-item">
                <label>Memory Usage</label>
                <span class="value">${data.memory_usage}%</span>
            </div>
            <div class="status-item">
                <label>Storage Usage</label>
                <span class="value">${data.storage_usage}%</span>
            </div>
        </div>
        <div class="alerts-section">
            <h4>Active Alerts</h4>
            <div class="alert-list">
                ${formatAlerts(data.alerts)}
            </div>
        </div>
    `;
}

function updateDeviceTable(devices) {
    const tbody = document.getElementById('monitorData');
    tbody.innerHTML = devices.map(device => `
        <tr>
            <td>
                <div class="device-name">${device.name}</div>
                <div class="device-ip">${device.ip_address}</div>
            </td>
            <td>
                <span class="status-badge ${device.status}">${device.status}</span>
            </td>
            <td>
                <div class="network-stats">
                    <div>↑ ${formatNetworkSpeed(device.network_up)}</div>
                    <div>↓ ${formatNetworkSpeed(device.network_down)}</div>
                </div>
            </td>
            <td>
                <div class="progress-bar">
                    <div class="progress" style="width: ${device.cpu_usage}%"></div>
                </div>
                <div class="value">${device.cpu_usage}%</div>
            </td>
            <td>
                <div class="progress-bar">
                    <div class="progress" style="width: ${device.memory_usage}%"></div>
                </div>
                <div class="value">${device.memory_usage}%</div>
            </td>
            <td>${formatLastUpdate(device.last_update)}</td>
        </tr>
    `).join('');
}

function formatAlerts(alerts) {
    if (!alerts || alerts.length === 0) {
        return '<div class="no-alerts">No active alerts</div>';
    }
    
    return alerts.map(alert => `
        <div class="alert-item ${alert.severity}">
            <div class="alert-header">
                <span class="alert-type">${alert.type}</span>
                <span class="alert-time">${formatTime(alert.created_at)}</span>
            </div>
            <div class="alert-message">${alert.message}</div>
        </div>
    `).join('');
}

function formatNetworkSpeed(speed) {
    if (speed < 1024) return `${speed.toFixed(1)} KB/s`;
    return `${(speed/1024).toFixed(1)} MB/s`;
}

function formatLastUpdate(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // seconds

    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff/60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
    return date.toLocaleDateString();
}

function formatTime(timestamp) {
    return new Date(timestamp).toLocaleTimeString();
}
