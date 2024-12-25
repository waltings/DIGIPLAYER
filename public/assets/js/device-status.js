class DeviceMonitor {
    constructor() {
        this.updateInterval = 30000;
        this.init();
    }

    init() {
        this.loadDeviceStats();
        setInterval(() => this.loadDeviceStats(), this.updateInterval);
    }

    async loadDeviceStats() {
        try {
            const response = await fetch('/digiplayer/public/api/device-stats');
            const stats = await response.json();
            this.updateStats(stats);
        } catch (error) {
            console.error('Failed to load device stats:', error);
        }
    }

    updateStats(stats) {
        this.updateOverview(stats.overview);
        this.updateDetailedStats(stats.details);
    }

    updateOverview(overview) {
        const template = `
            <div class="stat-group">
                <div class="stat-item">
                    <div class="stat-value">${overview.total_devices}</div>
                    <div class="stat-label">Total Devices</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">${overview.online_devices}</div>
                    <div class="stat-label">Online</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">${overview.offline_devices}</div>
                    <div class="stat-label">Offline</div>
                </div>
            </div>
        `;
        document.getElementById('deviceOverview').innerHTML = template;
    }

    updateDetailedStats(details) {
        const template = details.map(device => `
            <div class="device-stat-card">
                <div class="device-header">
                    <h3>${device.name}</h3>
                    <span class="status-badge ${device.status}">${device.status}</span>
                </div>
                <div class="stat-grid">
                    <div class="stat-row">
                        <label>CPU Usage</label>
                        <div class="progress-bar">
                            <div class="progress" style="width: ${device.cpu_usage}%"></div>
                        </div>
                        <span>${device.cpu_usage}%</span>
                    </div>
                    <div class="stat-row">
                        <label>Memory</label>
                        <div class="progress-bar">
                            <div class="progress" style="width: ${device.memory_usage}%"></div>
                        </div>
                        <span>${device.memory_usage}%</span>
                    </div>
                    <div class="stat-row">
                        <label>Storage</label>
                        <div class="progress-bar">
                            <div class="progress" style="width: ${device.storage_usage}%"></div>
                        </div>
                        <span>${device.storage_usage}%</span>
                    </div>
                </div>
                <div class="device-footer">
                    <span>Last Updated: ${new Date(device.last_update).toLocaleString()}</span>
                </div>
            </div>
        `).join('');
        document.getElementById('deviceStats').innerHTML = template;
    }
}

const deviceMonitor = new DeviceMonitor();
